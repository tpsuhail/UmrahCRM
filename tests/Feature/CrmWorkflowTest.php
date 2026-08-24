<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Trip;
use App\Services\AuthService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

class CrmWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private string $operatorToken;

    private string $agentToken;

    private string $agentCode;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->operatorToken = $this->login('admin', 'Admin@1447');

        $this->agentCode = $this->action($this->operatorToken, 'agents.save', ['agentName' => 'Nur Travel'])['agentCode'];
        $this->action($this->operatorToken, 'users.save', [
            'username' => 'nur', 'password' => 'secret123',
            'role' => 'agent', 'agentCode' => $this->agentCode,
        ]);
        $this->agentToken = $this->login('nur', 'secret123');
    }

    public function test_login_rejects_a_bad_password(): void
    {
        $this->postJson('/api/login', ['username' => 'admin', 'password' => 'wrong'])
            ->assertOk()
            ->assertJson(['ok' => false, 'error' => 'INVALID']);
    }

    public function test_unknown_token_is_rejected(): void
    {
        $this->postJson('/api', ['token' => 'nope', 'action' => 'dashboard.get'])
            ->assertStatus(401)
            ->assertJson(['ok' => false, 'error' => 'AUTH']);
    }

    public function test_an_agent_may_not_run_an_operator_action(): void
    {
        $this->postJson('/api', [
            'token' => $this->agentToken,
            'action' => 'requests.review',
            'payload' => ['requestId' => 'anything', 'decision' => 'approve'],
        ])->assertStatus(403)->assertJson(['ok' => false, 'error' => 'FORBIDDEN']);
    }

    public function test_an_approved_request_becomes_a_group_with_its_children(): void
    {
        $submitted = $this->action($this->agentToken, 'requests.submit', $this->booking());
        $this->assertTrue($submitted['ok']);

        $review = $this->action($this->operatorToken, 'requests.review', [
            'requestId' => $submitted['requestId'], 'decision' => 'approve',
        ]);

        $this->assertSame('approved', $review['decision']);
        $group = Group::find($review['groupId']);
        $this->assertSame('45', $group->totalPax);          // 40 + 4 + 1
        $this->assertSame('Confirmed', $group->status);
        $this->assertSame('Visa', $group->stage);
        $this->assertSame(3, Trip::query()->where('groupId', $group->groupId)->count());
    }

    public function test_an_agent_only_ever_sees_their_own_groups(): void
    {
        $this->approvedGroup();

        $other = $this->action($this->operatorToken, 'agents.save', ['agentName' => 'Other Travel'])['agentCode'];
        $this->action($this->operatorToken, 'users.save', [
            'username' => 'other', 'password' => 'secret123', 'role' => 'agent', 'agentCode' => $other,
        ]);
        $otherToken = $this->login('other', 'secret123');

        $this->assertCount(1, $this->action($this->agentToken, 'groups.list')['rows']);
        $this->assertCount(0, $this->action($otherToken, 'groups.list')['rows']);
    }

    public function test_stage_moves_forward_only_once_their_gates_are_met(): void
    {
        $groupId = $this->approvedGroup();
        $stage = fn (string $stage) => $this->action($this->operatorToken, 'groups.setStage',
            ['groupId' => $groupId, 'stage' => $stage]);

        $this->assertSame('NEED_VISA', $stage('Transport')['error']);
        $this->action($this->operatorToken, 'groups.setVisa', ['groupId' => $groupId, 'done' => true]);
        $this->assertTrue($stage('Transport')['ok']);

        $this->assertSame('NEED_ORDER', $stage('Operation')['error']);
        $this->action($this->operatorToken, 'transport.saveOrder', [
            'groupId' => $groupId, 'transportCompany' => 'Samaya Company', 'status' => 'Booked',
        ]);
        $this->assertTrue($stage('Operation')['ok']);

        $this->assertSame('NEED_TRIPS', $stage('Completed')['error']);
    }

    public function test_a_group_edit_cascades_into_its_trips_and_hotels(): void
    {
        $groupId = $this->approvedGroup();

        $impact = $this->action($this->operatorToken, 'groups.editImpact', [
            'groupId' => $groupId, 'arrivalDate' => '2026-09-12',
            'adults' => 49, 'children' => 4, 'infants' => 1,
        ]);

        $changes = collect($impact['changes']);
        // the arrival trip follows the new arrival date …
        $this->assertTrue($changes->contains(fn ($c) => $c['kind'] === 'trip'
            && $c['field'] === 'date' && $c['after'] === '2026-09-12'));
        // … the mazarat trip shifts by the same two days …
        $this->assertTrue($changes->contains(fn ($c) => $c['kind'] === 'trip'
            && $c['field'] === 'date' && $c['after'] === '2026-09-14'));
        // … 54 pax no longer fit in one 49-seat bus …
        $this->assertTrue($changes->contains(fn ($c) => $c['field'] === 'buses' && $c['after'] === '2'));
        // … and the first hotel's check-in moves with the arrival
        $this->assertTrue($changes->contains(fn ($c) => $c['kind'] === 'hotel'
            && $c['field'] === 'checkIn' && $c['after'] === '2026-09-12'));
    }

    public function test_a_trip_cannot_be_completed_before_it_happens(): void
    {
        $groupId = $this->approvedGroup();
        $trip = Trip::query()->where('groupId', $groupId)->where('tripType', 'Arrival')->first();

        $res = $this->action($this->operatorToken, 'trips.save', [
            'groupId' => $groupId, 'tripId' => $trip->tripId, 'tripStatus' => 'Done',
        ]);

        $this->assertSame('TRIP_FUTURE', $res['error']);
    }

    public function test_completing_the_arrival_trip_puts_the_group_in_kingdom(): void
    {
        $groupId = $this->approvedGroup();
        $trip = Trip::query()->where('groupId', $groupId)->where('tripType', 'Arrival')->first();

        $this->action($this->operatorToken, 'trips.save', [
            'groupId' => $groupId, 'tripId' => $trip->tripId,
            'date' => '2020-01-01', 'tripStatus' => 'Done',
        ]);

        $this->assertSame('InKingdom', Group::find($groupId)->status);
    }

    public function test_the_ops_board_only_lists_trips_with_a_confirmed_order(): void
    {
        $groupId = $this->approvedGroup();
        $this->assertCount(0, $this->action($this->operatorToken, 'ops.board')['rows']);

        $this->action($this->operatorToken, 'transport.saveOrder', [
            'groupId' => $groupId, 'transportCompany' => 'Samaya Company', 'status' => 'Requested',
        ]);
        $this->assertCount(0, $this->action($this->operatorToken, 'ops.board')['rows']);

        $order = $this->action($this->operatorToken, 'transport.board')['orders'][0];
        $this->action($this->operatorToken, 'transport.saveOrder', [
            'groupId' => $groupId, 'orderId' => $order['orderId'],
            'transportCompany' => 'Samaya Company', 'status' => 'Booked',
        ]);
        $this->assertCount(3, $this->action($this->operatorToken, 'ops.board')['rows']);
    }

    public function test_reports_search_matches_any_of_several_terms(): void
    {
        $this->approvedGroup();

        $report = $this->action($this->operatorToken, 'reports.run',
            ['module' => 'groups', 'search' => 'nothing-matches, Rombongan']);

        $this->assertSame(1, $report['total']);
        $this->assertSame('Rombongan A', $report['rows'][0]['groupName']);
    }

    public function test_a_report_exports_as_a_real_xlsx_file(): void
    {
        $this->approvedGroup();

        $export = $this->action($this->operatorToken, 'reports.excel', ['module' => 'groups', 'lang' => 'ar']);

        $this->assertTrue($export['ok']);
        $this->assertStringStartsWith('PK', base64_decode($export['base64']));   // zip magic
    }

    public function test_html_renders_to_a_pdf(): void
    {
        $export = $this->action($this->operatorToken, 'pdf.fromHtml', [
            'html' => '<html><body><h1>مجموعة</h1></body></html>', 'filename' => 'voucher',
        ]);

        $this->assertTrue($export['ok']);
        $this->assertSame('voucher.pdf', $export['filename']);
        $this->assertStringStartsWith('%PDF', base64_decode($export['base64']));
    }

    public function test_a_password_change_takes_effect(): void
    {
        AuthService::makeUser([
            'username' => 'temp', 'role' => 'operator', 'displayName' => 'Temp', 'status' => 'active',
        ], 'oldpass123');
        $token = $this->login('temp', 'oldpass123');

        $this->assertSame('WRONG_OLD', $this->action($token, 'auth.changePassword',
            ['oldPassword' => 'nope', 'newPassword' => 'newpass123'])['error']);
        $this->assertTrue($this->action($token, 'auth.changePassword',
            ['oldPassword' => 'oldpass123', 'newPassword' => 'newpass123'])['ok']);

        $this->assertSame('INVALID', $this->postJson('/api/login',
            ['username' => 'temp', 'password' => 'oldpass123'])->json('error'));
        $this->assertNotEmpty($this->login('temp', 'newpass123'));
    }

    public function test_logging_out_invalidates_the_token(): void
    {
        $token = $this->login('admin', 'Admin@1447');
        $this->assertTrue($this->action($token, 'auth.logout')['ok']);

        $this->postJson('/api', ['token' => $token, 'action' => 'dashboard.get'])->assertStatus(401);
    }

    // ── helpers ──────────────────────────────────────────────

    private function login(string $username, string $password): string
    {
        $this->withoutMiddleware(ThrottleRequests::class);

        return $this->postJson('/api/login', compact('username', 'password'))
            ->assertOk()->json('token');
    }

    private function action(string $token, string $action, array $payload = []): array
    {
        return $this->postJson('/api', compact('token', 'action', 'payload'))->json();
    }

    private function approvedGroup(): string
    {
        $submitted = $this->action($this->agentToken, 'requests.submit', $this->booking());

        return $this->action($this->operatorToken, 'requests.review', [
            'requestId' => $submitted['requestId'], 'decision' => 'approve',
        ])['groupId'];
    }

    private function booking(): array
    {
        return [
            'group' => [
                'groupName' => 'Rombongan A', 'adults' => 40, 'children' => 4, 'infants' => 1,
                'arrivalDate' => '2026-09-10', 'arrivalTime' => '08:30',
                'arrivalFlight' => 'SV811', 'arrivalPort' => 'JED Airport',
                'departureDate' => '2026-09-20', 'departureTime' => '22:00',
                'departureFlight' => 'SV812', 'departurePort' => 'JED Airport',
                'departureMode' => 'Air', 'bookingType' => 'Group',
            ],
            'hotels' => [
                ['city' => 'Makkah', 'hotelName' => 'Imtiyaz', 'checkIn' => '2026-09-10', 'checkOut' => '2026-09-15'],
                ['city' => 'Madinah', 'hotelName' => 'Sidrat', 'checkIn' => '2026-09-15', 'checkOut' => '2026-09-20'],
            ],
            'brn' => [['brnType' => 'Hotel', 'hotelName' => 'Imtiyaz', 'brnNumber' => 'BRN-1']],
            'trips' => [
                ['date' => '2026-09-10', 'tripType' => 'Arrival', 'from' => 'JED Airport', 'to' => 'Makkah',
                    'time' => '08:30', 'flightNo' => 'SV811', 'vehicleType' => 'Bus 49', 'buses' => '1', 'pax' => '45'],
                ['date' => '2026-09-20', 'tripType' => 'Departure', 'from' => 'Makkah', 'to' => 'JED Airport',
                    'time' => '17:00', 'flightNo' => 'SV812', 'vehicleType' => 'Bus 49', 'buses' => '1', 'pax' => '45'],
                ['date' => '2026-09-12', 'tripType' => 'Makkah Mazarat', 'from' => 'Makkah', 'to' => 'Makkah',
                    'time' => '09:00', 'vehicleType' => 'Coaster 23', 'buses' => '2', 'pax' => '45'],
            ],
        ];
    }
}
