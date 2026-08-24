<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\BookingRequest;
use App\Models\Group;
use App\Models\TransportOrder;
use App\Models\Trip;
use App\Support\Crm;
use App\Support\Dates;
use App\Support\Ids;
use App\Support\Session;

/** Transport orders, trip legs, and the operations board. */
class TransportService
{
    public function __construct(
        private ActivityLogger $logger,
        private GroupService $groups,
    ) {}

    public function list(Session $session, array $p): array
    {
        $guard = $this->groups->guard($session, Crm::s($p['groupId'] ?? ''));
        if (isset($guard['err'])) {
            return $guard['err'];
        }
        $groupId = Crm::s($p['groupId']);

        return [
            'ok' => true,
            'orders' => TransportOrder::query()->where('groupId', $groupId)->get()->map(fn ($r) => $r->toRow())->all(),
            'trips' => Trip::query()->where('groupId', $groupId)->get()->map(fn ($r) => $r->toRow())->all(),
        ];
    }

    public function saveOrder(Session $session, array $p): array
    {
        $guard = $this->groups->guard($session, Crm::s($p['groupId'] ?? ''));
        if (isset($guard['err'])) {
            return $guard['err'];
        }
        /** @var Group $group */
        $group = $guard['group'];

        if (Crm::s($p['transportCompany'] ?? '') === '') {
            return ['ok' => false, 'error' => 'MISSING'];
        }

        $status = Crm::s($p['status'] ?? '');
        $fields = [
            'groupId' => $group->groupId,
            'fileNo' => (string) $group->fileNo,
            'transportCompany' => Crm::s($p['transportCompany']),
            'orderNumber' => Crm::s($p['orderNumber'] ?? ''),
            'brn' => Crm::s($p['brn'] ?? ''),
            'confirmationNo' => Crm::s($p['confirmationNo'] ?? ''),
            'status' => in_array($status, ['Requested', 'Booked'], true) ? $status : 'Requested',
        ];

        if (! empty($p['orderId'])) {
            $order = TransportOrder::find(Crm::s($p['orderId']));
            if (! $order) {
                return ['ok' => false, 'error' => 'NOTFOUND'];
            }
            $order->update($fields);
            $this->logger->log($session, 'ORDER_UPDATE', 'TransportOrders', $order->orderId,
                $fields['transportCompany'].' / '.$fields['orderNumber'].' ['.$fields['status'].']', $group->groupId);

            return ['ok' => true, 'orderId' => $order->orderId];
        }

        // per-company sequential order number, starting from 1
        if ($fields['orderNumber'] === '') {
            $max = 0;
            foreach (TransportOrder::query()->where('transportCompany', $fields['transportCompany'])->pluck('orderNumber') as $n) {
                $max = max($max, Crm::i($n));
            }
            $fields['orderNumber'] = (string) ($max + 1);
        }

        $id = Ids::make('T');
        TransportOrder::create($fields + [
            'orderId' => $id,
            'createdBy' => $session->username,
            'approvedBy' => '',
            'createdAt' => Dates::nowStr(),
        ]);

        // assigning an order confirms the group's trips
        Trip::query()
            ->where('groupId', $group->groupId)
            ->where('bookingStatus', '!=', 'Booked')
            ->update(['bookingStatus' => 'Booked']);

        $this->logger->log($session, 'ORDER_CREATE', 'TransportOrders', $id,
            $fields['transportCompany'].' #'.$fields['orderNumber']
            .' (file '.$fields['fileNo'].') — trips confirmed', $group->groupId);

        return ['ok' => true, 'orderId' => $id, 'orderNumber' => $fields['orderNumber']];
    }

    // ── TRIPS ────────────────────────────────────────────────

    public function saveTrip(Session $session, array $p): array
    {
        $guard = $this->groups->guard($session, Crm::s($p['groupId'] ?? ''));
        if (isset($guard['err'])) {
            return $guard['err'];
        }
        $groupId = Crm::s($p['groupId']);

        // ── UPDATE: patch only what was sent, so driver-only saves are safe ──
        if (! empty($p['tripId'])) {
            $trip = Trip::find(Crm::s($p['tripId']));
            if (! $trip) {
                return ['ok' => false, 'error' => 'NOTFOUND'];
            }

            $patch = [];
            $setIf = function (string $key, ?array $allowed = null) use (&$patch, $p) {
                if (! array_key_exists($key, $p)) {
                    return;                                   // field not sent → leave as-is
                }
                $value = Crm::s($p[$key]);
                if ($allowed !== null && ! in_array($value, $allowed, true)) {
                    return;
                }
                $patch[$key] = $value;
            };

            $setIf('orderId');
            if (array_key_exists('date', $p)) {
                $patch['date'] = Crm::s($p['date']);
                $patch['day'] = Dates::dayNameOf($patch['date']);
            }
            $setIf('tripType');
            $setIf('transportMode', ['Air', 'Land', 'Sea', '']);
            $setIf('vehicleType');
            $setIf('from');
            $setIf('fromDetail');
            $setIf('to');
            $setIf('toDetail');
            $setIf('time');
            $setIf('flightNo');
            $setIf('buses');
            $setIf('pax');
            $setIf('driverName');
            $setIf('driverMobile');
            $setIf('plateNo');
            $setIf('bookingStatus', ['Pending', 'Booked']);
            $setIf('tripStatus', Crm::TRIP_STATUSES);
            $setIf('notes');

            // a trip cannot be completed before it has happened
            if (($patch['tripStatus'] ?? '') === 'Done') {
                $effective = $patch['date'] ?? (string) $trip->date;
                if (Dates::isFuture($effective)) {
                    return ['ok' => false, 'error' => 'TRIP_FUTURE', 'message' => $effective];
                }
            }

            $before = $trip->toRow();
            $trip->update($patch);

            $this->logger->log($session, 'TRIP_UPDATE', 'Trips', $trip->tripId,
                ($patch['date'] ?? $before['date']).' '.($patch['tripType'] ?? $before['tripType'])
                .(array_key_exists('driverName', $patch) ? ' | driver: '.($patch['driverName'] ?: '–') : '')
                .(array_key_exists('plateNo', $patch) ? ' | plate: '.($patch['plateNo'] ?: '–') : '')
                .(array_key_exists('tripStatus', $patch) ? ' | '.$patch['tripStatus'] : ''), $groupId);

            $this->syncGroupFromTrip($session, $groupId, array_merge($before, $patch));
            if (array_key_exists('tripStatus', $patch)) {
                $this->autoGroupStatus($session, $groupId);
            }

            return ['ok' => true, 'tripId' => $trip->tripId];
        }

        // ── CREATE: date + type are the minimum a leg needs ──
        if (Crm::s($p['date'] ?? '') === '' || Crm::s($p['tripType'] ?? '') === '') {
            return ['ok' => false, 'error' => 'MISSING'];
        }

        $mode = Crm::s($p['transportMode'] ?? '');
        $bookingStatus = Crm::s($p['bookingStatus'] ?? '');
        $tripStatus = Crm::s($p['tripStatus'] ?? '');
        $fields = [
            'orderId' => Crm::s($p['orderId'] ?? ''),
            'groupId' => $groupId,
            'date' => Crm::s($p['date']),
            'day' => Dates::dayNameOf($p['date']),
            'tripType' => Crm::s($p['tripType']),
            'transportMode' => in_array($mode, ['Air', 'Land', 'Sea'], true) ? $mode : '',
            'vehicleType' => Crm::s($p['vehicleType'] ?? ''),
            'from' => Crm::s($p['from'] ?? ''),
            'fromDetail' => Crm::s($p['fromDetail'] ?? ''),
            'to' => Crm::s($p['to'] ?? ''),
            'toDetail' => Crm::s($p['toDetail'] ?? ''),
            'time' => Crm::s($p['time'] ?? ''),
            'flightNo' => Crm::s($p['flightNo'] ?? ''),
            'buses' => Crm::s($p['buses'] ?? ''),
            'pax' => Crm::s($p['pax'] ?? ''),
            'driverName' => Crm::s($p['driverName'] ?? ''),
            'driverMobile' => Crm::s($p['driverMobile'] ?? ''),
            'plateNo' => Crm::s($p['plateNo'] ?? ''),
            'bookingStatus' => in_array($bookingStatus, ['Pending', 'Booked'], true) ? $bookingStatus : 'Pending',
            'tripStatus' => in_array($tripStatus, Crm::TRIP_STATUSES, true) ? $tripStatus : 'Pending',
            'notes' => Crm::s($p['notes'] ?? ''),
        ];

        if ($fields['tripStatus'] === 'Done' && Dates::isFuture($fields['date'])) {
            return ['ok' => false, 'error' => 'TRIP_FUTURE', 'message' => $fields['date']];
        }

        $id = Ids::make('TR');
        Trip::create($fields + ['tripId' => $id]);
        $this->logger->log($session, 'TRIP_CREATE', 'Trips', $id,
            $fields['date'].' '.$fields['tripType'], $groupId);

        return ['ok' => true, 'tripId' => $id];
    }

    public function deleteTrip(Session $session, array $p): array
    {
        $trip = Trip::find(Crm::s($p['tripId'] ?? ''));
        if (! $trip) {
            return ['ok' => false, 'error' => 'NOTFOUND'];
        }
        $trip->delete();
        $this->logger->log($session, 'TRIP_DELETE', 'Trips', $trip->tripId,
            $trip->date.' '.$trip->tripType, (string) $trip->groupId);

        return ['ok' => true];
    }

    /**
     * Completing an arrival or departure trip moves the group forward on its
     * own. Terminal states set by hand are never overridden, and the status
     * only ever advances along New → Confirmed → InKingdom → Departed.
     */
    public function autoGroupStatus(Session $session, string $groupId): void
    {
        $group = Group::find($groupId);
        if (! $group || in_array($group->status, ['Closed', 'Cancelled'], true)) {
            return;
        }

        $trips = Trip::query()->where('groupId', $groupId)->get(['tripType', 'tripStatus']);
        $depDone = $trips->contains(fn ($tr) => preg_match(Crm::DEPARTURE_RE, (string) $tr->tripType) && $tr->tripStatus === 'Done');
        $arrDone = $trips->contains(fn ($tr) => preg_match(Crm::ARRIVAL_RE, (string) $tr->tripType) && $tr->tripStatus === 'Done');

        $next = $depDone ? 'Departed' : ($arrDone ? 'InKingdom' : '');
        if ($next === '' || $next === $group->status) {
            return;
        }

        $order = ['New', 'Confirmed', 'InKingdom', 'Departed'];
        $currentIdx = array_search($group->status, $order, true);
        if ($currentIdx === false || array_search($next, $order, true) <= $currentIdx) {
            return;
        }

        $from = $group->status;
        $group->update(['status' => $next]);
        $this->logger->log($session, 'GROUP_STATUS_AUTO', 'Groups', $groupId,
            $from.' → '.$next.' (auto: trip completed)', $groupId);
    }

    /**
     * The arrival trip and the group's arrival fields describe the same flight,
     * so editing one must not silently contradict the other. Departure date and
     * time are deliberately not pushed back: the bus leaves hours before the
     * flight, so the trip's time is derived, not the source of truth.
     */
    public function syncGroupFromTrip(Session $session, string $groupId, array $trip): void
    {
        $group = Group::find($groupId);
        if (! $group) {
            return;
        }
        $type = (string) ($trip['tripType'] ?? '');
        $patch = [];

        if (preg_match(Crm::ARRIVAL_RE, $type)) {
            $map = ['date' => 'arrivalDate', 'time' => 'arrivalTime', 'flightNo' => 'arrivalFlight', 'from' => 'arrivalPort'];
        } elseif (preg_match(Crm::DEPARTURE_RE, $type)) {
            $map = ['flightNo' => 'departureFlight', 'to' => 'departurePort'];
        } else {
            return;   // ordinary trip: nothing to sync
        }

        foreach ($map as $tripField => $groupField) {
            $value = Crm::s($trip[$tripField] ?? '');
            if ($value !== '' && $value !== (string) $group->{$groupField}) {
                $patch[$groupField] = $value;
            }
        }
        if (! $patch) {
            return;
        }

        $diffs = [];
        foreach ($patch as $k => $v) {
            $diffs[] = $k.': '.(((string) $group->{$k}) ?: '–').' → '.$v;
        }
        $group->update($patch);
        $this->logger->log($session, 'GROUP_SYNC_FROM_TRIP', 'Groups', $groupId,
            implode(' | ', $diffs).' (from '.$type.')', $groupId);
    }

    // ── OPERATIONS BOARD ─────────────────────────────────────

    /**
     * The run sheet: every trip whose group has a CONFIRMED transport order.
     * Orders still "Requested" are only waiting on the company, so their trips
     * are not operational yet and stay off the board.
     */
    public function opsBoard(Session $session): array
    {
        $groups = Group::query()->get()->keyBy('groupId');
        $agentNames = Agent::query()->pluck('agentName', 'agentCode');

        $primaryOrder = [];   // groupId → first confirmed order
        foreach (TransportOrder::query()->where('status', 'Booked')->get() as $o) {
            $primaryOrder[$o->groupId] ??= $o;
        }

        $trips = Trip::query()->get()->filter(function (Trip $tr) use ($primaryOrder, $groups, $session) {
            if (! isset($primaryOrder[$tr->groupId])) {
                return false;
            }
            if ($session->isAgent()) {
                $g = $groups[$tr->groupId] ?? null;

                return $g && $g->agentCode === $session->agentCode;
            }

            return true;
        });

        $rows = $trips->map(function (Trip $tr) use ($groups, $agentNames, $primaryOrder) {
            $g = $groups[$tr->groupId] ?? null;
            $o = $primaryOrder[$tr->groupId];

            return $tr->toRow() + [
                'fileNo' => (string) ($g->fileNo ?? ''),
                'groupName' => (string) ($g->groupName ?? ''),
                'agentName' => $agentNames[$g->agentCode ?? ''] ?? (string) ($g->agentCode ?? ''),
                'transportCompany' => (string) $o->transportCompany,
                'orderNumber' => (string) $o->orderNumber,
                'confirmationNo' => (string) $o->confirmationNo,
            ];
        })->values()->all();

        // a run sheet reads best grouped by day, then by clock, then by driver
        usort($rows, fn ($a, $b) => [$a['date'], $a['time'], $a['driverName'] ?: "\u{ffff}", $a['driverMobile']]
            <=> [$b['date'], $b['time'], $b['driverName'] ?: "\u{ffff}", $b['driverMobile']]);

        return ['ok' => true, 'rows' => $rows, 'todayUTC' => Dates::todayUtc()];
    }

    /** Transportation page: KPIs, all orders, and pending change requests. */
    public function board(Session $session): array
    {
        $groups = Group::query()->get()->keyBy('groupId');
        $agentNames = Agent::query()->pluck('agentName', 'agentCode');

        $mine = function (string $groupId) use ($groups, $session) {
            if (! $session->isAgent()) {
                return true;
            }
            $g = $groups[$groupId] ?? null;

            return $g && $g->agentCode === $session->agentCode;
        };

        $orders = TransportOrder::query()->get()->filter(fn ($o) => $mine($o->groupId));
        $trips = Trip::query()->get()->filter(fn ($tr) => $mine($tr->groupId));
        $tripsByGroup = $trips->countBy('groupId');

        $orderRows = $orders->map(function (TransportOrder $o) use ($groups, $agentNames, $tripsByGroup) {
            $g = $groups[$o->groupId] ?? null;

            return [
                'orderId' => $o->orderId,
                'groupId' => (string) $o->groupId,
                'fileNo' => (string) ($g->fileNo ?? ''),
                'groupName' => (string) ($g->groupName ?? ''),
                'agentName' => $agentNames[$g->agentCode ?? ''] ?? (string) ($g->agentCode ?? ''),
                'transportCompany' => (string) $o->transportCompany,
                'orderNumber' => (string) $o->orderNumber,
                'confirmationNo' => (string) $o->confirmationNo,
                'brn' => (string) $o->brn,
                'status' => (string) $o->status,
                'createdAt' => (string) $o->createdAt,
                'tripsCount' => $tripsByGroup[$o->groupId] ?? 0,
            ];
        })->sortByDesc('createdAt')->values()->all();

        // groups still needing a CONFIRMED order
        $confirmedFor = $orders->where('status', 'Booked')->pluck('groupId')->flip();
        $withAnyOrder = $orders->pluck('groupId')->flip();

        $awaiting = $groups->filter(fn (Group $g) => $mine($g->groupId)
                && $g->status !== 'Cancelled' && $g->archived !== 'yes'
                && $g->stage !== 'Completed' && ! $confirmedFor->has($g->groupId))
            ->map(fn (Group $g) => [
                'groupId' => $g->groupId,
                'fileNo' => (string) $g->fileNo,
                'groupName' => (string) $g->groupName,
                'agentName' => $agentNames[$g->agentCode] ?? (string) $g->agentCode,
                'arrivalDate' => (string) $g->arrivalDate,
                'totalPax' => (string) $g->totalPax,
                'stage' => (string) $g->stage,
                // an order placed but unconfirmed reads differently from none at all
                'state' => $withAnyOrder->has($g->groupId) ? 'Requested' : 'None',
            ])
            ->sortBy('arrivalDate')->values()->all();

        $pending = BookingRequest::query()
            ->where('status', 'Pending')
            ->whereIn('type', ['EditTrip', 'EditOrder'])
            ->when($session->isAgent(), fn ($q) => $q->where('agentCode', $session->agentCode))
            ->orderByDesc('seq')
            ->get()
            ->map(function (BookingRequest $r) use ($groups, $agentNames) {
                $g = $groups[$r->groupId] ?? null;
                $note = (string) (json_decode((string) $r->payloadJSON, true)['note'] ?? '');

                return [
                    'requestId' => $r->requestId,
                    'type' => (string) $r->type,
                    'groupId' => (string) $r->groupId,
                    'fileNo' => (string) ($g->fileNo ?? ''),
                    'groupName' => (string) ($g->groupName ?? ''),
                    'agentName' => $agentNames[$r->agentCode] ?? (string) $r->agentCode,
                    'submittedAt' => (string) $r->submittedAt,
                    'note' => $note,
                ];
            })->all();

        $kpis = [
            'ordersTotal' => $orders->count(),
            'ordersConfirmed' => $orders->where('status', 'Booked')->count(),
            'ordersRequested' => $orders->where('status', 'Requested')->count(),
            'awaitingOrder' => count($awaiting),
            'tripsTotal' => $trips->count(),
            'tripsDone' => $trips->where('tripStatus', 'Done')->count(),
            'tripsNoDriver' => $trips->filter(fn ($tr) => ! $tr->driverName && $tr->tripStatus !== 'Done')->count(),
            'vehicles' => $trips->sum(fn ($tr) => Crm::i($tr->buses)),
            'pendingReqs' => count($pending),
        ];

        return ['ok' => true, 'kpis' => $kpis, 'orders' => $orderRows, 'awaiting' => $awaiting, 'pending' => $pending];
    }
}
