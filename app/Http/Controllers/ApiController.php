<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogger;
use App\Services\AdminService;
use App\Services\AuthService;
use App\Services\CascadeService;
use App\Services\DashboardService;
use App\Services\GroupService;
use App\Services\HotelService;
use App\Services\ReportService;
use App\Services\RequestService;
use App\Services\TransportService;
use App\Services\VoucherService;
use App\Support\Session;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Single secured entry point for every authenticated call.
 *
 * The client posts {token, action, payload}; the registry decides which roles
 * may run the action and what handles it. Role and agent-code filtering happen
 * here and in the services — never in the browser.
 */
class ApiController extends Controller
{
    public function __construct(
        private AuthService $auth,
        private AdminService $admin,
        private DashboardService $dashboard,
        private GroupService $groups,
        private CascadeService $cascade,
        private RequestService $requests,
        private HotelService $hotels,
        private TransportService $transport,
        private VoucherService $vouchers,
        private ReportService $reports,
    ) {}

    /** Actions that write, and so run inside a transaction. */
    private const WRITES = [
        'auth.changePassword', 'auth.resetPassword',
        'agents.save', 'users.save', 'masters.save',
        'groups.createFull', 'groups.save', 'groups.setStatus', 'groups.setStage',
        'groups.archive', 'groups.setVisa', 'groups.setTransportBy',
        'requests.submit', 'requests.cancel', 'requests.saveDraft', 'requests.deleteDraft',
        'requests.review', 'requests.resubmit',
        'hotels.save', 'hotels.delete', 'brn.save', 'brn.delete',
        'transport.request', 'transport.saveOrder', 'trips.save', 'trips.delete',
    ];

    /**
     * action → [allowed roles, handler]. A handler receives the resolved
     * session and the client payload.
     *
     * @return array<string, array{roles: string[], fn: callable}>
     */
    private function registry(string $token): array
    {
        $both = ['operator', 'agent'];
        $operator = ['operator'];
        $agent = ['agent'];

        $entry = fn (array $roles, callable $fn) => ['roles' => $roles, 'fn' => $fn];

        return [
            // auth
            'auth.logout' => $entry($both, fn (Session $s, array $p) => $this->auth->logout($s, $token)),
            'auth.changePassword' => $entry($both, fn (Session $s, array $p) => $this->auth->changePassword($s, $p)),
            'auth.resetPassword' => $entry($operator, fn (Session $s, array $p) => $this->auth->resetPassword($s, $p)),

            // dashboard
            'dashboard.get' => $entry($both, fn (Session $s, array $p) => $this->dashboard->get($s)),
            'dashboard.stats' => $entry($both, fn (Session $s, array $p) => $this->dashboard->stats($s)),

            // agents / users / masters / logs
            'agents.list' => $entry($operator, fn (Session $s, array $p) => $this->admin->agentsList()),
            'agents.save' => $entry($operator, fn (Session $s, array $p) => $this->admin->agentsSave($s, $p)),
            'users.list' => $entry($operator, fn (Session $s, array $p) => $this->admin->usersList()),
            'users.save' => $entry($operator, fn (Session $s, array $p) => $this->admin->usersSave($s, $p)),
            'masters.list' => $entry($both, fn (Session $s, array $p) => $this->admin->mastersList($s, $p)),
            'masters.save' => $entry($operator, fn (Session $s, array $p) => $this->admin->mastersSave($s, $p)),
            'logs.list' => $entry($operator, fn (Session $s, array $p) => $this->admin->logsList($p)),

            // groups
            'groups.list' => $entry($both, fn (Session $s, array $p) => $this->groups->list($s)),
            'groups.createFull' => $entry($operator, fn (Session $s, array $p) => $this->groups->createFull($s, $p)),
            'groups.save' => $entry($operator, fn (Session $s, array $p) => $this->groups->save($s, $p)),
            'groups.setStatus' => $entry($operator, fn (Session $s, array $p) => $this->groups->setStatus($s, $p)),
            'groups.setStage' => $entry($operator, fn (Session $s, array $p) => $this->groups->setStage($s, $p)),
            'groups.log' => $entry($both, fn (Session $s, array $p) => $this->groups->activityLog($s, $p)),
            'groups.editImpact' => $entry($both, fn (Session $s, array $p) => $this->cascade->preview($s, $this->groups, $p)),
            'groups.archive' => $entry($operator, fn (Session $s, array $p) => $this->groups->archive($s, $p)),
            'groups.setVisa' => $entry($operator, fn (Session $s, array $p) => $this->groups->setVisa($s, $p)),
            'groups.setTransportBy' => $entry($operator, fn (Session $s, array $p) => $this->groups->setTransportBy($s, $p)),
            'followup.get' => $entry($both, fn (Session $s, array $p) => $this->groups->followup($s)),

            // requests
            'requests.list' => $entry($both, fn (Session $s, array $p) => $this->requests->list($s)),
            'requests.submit' => $entry($agent, fn (Session $s, array $p) => $this->requests->submit($s, $p)),
            'requests.cancel' => $entry($both, fn (Session $s, array $p) => $this->requests->cancel($s, $p)),
            'requests.saveDraft' => $entry($both, fn (Session $s, array $p) => $this->requests->saveDraft($s, $p)),
            'requests.deleteDraft' => $entry($both, fn (Session $s, array $p) => $this->requests->deleteDraft($s, $p)),
            'requests.review' => $entry($operator, fn (Session $s, array $p) => $this->requests->review($s, $p)),
            'requests.resubmit' => $entry($agent, fn (Session $s, array $p) => $this->requests->resubmit($s, $p)),

            // hotels + BRN
            'hotels.list' => $entry($both, fn (Session $s, array $p) => $this->hotels->list($s, $p)),
            'hotels.save' => $entry($operator, fn (Session $s, array $p) => $this->hotels->save($s, $p)),
            'hotels.delete' => $entry($operator, fn (Session $s, array $p) => $this->hotels->delete($s, $p)),
            'brn.save' => $entry($operator, fn (Session $s, array $p) => $this->hotels->brnSave($s, $p)),
            'brn.delete' => $entry($operator, fn (Session $s, array $p) => $this->hotels->brnDelete($s, $p)),

            // transport + trips + ops board
            'transport.list' => $entry($both, fn (Session $s, array $p) => $this->transport->list($s, $p)),
            'transport.board' => $entry($both, fn (Session $s, array $p) => $this->transport->board($s)),
            'transport.request' => $entry($both, fn (Session $s, array $p) => $this->requests->transportRequest($s, $p)),
            'transport.saveOrder' => $entry($operator, fn (Session $s, array $p) => $this->transport->saveOrder($s, $p)),
            'trips.save' => $entry($operator, fn (Session $s, array $p) => $this->transport->saveTrip($s, $p)),
            'trips.delete' => $entry($operator, fn (Session $s, array $p) => $this->transport->deleteTrip($s, $p)),
            'ops.board' => $entry($both, fn (Session $s, array $p) => $this->transport->opsBoard($s)),

            // vouchers, documents, reports
            'voucher.get' => $entry($both, fn (Session $s, array $p) => $this->vouchers->get($s, $p)),
            'pdf.fromHtml' => $entry($both, fn (Session $s, array $p) => $this->vouchers->pdfFromHtml($s, $p)),
            'company.get' => $entry($both, fn (Session $s, array $p) => ['ok' => true, 'company' => $this->admin->company()]),
            'reports.run' => $entry($both, fn (Session $s, array $p) => $this->reports->run($s, $p)),
            'reports.meta' => $entry($both, fn (Session $s, array $p) => $this->reports->meta($s)),
            'reports.excel' => $entry($both, fn (Session $s, array $p) => $this->reports->excel($s, $p)),
        ];
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:120'],
            'password' => ['required', 'string', 'max:200'],
        ]);

        return response()->json($this->auth->login($data['username'], $data['password']));
    }

    public function dispatchAction(Request $request): JsonResponse
    {
        $token = (string) ($request->bearerToken() ?: $request->input('token', ''));
        $action = (string) $request->input('action', '');
        $payload = $request->input('payload', []);
        if (! is_array($payload)) {
            $payload = [];
        }

        $session = $this->auth->resolve($token);
        if (! $session) {
            return response()->json(['ok' => false, 'error' => 'AUTH'], 401);
        }

        $entry = $this->registry($token)[$action] ?? null;
        if (! $entry) {
            return response()->json(['ok' => false, 'error' => 'UNKNOWN_ACTION'], 404);
        }
        if (! in_array($session->role, $entry['roles'], true)) {
            app(ActivityLogger::class)->log($session, 'FORBIDDEN', '', '', $action);

            return response()->json(['ok' => false, 'error' => 'FORBIDDEN'], 403);
        }

        try {
            $run = fn () => $entry['fn']($session, $payload);
            $result = in_array($action, self::WRITES, true) ? DB::transaction($run) : $run();

            return response()->json($result);
        } catch (Throwable $e) {
            Log::error('CRM action failed', ['action' => $action, 'exception' => $e]);

            return response()->json([
                'ok' => false,
                'error' => 'SERVER',
                // the raw message is useful in development and noise in production
                'message' => config('app.debug') ? $e->getMessage() : 'Unexpected server error',
            ], 500);
        }
    }
}
