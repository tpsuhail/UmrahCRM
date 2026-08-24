<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\BookingRequest;
use App\Models\Group;
use App\Models\TransportOrder;
use App\Models\Trip;
use App\Models\User;
use App\Support\Crm;
use App\Support\Dates;
use App\Support\Session;
use Illuminate\Support\Collection;

class DashboardService
{
    /** Landing dashboard: headline counters plus two short work lists. */
    public function get(Session $session): array
    {
        $groups = Group::query()->get();
        $requests = BookingRequest::query()->orderBy('seq')->get();
        $trips = Trip::query()->get();
        $groupMap = $groups->keyBy('groupId');

        if ($session->isAgent()) {
            $myGroups = $groups->where('agentCode', $session->agentCode);
            $myRequests = $requests->where('agentCode', $session->agentCode);

            $kpis = $this->kpis($myGroups, $myRequests);
            $kpis['returnedRequests'] = $myRequests->where('status', 'Returned')->count();

            $myGroupIds = $myGroups->pluck('groupId')->flip();

            return [
                'ok' => true,
                'role' => 'agent',
                'kpis' => $kpis,
                'openRequests' => $myRequests
                    ->whereIn('status', ['Pending', 'Returned'])
                    ->take(-8)->reverse()
                    ->map(fn ($r) => $this->requestRow($r))->values()->all(),
                'upcomingTrips' => $trips
                    ->filter(fn ($tr) => $myGroupIds->has($tr->groupId) && Dates::within7($tr->date))
                    ->sortBy(fn ($tr) => [$tr->date, $tr->time])
                    ->take(8)
                    ->map(fn ($tr) => $this->tripRow($tr, $groupMap))->values()->all(),
            ];
        }

        $agents = Agent::query()->get();
        $orders = TransportOrder::query()->get();
        $today = Dates::todayUtc();

        $k = $this->kpis($groups, $requests);
        $k['agents'] = $agents->where('status', 'active')->count();
        $k['users'] = User::query()->where('status', 'active')->count();

        // ── operator notifications: what needs a human today ──
        $hasOrder = $orders->pluck('groupId')->flip();
        $active = $groups->filter(fn ($g) => $g->status !== 'Cancelled' && $g->stage !== 'Completed');

        $k['waitingVisa'] = $active->filter(fn ($g) => (! in_array($g->stage, Crm::GROUP_STAGES, true) || $g->stage === 'Visa')
            && $g->visaStatus !== 'Done')->count();
        $k['transportWaiting'] = $active->filter(fn ($g) => $g->stage === 'Transport' && ! $hasOrder->has($g->groupId))->count();
        $k['staleStages'] = $active->filter(function ($g) use ($today) {
            $since = Dates::parseYmd(str_replace('/', '-', explode(' ', (string) $g->stageSince)[0]));

            return $since !== null && $today !== null && round(($today - $since) / 86400) >= 5;
        })->count();
        $k['tripsToday'] = $trips->filter(fn ($tr) => Dates::parseYmd($tr->date) === $today && $tr->tripStatus !== 'Done')->count();
        $k['transportReqs'] = $requests->filter(fn ($r) => $r->status === 'Pending'
            && in_array($r->type, ['EditTrip', 'EditOrder'], true))->count();

        return [
            'ok' => true,
            'role' => 'operator',
            'kpis' => $k,
            'todayTrips' => $trips
                ->filter(fn ($tr) => Dates::parseYmd($tr->date) === $today)
                ->sortBy('time')->take(8)
                ->map(fn ($tr) => $this->tripRow($tr, $groupMap))->values()->all(),
            'pendingRequests' => $requests->where('status', 'Pending')
                ->take(-8)->reverse()
                ->map(fn ($r) => $this->requestRow($r))->values()->all(),
        ];
    }

    private function kpis(Collection $groups, Collection $requests): array
    {
        $active = ['New', 'Confirmed', 'InKingdom'];
        $settled = ['Departed', 'Closed', 'Cancelled'];

        return [
            'groups' => $groups->count(),
            'totalPax' => $groups->sum(fn ($g) => Crm::i($g->totalPax)),
            'inKingdom' => $groups->where('status', 'InKingdom')->count(),
            'arriving7' => $groups->filter(fn ($g) => in_array($g->status, $active, true) && Dates::within7($g->arrivalDate))->count(),
            'departing7' => $groups->filter(fn ($g) => ! in_array($g->status, $settled, true) && Dates::within7($g->departureDate))->count(),
            'pendingRequests' => $requests->where('status', 'Pending')->count(),
        ];
    }

    private function requestRow(BookingRequest $r): array
    {
        $group = json_decode((string) $r->payloadJSON, true);
        $group = is_array($group) ? ($group['group'] ?? $group) : [];

        return [
            'requestId' => $r->requestId,
            'type' => (string) $r->type,
            'status' => (string) $r->status,
            'agentCode' => (string) $r->agentCode,
            'groupName' => Crm::s($group['groupName'] ?? ''),
            'totalPax' => Crm::s($group['totalPax'] ?? ''),
            'submittedAt' => (string) $r->submittedAt,
        ];
    }

    private function tripRow(Trip $tr, Collection $groupMap): array
    {
        $g = $groupMap[$tr->groupId] ?? null;

        return [
            'tripId' => $tr->tripId,
            'groupId' => (string) $tr->groupId,
            'date' => (string) $tr->date,
            'day' => (string) $tr->day,
            'time' => (string) $tr->time,
            'tripType' => (string) $tr->tripType,
            'fileNo' => (string) ($g->fileNo ?? ''),
            'groupName' => (string) ($g->groupName ?? ''),
            'from' => (string) $tr->from,
            'to' => (string) $tr->to,
            'tripStatus' => (string) $tr->tripStatus,
            'driverName' => (string) $tr->driverName,
        ];
    }

    /**
     * The analytics view: arrival/departure pipeline, overdue detection, stage
     * and agent distribution, and today's real movements. Agents only ever see
     * their own groups.
     */
    public function stats(Session $session): array
    {
        $isAgent = $session->isAgent();
        $agentNames = Agent::query()->pluck('agentName', 'agentCode');

        $groups = Group::query()
            ->where('archived', '!=', 'yes')
            ->when($isAgent, fn ($q) => $q->where('agentCode', $session->agentCode))
            ->get();

        $confirmed = TransportOrder::query()->where('status', 'Booked')->pluck('groupId')->flip();
        $live = $groups->whereNotIn('status', ['Cancelled', 'Closed']);

        $k = [
            'totalGroups' => $live->count(),
            'totalPax' => $live->sum(fn ($g) => Crm::i($g->totalPax)),
            'arrivingToday' => 0, 'arrivingTomorrow' => 0, 'arrivingNext7' => 0,
            'inKingdom' => 0,
            'departingToday' => 0, 'departingNext7' => 0, 'departed' => 0,
            'overdue' => 0, 'noArrivalDate' => 0, 'awaitingTransport' => 0,
        ];
        if (! $isAgent) {
            $k['agents'] = Agent::query()->where('status', 'active')->count();
            $k['users'] = User::query()->where('status', 'active')->count();
        }

        foreach ($live as $g) {
            $a = Dates::dayDiff($g->arrivalDate);
            $d = Dates::dayDiff($g->departureDate);

            if ($a === null) {
                $k['noArrivalDate']++;
            }
            if ($a === 0) {
                $k['arrivingToday']++;
            }
            if ($a === 1) {
                $k['arrivingTomorrow']++;
            }
            if ($a !== null && $a >= 0 && $a <= 7) {
                $k['arrivingNext7']++;
            }
            if ($g->status === 'InKingdom') {
                $k['inKingdom']++;
            }
            if ($g->status === 'Departed') {
                $k['departed']++;
            }
            if ($d === 0) {
                $k['departingToday']++;
            }
            if ($d !== null && $d >= 0 && $d <= 7) {
                $k['departingNext7']++;
            }
            if ($this->isOverdue($g)) {
                $k['overdue']++;
            }
            if (! $confirmed->has($g->groupId) && $g->stage !== 'Completed') {
                $k['awaitingTransport']++;
            }
        }

        // ── distributions, rendered as bars (no charting library needed) ──
        $stageDist = array_fill_keys(Crm::GROUP_STAGES, 0);
        foreach ($live as $g) {
            $stage = in_array($g->stage, Crm::GROUP_STAGES, true) ? $g->stage : 'Visa';
            $stageDist[$stage]++;
        }
        $statusDist = array_fill_keys(Crm::GROUP_STATUSES, 0);
        foreach ($groups as $g) {
            if (isset($statusDist[$g->status])) {
                $statusDist[$g->status]++;
            }
        }

        // ── per-agent breakdown (operator only) ──
        $agentStats = [];
        if (! $isAgent) {
            $byAgent = [];
            foreach ($live as $g) {
                $code = (string) ($g->agentCode ?: '—');
                $byAgent[$code] ??= [
                    'agentCode' => $code, 'agentName' => $agentNames[$code] ?? $code,
                    'groups' => 0, 'pax' => 0, 'arriving7' => 0, 'departing7' => 0,
                    'inKingdom' => 0, 'overdue' => 0, 'noOrder' => 0,
                ];
                $row = &$byAgent[$code];
                $row['groups']++;
                $row['pax'] += Crm::i($g->totalPax);
                $a = Dates::dayDiff($g->arrivalDate);
                $d = Dates::dayDiff($g->departureDate);
                if ($a !== null && $a >= 0 && $a <= 7) {
                    $row['arriving7']++;
                }
                if ($d !== null && $d >= 0 && $d <= 7) {
                    $row['departing7']++;
                }
                if ($g->status === 'InKingdom') {
                    $row['inKingdom']++;
                }
                if ($this->isOverdue($g)) {
                    $row['overdue']++;
                }
                if (! $confirmed->has($g->groupId) && $g->stage !== 'Completed') {
                    $row['noOrder']++;
                }
                unset($row);
            }
            $agentStats = array_values($byAgent);
            usort($agentStats, fn ($x, $y) => $y['groups'] <=> $x['groups']);
        }

        // ── today's movements, taken from trips so times and drivers are real ──
        $groupMap = $groups->keyBy('groupId');
        $todayTrips = Trip::query()->get()
            ->filter(fn (Trip $tr) => $groupMap->has($tr->groupId) && Dates::dayDiff($tr->date) === 0)
            ->map(function (Trip $tr) use ($groupMap, $agentNames) {
                $g = $groupMap[$tr->groupId];

                return [
                    'tripId' => $tr->tripId, 'groupId' => (string) $tr->groupId,
                    'fileNo' => (string) $g->fileNo, 'groupName' => (string) $g->groupName,
                    'agentName' => $agentNames[$g->agentCode] ?? (string) $g->agentCode,
                    'time' => (string) $tr->time, 'tripType' => (string) $tr->tripType,
                    'from' => (string) $tr->from, 'to' => (string) $tr->to,
                    'driverName' => (string) $tr->driverName, 'buses' => (string) $tr->buses,
                    'pax' => (string) $tr->pax, 'tripStatus' => (string) $tr->tripStatus,
                ];
            })->sortBy('time')->values()->all();

        // groups whose departure date has passed but which aren't marked departed
        $overdueList = $live->filter(fn ($g) => $this->isOverdue($g))
            ->map(fn (Group $g) => [
                'groupId' => $g->groupId, 'fileNo' => (string) $g->fileNo,
                'groupName' => (string) $g->groupName,
                'agentName' => $agentNames[$g->agentCode] ?? (string) $g->agentCode,
                'departureDate' => (string) $g->departureDate, 'status' => (string) $g->status,
                'daysLate' => abs(Dates::dayDiff($g->departureDate) ?? 0),
            ])->sortByDesc('daysLate')->take(10)->values()->all();

        return [
            'ok' => true, 'role' => $session->role, 'kpis' => $k,
            'stageDist' => $stageDist, 'statusDist' => $statusDist,
            'agentStats' => $agentStats, 'todayTrips' => $todayTrips, 'overdueList' => $overdueList,
        ];
    }

    /** Departure date has passed, yet the group is still marked in-kingdom. */
    private function isOverdue(Group $g): bool
    {
        $d = Dates::dayDiff($g->departureDate);

        return $d !== null && $d < 0 && ! in_array($g->status, ['Departed', 'Closed', 'Cancelled'], true);
    }
}
