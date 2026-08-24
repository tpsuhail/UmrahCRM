<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Brn;
use App\Models\Group;
use App\Models\HotelBooking;
use App\Models\TransportOrder;
use App\Models\Trip;
use App\Support\Crm;
use App\Support\Dates;
use App\Support\Ids;
use App\Support\Session;

class GroupService
{
    public function __construct(
        private ActivityLogger $logger,
        private FileService $files,
        private CascadeService $cascade,
    ) {}

    /** Verify the group exists and the caller may touch it. */
    public function guard(Session $session, ?string $groupId): array
    {
        $group = $groupId ? Group::find($groupId) : null;
        if (! $group) {
            return ['err' => ['ok' => false, 'error' => 'NOTFOUND']];
        }
        if ($session->isAgent() && $group->agentCode !== $session->agentCode) {
            $this->logger->log($session, 'FORBIDDEN', 'Groups', $groupId, 'cross-agent access attempt');

            return ['err' => ['ok' => false, 'error' => 'FORBIDDEN']];
        }

        return ['group' => $group];
    }

    public function list(Session $session): array
    {
        $query = Group::query();
        if ($session->isAgent()) {
            $query->where('agentCode', $session->agentCode);
        }

        return ['ok' => true, 'rows' => $query->get()->map(fn (Group $g) => $g->toRow())->all()];
    }

    /** Total pax = adults+children+infants when given, else the stated total. */
    public function paxTotal(array $p): int
    {
        $sum = Crm::i($p['adults'] ?? 0) + Crm::i($p['children'] ?? 0) + Crm::i($p['infants'] ?? 0);

        return $sum > 0 ? $sum : Crm::i($p['totalPax'] ?? 0);
    }

    public function pickGroupFields(array $p): array
    {
        $out = [];
        foreach (Crm::GROUP_FIELDS as $f) {
            $out[$f] = Crm::s($p[$f] ?? '');
        }

        return $out;
    }

    /**
     * A booking may carry several code/name pairs. Normalise them into the
     * stored JSON plus the flattened code and name the tables display.
     *
     * @return array{pairs: array, code: string, name: string, json: string}
     */
    public function normalizePairs(array $group): array
    {
        $pairs = is_array($group['groupPairs'] ?? null) ? $group['groupPairs'] : [];
        if (! $pairs && (($group['groupCode'] ?? '') !== '' || ($group['groupName'] ?? '') !== '')) {
            $pairs = [['code' => Crm::s($group['groupCode'] ?? ''), 'name' => Crm::s($group['groupName'] ?? '')]];
        }
        $pairs = array_values(array_filter($pairs, fn ($p) => ($p['code'] ?? '') !== '' || ($p['name'] ?? '') !== ''));

        $codes = array_filter(array_map(fn ($p) => Crm::s($p['code'] ?? ''), $pairs), 'strlen');
        $names = array_filter(array_map(fn ($p) => Crm::s($p['name'] ?? ''), $pairs), 'strlen');

        return [
            'pairs' => $pairs,
            'code' => implode(', ', $codes),
            'name' => implode(' / ', $names),
            'json' => json_encode($pairs, JSON_UNESCAPED_UNICODE),
        ];
    }

    public function nextFileNo(): string
    {
        $max = 0;
        foreach (Group::query()->pluck('fileNo') as $fileNo) {
            $n = Crm::i($fileNo);
            if ($n > $max) {
                $max = $n;
            }
        }

        return (string) ($max + 1);
    }

    /** Create the hotel / BRN / trip rows that hang off a group. */
    public function createChildren(string $groupId, array $hotels, array $brn, array $trips): array
    {
        foreach ($hotels as $h) {
            HotelBooking::create([
                'bookingId' => Ids::make('H'),
                'groupId' => $groupId,
                'city' => Crm::s($h['city'] ?? ''),
                'hotelName' => Crm::s($h['hotelName'] ?? ''),
                'rooms' => Crm::s($h['rooms'] ?? ''),
                'checkIn' => Crm::s($h['checkIn'] ?? ''),
                'checkOut' => Crm::s($h['checkOut'] ?? ''),
                'nights' => Dates::calcNights($h['checkIn'] ?? '', $h['checkOut'] ?? ''),
                'status' => 'Confirmed',
                'notes' => Crm::s($h['notes'] ?? ''),
                'rsvNo' => Crm::s($h['rsvNo'] ?? ''),
            ]);
        }

        foreach ($brn as $b) {
            Brn::create([
                'brnId' => Ids::make('B'),
                'groupId' => $groupId,
                'bookingId' => '',
                'brnType' => ($b['brnType'] ?? '') === 'Catering' ? 'Catering' : 'Hotel',
                'hotelName' => Crm::s($b['hotelName'] ?? ''),
                'checkIn' => Crm::s($b['checkIn'] ?? ''),
                'checkOut' => Crm::s($b['checkOut'] ?? ''),
                'brnNumber' => Crm::s($b['brnNumber'] ?? ''),
                'roomsCount' => Crm::s($b['roomsCount'] ?? ''),
            ]);
        }

        foreach ($trips as $tr) {
            Trip::create([
                'tripId' => Ids::make('TR'),
                'orderId' => Crm::s($tr['orderId'] ?? ''),
                'groupId' => $groupId,
                'date' => Crm::s($tr['date'] ?? ''),
                'day' => Dates::dayNameOf($tr['date'] ?? ''),
                'tripType' => Crm::s($tr['tripType'] ?? ''),
                'transportMode' => Crm::s($tr['transportMode'] ?? ''),
                'vehicleType' => Crm::s($tr['vehicleType'] ?? ''),
                'from' => Crm::s($tr['from'] ?? ''),
                'fromDetail' => Crm::s($tr['fromDetail'] ?? ''),
                'to' => Crm::s($tr['to'] ?? ''),
                'toDetail' => Crm::s($tr['toDetail'] ?? ''),
                'time' => Crm::s($tr['time'] ?? ''),
                'flightNo' => Crm::s($tr['flightNo'] ?? ''),
                'buses' => Crm::s($tr['buses'] ?? ''),
                'pax' => Crm::s($tr['pax'] ?? ''),
                'driverName' => Crm::s($tr['driverName'] ?? ''),
                'driverMobile' => Crm::s($tr['driverMobile'] ?? ''),
                'plateNo' => Crm::s($tr['plateNo'] ?? ''),
                'bookingStatus' => Crm::s($tr['bookingStatus'] ?? '') ?: 'Pending',
                'tripStatus' => Crm::s($tr['tripStatus'] ?? '') ?: 'Pending',
                'notes' => Crm::s($tr['notes'] ?? ''),
            ]);
        }

        return ['h' => count($hotels), 'b' => count($brn), 't' => count($trips)];
    }

    /**
     * Shared group creator, used both when an operator books directly and when
     * an agent's request is approved.
     *
     * @param  array{fileNo?: string, agentCode: string, status?: string, createdBy?: string, approvedBy?: string}  $meta
     */
    public function createWithChildren(
        Session $session,
        array $group,
        array $hotels,
        array $brn,
        array $trips,
        array $meta
    ): array {
        $id = Ids::make('G');
        $row = $this->pickGroupFields($group);

        $pairs = $this->normalizePairs($group);
        $row['groupPairs'] = $pairs['json'];
        $row['groupCode'] = $pairs['code'];
        $row['groupName'] = $pairs['name'] ?: Crm::s($group['groupName'] ?? '');
        $row['totalPax'] = (string) $this->paxTotal($group);

        $row['ticketUrl'] = ! empty($group['ticketBase64'])
            ? $this->files->saveTicket($group['ticketBase64'], Crm::s($group['ticketName'] ?? ''))
            : Crm::s($group['ticketUrl'] ?? '');
        $row['hostIdUrl'] = ! empty($group['hostIdBase64'])
            ? $this->files->saveTicket($group['hostIdBase64'], Crm::s($group['hostIdName'] ?? ''))
            : Crm::s($group['hostIdUrl'] ?? '');

        $row['groupId'] = $id;
        $row['fileNo'] = $meta['fileNo'] ?? $this->nextFileNo();
        $row['agentCode'] = $meta['agentCode'];
        $row['status'] = $meta['status'] ?? 'Confirmed';
        $row['stage'] = 'Visa';
        $row['stageSince'] = Dates::nowStr();
        $row['createdBy'] = $meta['createdBy'] ?? $session->username;
        $row['approvedBy'] = $meta['approvedBy'] ?? '';
        $row['createdAt'] = Dates::nowStr();

        Group::create($row);
        $counts = $this->createChildren($id, $hotels, $brn, $trips);

        return ['groupId' => $id, 'fileNo' => $row['fileNo'], 'counts' => $counts];
    }

    /** Operator creates a full group (with hotels/BRN/trips) via the wizard. */
    public function createFull(Session $session, array $p): array
    {
        $group = is_array($p['group'] ?? null) ? $p['group'] : [];
        $pairs = $this->normalizePairs($group);
        if ($pairs['name'] === '' && Crm::s($group['groupName'] ?? '') === '') {
            return ['ok' => false, 'error' => 'MISSING'];
        }
        if ($this->paxTotal($group) <= 0) {
            return ['ok' => false, 'error' => 'MISSING'];
        }
        if (($err = $this->requireActiveAgent($p['agentCode'] ?? '')) !== null) {
            return $err;
        }

        $res = $this->createWithChildren(
            $session,
            $group,
            is_array($p['hotels'] ?? null) ? $p['hotels'] : [],
            is_array($p['brn'] ?? null) ? $p['brn'] : [],
            is_array($p['trips'] ?? null) ? $p['trips'] : [],
            [
                'agentCode' => Crm::s($p['agentCode']),
                'status' => 'Confirmed',
                'createdBy' => $session->username,
                'approvedBy' => $session->username,
            ],
        );

        $this->logger->log($session, 'GROUP_CREATE_FULL', 'Groups', $res['groupId'],
            ($pairs['name'] ?: Crm::s($group['groupName'] ?? ''))
            .' ['.$res['counts']['h'].'H/'.$res['counts']['b'].'B/'.$res['counts']['t'].'T]');

        return ['ok' => true, 'groupId' => $res['groupId'], 'fileNo' => $res['fileNo']];
    }

    public function save(Session $session, array $p): array
    {
        if (Crm::s($p['groupName'] ?? '') === '') {
            return ['ok' => false, 'error' => 'MISSING'];
        }

        $fields = $this->pickGroupFields($p);
        $fields['totalPax'] = (string) $this->paxTotal($p);
        if (Crm::i($fields['totalPax']) <= 0) {
            return ['ok' => false, 'error' => 'MISSING'];
        }

        if (! empty($p['groupId'])) {
            $existing = Group::find(Crm::s($p['groupId']));
            if (! $existing) {
                return ['ok' => false, 'error' => 'NOTFOUND'];
            }
            $fields['agentCode'] = Crm::s($p['agentCode'] ?? '') ?: $existing->agentCode;
            $fields['fileNo'] = Crm::s($p['fileNo'] ?? '') ?: $existing->fileNo;

            // work out the downstream impact BEFORE the group row changes
            $changes = ($p['cascade'] ?? null) === false
                ? []
                : $this->cascade->compute($existing->groupId, $fields);

            $before = $existing->toRow();
            $existing->update($fields);

            $diffs = [];
            foreach ($fields as $k => $v) {
                if ((string) ($before[$k] ?? '') !== (string) $v) {
                    $diffs[] = $k.': '.(($before[$k] ?? '') ?: '–').' → '.($v ?: '–');
                }
            }
            $this->logger->log($session, 'GROUP_UPDATE', 'Groups', $existing->groupId,
                $diffs ? implode(' | ', $diffs) : $fields['groupName'], $existing->groupId);

            $applied = $this->cascade->apply($session, $existing->groupId, $changes);

            return ['ok' => true, 'groupId' => $existing->groupId, 'cascaded' => $applied];
        }

        if (($err = $this->requireActiveAgent($p['agentCode'] ?? '')) !== null) {
            return $err;
        }

        $id = Ids::make('G');
        $row = $fields + [
            'groupId' => $id,
            'fileNo' => Crm::s($p['fileNo'] ?? '') ?: $this->nextFileNo(),
            'agentCode' => Crm::s($p['agentCode']),
            'status' => 'New',
            'createdBy' => $session->username,
            'approvedBy' => '',
            'createdAt' => Dates::nowStr(),
        ];
        Group::create($row);
        $this->logger->log($session, 'GROUP_CREATE', 'Groups', $id,
            $row['groupName'].' (file '.$row['fileNo'].')');

        return ['ok' => true, 'groupId' => $id, 'fileNo' => $row['fileNo']];
    }

    public function setStatus(Session $session, array $p): array
    {
        $status = Crm::s($p['status'] ?? '');
        if (! in_array($status, Crm::GROUP_STATUSES, true)) {
            return ['ok' => false, 'error' => 'BAD_STATUS'];
        }
        $group = Group::find(Crm::s($p['groupId'] ?? ''));
        if (! $group) {
            return ['ok' => false, 'error' => 'NOTFOUND'];
        }
        $from = $group->status;
        $group->update(['status' => $status]);
        $this->logger->log($session, 'GROUP_STATUS', 'Groups', $group->groupId, $from.' → '.$status);

        return ['ok' => true];
    }

    /**
     * Move a group along Visa → Transport → Operation → Completed.
     * Forward moves are gated on real progress; backward moves are always
     * allowed (they are corrective, and they are logged).
     */
    public function setStage(Session $session, array $p): array
    {
        $stage = Crm::s($p['stage'] ?? '');
        if (! in_array($stage, Crm::GROUP_STAGES, true)) {
            return ['ok' => false, 'error' => 'BAD_STAGE'];
        }
        $group = Group::find(Crm::s($p['groupId'] ?? ''));
        if (! $group) {
            return ['ok' => false, 'error' => 'NOTFOUND'];
        }

        $from = in_array($group->stage, Crm::GROUP_STAGES, true) ? $group->stage : 'Visa';
        $fromIdx = array_search($from, Crm::GROUP_STAGES, true);
        $toIdx = array_search($stage, Crm::GROUP_STAGES, true);

        if ($toIdx > $fromIdx) {
            if ($stage === 'Transport' && $group->visaStatus !== 'Done') {
                return ['ok' => false, 'error' => 'NEED_VISA'];
            }
            if ($stage === 'Operation') {
                $hasConfirmed = TransportOrder::query()
                    ->where('groupId', $group->groupId)->where('status', 'Booked')->exists();
                if (! $hasConfirmed) {
                    return ['ok' => false, 'error' => 'NEED_ORDER'];
                }
            }
            if ($stage === 'Completed') {
                $live = Trip::query()
                    ->where('groupId', $group->groupId)
                    ->where('tripStatus', '!=', 'Cancelled')
                    ->pluck('tripStatus');
                $allDone = $live->isNotEmpty() && $live->every(fn ($s) => $s === 'Done');
                if (! $allDone) {
                    return ['ok' => false, 'error' => 'NEED_TRIPS'];
                }
            }
        }

        $group->update(['stage' => $stage, 'stageSince' => Dates::nowStr()]);
        $note = Crm::s($p['note'] ?? '');
        $this->logger->log($session, 'GROUP_STAGE', 'Groups', $group->groupId,
            $from.' → '.$stage.($note !== '' ? ' | '.$note : ''));

        return ['ok' => true];
    }

    /** Archive / unarchive. Only settled groups can be archived. */
    public function archive(Session $session, array $p): array
    {
        $group = Group::find(Crm::s($p['groupId'] ?? ''));
        if (! $group) {
            return ['ok' => false, 'error' => 'NOTFOUND'];
        }
        $wanted = (bool) ($p['archived'] ?? false);
        if ($wanted && ! in_array($group->status, ['Departed', 'Closed', 'Cancelled'], true)) {
            return ['ok' => false, 'error' => 'NOT_ARCHIVABLE'];
        }
        $group->update(['archived' => $wanted ? 'yes' : '']);
        $this->logger->log($session, $wanted ? 'GROUP_ARCHIVE' : 'GROUP_UNARCHIVE', 'Groups',
            $group->groupId, $group->fileNo.' ('.$group->status.')', $group->groupId);

        return ['ok' => true];
    }

    public function activityLog(Session $session, array $p): array
    {
        $guard = $this->guard($session, Crm::s($p['groupId'] ?? ''));
        if (isset($guard['err'])) {
            return $guard['err'];
        }

        return ['ok' => true, 'rows' => $this->logger->forGroup(Crm::s($p['groupId']))];
    }

    /** Toggle the visa sub-status within the Visa stage. */
    public function setVisa(Session $session, array $p): array
    {
        $group = Group::find(Crm::s($p['groupId'] ?? ''));
        if (! $group) {
            return ['ok' => false, 'error' => 'NOTFOUND'];
        }
        $done = ! empty($p['done']);
        $group->update(['visaStatus' => $done ? 'Done' : '']);
        $note = Crm::s($p['note'] ?? '');
        $this->logger->log($session, 'GROUP_VISA', 'Groups', $group->groupId,
            ($done ? 'Visa Done' : 'Visa Waiting').($note !== '' ? ' | '.$note : ''), $group->groupId);

        return ['ok' => true];
    }

    public function setTransportBy(Session $session, array $p): array
    {
        $value = Crm::s($p['transportBy'] ?? '');
        if (! in_array($value, ['Agent', 'Operator'], true)) {
            return ['ok' => false, 'error' => 'BAD_VALUE'];
        }
        $group = Group::find(Crm::s($p['groupId'] ?? ''));
        if (! $group) {
            return ['ok' => false, 'error' => 'NOTFOUND'];
        }
        $from = (string) $group->transportBy;
        $group->update(['transportBy' => $value]);
        $this->logger->log($session, 'GROUP_TRANSPORTBY', 'Groups', $group->groupId, $from.' → '.$value);

        return ['ok' => true];
    }

    /** Follow-up board: every live group by stage, with its trip progress. */
    public function followup(Session $session): array
    {
        $query = Group::query()->where('status', '!=', 'Cancelled');
        if ($session->isAgent()) {
            $query->where('agentCode', $session->agentCode);
        }
        $groups = $query->get();

        $agentNames = Agent::query()->pluck('agentName', 'agentCode');

        // cancelled trips are settled, so they don't count toward progress
        $progress = [];
        foreach (Trip::query()->where('tripStatus', '!=', 'Cancelled')->get(['groupId', 'tripStatus']) as $tr) {
            $progress[$tr->groupId] ??= ['total' => 0, 'done' => 0];
            $progress[$tr->groupId]['total']++;
            if ($tr->tripStatus === 'Done') {
                $progress[$tr->groupId]['done']++;
            }
        }

        $orderCount = [];
        $confirmedCount = [];
        foreach (TransportOrder::query()->get(['groupId', 'status']) as $o) {
            $orderCount[$o->groupId] = ($orderCount[$o->groupId] ?? 0) + 1;
            if ($o->status === 'Booked') {
                $confirmedCount[$o->groupId] = ($confirmedCount[$o->groupId] ?? 0) + 1;
            }
        }

        $today = Dates::todayUtc();

        $rows = $groups->map(function (Group $g) use ($agentNames, $progress, $orderCount, $confirmedCount, $today) {
            $pr = $progress[$g->groupId] ?? ['total' => 0, 'done' => 0];
            $since = Dates::parseYmd(str_replace('/', '-', explode(' ', (string) $g->stageSince)[0]));
            $daysInStage = ($since !== null && $today !== null)
                ? (string) max(0, (int) round(($today - $since) / 86400))
                : '';

            return [
                'groupId' => $g->groupId,
                'fileNo' => (string) $g->fileNo,
                'groupName' => (string) $g->groupName,
                'groupCode' => (string) $g->groupCode,
                'agentName' => $agentNames[$g->agentCode] ?? (string) $g->agentCode,
                'agentCode' => (string) $g->agentCode,
                'totalPax' => (string) $g->totalPax,
                'bookingType' => (string) $g->bookingType ?: 'Group',
                'arrivalDate' => (string) $g->arrivalDate,
                'departureDate' => (string) $g->departureDate,
                'status' => (string) $g->status,
                'stage' => in_array($g->stage, Crm::GROUP_STAGES, true) ? $g->stage : 'Visa',
                'visaStatus' => (string) $g->visaStatus,
                'transportBy' => (string) $g->transportBy,
                'ordersConfirmed' => $confirmedCount[$g->groupId] ?? 0,
                'tripsTotal' => $pr['total'],
                'tripsDone' => $pr['done'],
                'ordersCount' => $orderCount[$g->groupId] ?? 0,
                'daysInStage' => $daysInStage,
            ];
        })->all();

        return ['ok' => true, 'rows' => $rows];
    }

    private function requireActiveAgent(mixed $agentCode): ?array
    {
        $code = Crm::s($agentCode);
        if ($code === '') {
            return ['ok' => false, 'error' => 'NO_AGENT'];
        }
        $agent = Agent::find($code);
        if (! $agent || $agent->status !== 'active') {
            return ['ok' => false, 'error' => 'NO_AGENT'];
        }

        return null;
    }
}
