<?php

namespace App\Services;

use App\Models\BookingRequest;
use App\Models\Brn;
use App\Models\Group;
use App\Models\HotelBooking;
use App\Models\TransportOrder;
use App\Models\Trip;
use App\Support\Crm;
use App\Support\Dates;
use App\Support\Session;

/**
 * The approval workflow: agents submit bookings and change requests, operators
 * approve / return / reject them. Nothing an agent sends touches a live record
 * until an operator approves it.
 */
class RequestService
{
    public function __construct(
        private ActivityLogger $logger,
        private FileService $files,
        private GroupService $groups,
        private TransportService $transport,
    ) {}

    public function list(Session $session): array
    {
        $rows = BookingRequest::query()
            ->when($session->isAgent(), fn ($q) => $q->where('agentCode', $session->agentCode))
            ->orderByDesc('seq')   // newest first
            ->get()
            ->map(fn (BookingRequest $r) => $r->toRow())
            ->all();

        return ['ok' => true, 'rows' => $rows];
    }

    /** Sequential per-day request id: R-ddMMyyyy-N */
    public function newRequestId(): string
    {
        $prefix = 'R-'.Dates::now()->format('dmY').'-';
        $max = 0;
        foreach (BookingRequest::query()->where('requestId', 'like', $prefix.'%')->pluck('requestId') as $id) {
            $max = max($max, Crm::i(substr((string) $id, strlen($prefix))));
        }

        return $prefix.($max + 1);
    }

    /**
     * Normalise a client payload into the stored request shape.
     *
     * @return array{payload?: array, error?: string}
     */
    private function buildPayload(array $p): array
    {
        $group = is_array($p['group'] ?? null) ? $p['group'] : $p;

        $g = $this->groups->pickGroupFields($group);
        $pairs = $this->groups->normalizePairs($group);
        $g['groupPairs'] = $pairs['json'];
        $g['groupCode'] = $pairs['code'];
        $g['groupName'] = $pairs['name'] ?: Crm::s($group['groupName'] ?? '');
        $g['totalPax'] = (string) $this->groups->paxTotal($group);

        if ($g['groupName'] === '' || Crm::i($g['totalPax']) <= 0) {
            return ['error' => 'MISSING'];
        }
        if (! empty($group['ticketBase64'])) {
            $g['ticketUrl'] = $this->files->saveTicket($group['ticketBase64'], Crm::s($group['ticketName'] ?? ''));
        }
        if (! empty($group['hostIdBase64'])) {
            $g['hostIdUrl'] = $this->files->saveTicket($group['hostIdBase64'], Crm::s($group['hostIdName'] ?? ''));
        }

        return ['payload' => [
            'group' => $g,
            'hotels' => is_array($p['hotels'] ?? null) ? $p['hotels'] : [],
            'brn' => is_array($p['brn'] ?? null) ? $p['brn'] : [],
            'trips' => is_array($p['trips'] ?? null) ? $p['trips'] : [],
        ]];
    }

    public function submit(Session $session, array $p): array
    {
        $built = $this->buildPayload($p);
        if (isset($built['error'])) {
            return ['ok' => false, 'error' => $built['error']];
        }
        $payload = $built['payload'];

        // change request against a live group: the agent must own it
        $editGroupId = '';
        if (! empty($p['editGroupId'])) {
            $target = Group::find(Crm::s($p['editGroupId']));
            if (! $target) {
                return ['ok' => false, 'error' => 'NOTFOUND'];
            }
            if ($session->isAgent() && $target->agentCode !== $session->agentCode) {
                return ['ok' => false, 'error' => 'FORBIDDEN'];
            }
            $editGroupId = $target->groupId;
        }

        $type = $editGroupId !== ''
            ? 'EditGroup'
            : (($payload['group']['bookingType'] ?? '') === 'Individual' ? 'Individual' : 'NewGroup');

        // promoting an existing draft updates it in place, rather than orphaning it
        if (! empty($p['draftId'])) {
            $draft = BookingRequest::find(Crm::s($p['draftId']));
            if ($draft && $draft->status === 'Draft'
                && (! $session->isAgent() || $draft->agentCode === $session->agentCode)) {
                $draft->update([
                    'type' => $type,
                    'groupId' => $editGroupId,
                    'payloadJSON' => $this->encode($payload),
                    'status' => 'Pending',
                    'submittedAt' => Dates::nowStr(),
                ]);
                $this->logger->log($session, 'REQUEST_SUBMIT', 'Requests', $draft->requestId, 'from draft');

                return ['ok' => true, 'requestId' => $draft->requestId];
            }
        }

        $id = $this->newRequestId();
        BookingRequest::create([
            'requestId' => $id,
            'type' => $type,
            'groupId' => $editGroupId,
            'agentCode' => $session->agentCode,
            'payloadJSON' => $this->encode($payload),
            'status' => 'Pending',
            'submittedAt' => Dates::nowStr(),
            'reviewedBy' => '', 'reviewedAt' => '', 'reviewNote' => '',
        ]);
        $this->logger->log($session, 'REQUEST_SUBMIT', 'Requests', $id,
            $payload['group']['groupName'].' ['.count($payload['hotels']).'H/'
            .count($payload['brn']).'B/'.count($payload['trips']).'T]');

        return ['ok' => true, 'requestId' => $id];
    }

    /** An agent edits a Returned request and sends it back for review. */
    public function resubmit(Session $session, array $p): array
    {
        $request = BookingRequest::find(Crm::s($p['requestId'] ?? ''));
        if (! $request) {
            return ['ok' => false, 'error' => 'NOTFOUND'];
        }
        if ($session->isAgent() && $request->agentCode !== $session->agentCode) {
            return ['ok' => false, 'error' => 'FORBIDDEN'];
        }
        if ($request->status !== 'Returned') {
            return ['ok' => false, 'error' => 'NOT_RETURNED'];
        }

        $built = $this->buildPayload($p);
        if (isset($built['error'])) {
            return ['ok' => false, 'error' => $built['error']];
        }

        $request->update([
            'payloadJSON' => $this->encode($built['payload']),
            'type' => $request->type === 'EditGroup'
                ? 'EditGroup'
                : ((($built['payload']['group']['bookingType'] ?? '') === 'Individual') ? 'Individual' : 'NewGroup'),
            'status' => 'Pending',
            'submittedAt' => Dates::nowStr(),
            'reviewedBy' => '', 'reviewedAt' => '', 'reviewNote' => '',
        ]);
        $this->logger->log($session, 'REQUEST_RESUBMIT', 'Requests', $request->requestId,
            $built['payload']['group']['groupName']);

        return ['ok' => true, 'requestId' => $request->requestId];
    }

    public function cancel(Session $session, array $p): array
    {
        $request = BookingRequest::find(Crm::s($p['requestId'] ?? ''));
        if (! $request) {
            return ['ok' => false, 'error' => 'NOTFOUND'];
        }
        if ($session->isAgent() && $request->agentCode !== $session->agentCode) {
            return ['ok' => false, 'error' => 'FORBIDDEN'];
        }
        if ($request->status !== 'Pending') {
            return ['ok' => false, 'error' => 'NOT_PENDING'];
        }

        $request->update([
            'status' => 'Cancelled',
            'reviewedBy' => $session->username,
            'reviewedAt' => Dates::nowStr(),
        ]);
        $this->logger->log($session, 'REQUEST_CANCEL', 'Requests', $request->requestId);

        return ['ok' => true];
    }

    /**
     * A change request against a single trip or order. The "before" snapshot is
     * captured now so the operator reviews exactly what would change.
     */
    public function transportRequest(Session $session, array $p): array
    {
        $type = Crm::s($p['type'] ?? '');
        if (! in_array($type, ['EditTrip', 'EditOrder'], true)) {
            return ['ok' => false, 'error' => 'BAD_TYPE'];
        }

        $guard = $this->groups->guard($session, Crm::s($p['groupId'] ?? ''));
        if (isset($guard['err'])) {
            return $guard['err'];
        }
        /** @var Group $group */
        $group = $guard['group'];

        $before = [];
        if ($type === 'EditTrip' && ! empty($p['tripId'])) {
            $before = Trip::find(Crm::s($p['tripId']))?->toRow() ?? [];
        }
        if ($type === 'EditOrder' && ! empty($p['orderId'])) {
            $before = TransportOrder::find(Crm::s($p['orderId']))?->toRow() ?? [];
        }

        $id = $this->newRequestId();
        BookingRequest::create([
            'requestId' => $id,
            'type' => $type,
            'groupId' => $group->groupId,
            'agentCode' => $session->isAgent() ? $session->agentCode : (string) $group->agentCode,
            'payloadJSON' => $this->encode([
                'targetId' => Crm::s($p['tripId'] ?? $p['orderId'] ?? ''),
                'fields' => is_array($p['fields'] ?? null) ? $p['fields'] : [],
                'before' => $before,
                'note' => Crm::s($p['note'] ?? ''),
            ]),
            'status' => 'Pending',
            'submittedAt' => Dates::nowStr(),
            'reviewedBy' => '', 'reviewedAt' => '', 'reviewNote' => '',
        ]);
        $this->logger->log($session, 'REQUEST_SUBMIT', 'Requests', $id,
            $type.' for '.Crm::s($p['tripId'] ?? $p['orderId'] ?? ''), $group->groupId);

        return ['ok' => true, 'requestId' => $id];
    }

    /**
     * Drafts exist so a half-finished booking survives a refresh, a logout, or
     * a lost connection. Validation is deliberately skipped — a draft is
     * allowed to be incomplete, and it is never reviewed.
     */
    public function saveDraft(Session $session, array $p): array
    {
        $payload = [
            'group' => $p['group'] ?? [],
            'hotels' => $p['hotels'] ?? [],
            'brn' => $p['brn'] ?? [],
            'trips' => $p['trips'] ?? [],
            'wizard' => [
                'step' => Crm::i($p['step'] ?? 1) ?: 1,
                'kind' => Crm::s($p['kind'] ?? '') ?: 'Group',
                'brnSkip' => (bool) ($p['brnSkip'] ?? false),
                'cateringSkip' => (bool) ($p['cateringSkip'] ?? false),
            ],
        ];
        $agentCode = $session->isAgent() ? $session->agentCode : Crm::s($p['agentCode'] ?? '');

        if (! empty($p['requestId'])) {
            $draft = BookingRequest::find(Crm::s($p['requestId']));
            if (! $draft) {
                return ['ok' => false, 'error' => 'NOTFOUND'];
            }
            if ($session->isAgent() && $draft->agentCode !== $session->agentCode) {
                return ['ok' => false, 'error' => 'FORBIDDEN'];
            }
            if ($draft->status !== 'Draft') {
                return ['ok' => false, 'error' => 'NOT_DRAFT'];
            }
            $draft->update([
                'payloadJSON' => $this->encode($payload),
                'agentCode' => $agentCode,
                'submittedAt' => Dates::nowStr(),
            ]);

            return ['ok' => true, 'requestId' => $draft->requestId];
        }

        $id = $this->newRequestId();
        BookingRequest::create([
            'requestId' => $id,
            'type' => ($p['kind'] ?? '') === 'Individual' ? 'Individual' : 'NewGroup',
            'groupId' => Crm::s($p['editGroupId'] ?? ''),
            'agentCode' => $agentCode,
            'payloadJSON' => $this->encode($payload),
            'status' => 'Draft',
            'submittedAt' => Dates::nowStr(),
            'reviewedBy' => '', 'reviewedAt' => '', 'reviewNote' => '',
        ]);
        $this->logger->log($session, 'REQUEST_DRAFT', 'Requests', $id, 'draft saved');

        return ['ok' => true, 'requestId' => $id];
    }

    public function deleteDraft(Session $session, array $p): array
    {
        $draft = BookingRequest::find(Crm::s($p['requestId'] ?? ''));
        if (! $draft) {
            return ['ok' => false, 'error' => 'NOTFOUND'];
        }
        if ($draft->status !== 'Draft') {
            return ['ok' => false, 'error' => 'NOT_DRAFT'];
        }
        if ($session->isAgent() && $draft->agentCode !== $session->agentCode) {
            return ['ok' => false, 'error' => 'FORBIDDEN'];
        }
        $draft->delete();
        $this->logger->log($session, 'REQUEST_DRAFT_DELETE', 'Requests', (string) $p['requestId']);

        return ['ok' => true];
    }

    // ── REVIEW ───────────────────────────────────────────────

    public function review(Session $session, array $p): array
    {
        $request = BookingRequest::find(Crm::s($p['requestId'] ?? ''));
        if (! $request) {
            return ['ok' => false, 'error' => 'NOTFOUND'];
        }
        if ($request->status !== 'Pending') {
            return ['ok' => false, 'error' => 'NOT_PENDING'];
        }

        $decision = Crm::s($p['decision'] ?? '');
        $note = Crm::s($p['note'] ?? '');

        if ($decision === 'reject' || $decision === 'return') {
            $status = $decision === 'reject' ? 'Rejected' : 'Returned';
            $request->update([
                'status' => $status,
                'reviewedBy' => $session->username,
                'reviewedAt' => Dates::nowStr(),
                'reviewNote' => $note,
            ]);
            $this->logger->log($session, 'REQUEST_'.strtoupper($decision), 'Requests', $request->requestId, $note);

            return ['ok' => true, 'decision' => $decision === 'reject' ? 'rejected' : 'returned'];
        }

        if ($decision !== 'approve') {
            return ['ok' => false, 'error' => 'BAD_DECISION'];
        }

        $payload = json_decode((string) $request->payloadJSON, true);
        if (! is_array($payload)) {
            return ['ok' => false, 'error' => 'BAD_PAYLOAD'];
        }

        return match ($request->type) {
            'EditTrip', 'EditOrder' => $this->applyTransportEdit($session, $request, $payload, $note),
            'EditGroup' => $this->applyGroupEdit($session, $request, $payload, $note),
            default => $this->applyNewBooking($session, $request, $payload, $note),
        };
    }

    private function applyTransportEdit(Session $session, BookingRequest $request, array $payload, string $note): array
    {
        $fields = is_array($payload['fields'] ?? null) ? $payload['fields'] : [];
        $targetId = Crm::s($payload['targetId'] ?? '');
        $groupId = (string) $request->groupId;

        $applied = $request->type === 'EditTrip'
            ? $this->transport->saveTrip($session, $fields + ['groupId' => $groupId]
                + ($targetId !== '' ? ['tripId' => $targetId] : []))
            : $this->transport->saveOrder($session, $fields + ['groupId' => $groupId]
                + ($targetId !== '' ? ['orderId' => $targetId] : []));

        // a rejected patch leaves the request Pending, so nothing is lost
        if (! ($applied['ok'] ?? false)) {
            return $applied;
        }

        $request->update([
            'status' => 'Approved',
            'reviewedBy' => $session->username,
            'reviewedAt' => Dates::nowStr(),
            'reviewNote' => $note,
        ]);
        $this->logger->log($session, 'REQUEST_'.strtoupper($request->type).'_APPLY', 'Requests',
            $request->requestId, 'applied to '.($targetId ?: 'new'), $groupId);

        return ['ok' => true, 'decision' => 'approved', 'groupId' => $groupId];
    }

    /** Apply an agent's edit to the live group, replacing its child records. */
    private function applyGroupEdit(Session $session, BookingRequest $request, array $payload, string $note): array
    {
        $target = Group::find((string) $request->groupId);
        if (! $target) {
            return ['ok' => false, 'error' => 'NOTFOUND'];
        }
        $group = is_array($payload['group'] ?? null) ? $payload['group'] : $payload;

        $fields = $this->groups->pickGroupFields($group);
        $pairs = $this->groups->normalizePairs($group);
        $fields['groupPairs'] = $pairs['json'];
        $fields['groupCode'] = $pairs['code'];
        $fields['groupName'] = $pairs['name'] ?: Crm::s($group['groupName'] ?? '');
        $fields['totalPax'] = (string) $this->groups->paxTotal($group);
        $fields['ticketUrl'] = ! empty($group['ticketBase64'])
            ? $this->files->saveTicket($group['ticketBase64'], Crm::s($group['ticketName'] ?? ''))
            : (Crm::s($group['ticketUrl'] ?? '') ?: (string) $target->ticketUrl);
        $fields['hostIdUrl'] = ! empty($group['hostIdBase64'])
            ? $this->files->saveTicket($group['hostIdBase64'], Crm::s($group['hostIdName'] ?? ''))
            : (Crm::s($group['hostIdUrl'] ?? '') ?: (string) $target->hostIdUrl);

        $target->update($fields);

        // the requested set replaces the current one wholesale
        HotelBooking::query()->where('groupId', $target->groupId)->delete();
        Brn::query()->where('groupId', $target->groupId)->delete();
        Trip::query()->where('groupId', $target->groupId)->delete();
        $counts = $this->groups->createChildren($target->groupId,
            $payload['hotels'] ?? [], $payload['brn'] ?? [], $payload['trips'] ?? []);

        $request->update([
            'status' => 'Approved',
            'reviewedBy' => $session->username,
            'reviewedAt' => Dates::nowStr(),
            'reviewNote' => $note,
        ]);
        $this->logger->log($session, 'REQUEST_EDIT_APPLY', 'Groups', $target->groupId,
            'req '.$request->requestId.' ['.$counts['h'].'H/'.$counts['b'].'B/'.$counts['t'].'T]');

        return ['ok' => true, 'decision' => 'approved', 'groupId' => $target->groupId, 'fileNo' => (string) $target->fileNo];
    }

    private function applyNewBooking(Session $session, BookingRequest $request, array $payload, string $note): array
    {
        $group = is_array($payload['group'] ?? null) ? $payload['group'] : $payload;

        $res = $this->groups->createWithChildren($session, $group,
            $payload['hotels'] ?? [], $payload['brn'] ?? [], $payload['trips'] ?? [],
            [
                'agentCode' => (string) $request->agentCode,
                'status' => 'Confirmed',
                'createdBy' => (string) $request->agentCode,
                'approvedBy' => $session->username,
            ]);

        $request->update([
            'status' => 'Approved',
            'groupId' => $res['groupId'],
            'reviewedBy' => $session->username,
            'reviewedAt' => Dates::nowStr(),
            'reviewNote' => $note,
        ]);
        $this->logger->log($session, 'REQUEST_APPROVE', 'Requests', $request->requestId,
            '→ group '.$res['fileNo'].' ['.$res['counts']['h'].'H/'.$res['counts']['b'].'B/'.$res['counts']['t'].'T]');

        return ['ok' => true, 'decision' => 'approved', 'groupId' => $res['groupId'], 'fileNo' => $res['fileNo']];
    }

    private function encode(array $payload): string
    {
        return json_encode($payload, JSON_UNESCAPED_UNICODE);
    }
}
