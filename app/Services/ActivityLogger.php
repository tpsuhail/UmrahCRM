<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Support\Dates;
use App\Support\Session;
use Throwable;

class ActivityLogger
{
    /** Audit trail. Logging must never be the reason a request fails. */
    public function log(
        ?Session $session,
        string $action,
        string $entity = '',
        string $entityId = '',
        string $details = '',
        string $groupId = ''
    ): void {
        try {
            ActivityLog::create([
                'timestamp' => Dates::nowStr(),
                'userId' => $session?->userId ?? '',
                'username' => $session?->username ?? 'system',
                'action' => $action,
                'entity' => $entity,
                'entityId' => $entityId,
                'details' => $details,
                'groupId' => $groupId ?: ($entity === 'Groups' ? $entityId : ''),
            ]);
        } catch (Throwable) {
            // never block on logging
        }
    }

    public function list(int $limit = 100): array
    {
        return ActivityLog::query()
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (ActivityLog $r) => $r->toRow())
            ->all();
    }

    /** Activity for one group, newest first. */
    public function forGroup(string $groupId, int $limit = 200): array
    {
        return ActivityLog::query()
            ->where('groupId', $groupId)
            ->orderByDesc('timestamp')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (ActivityLog $r) => $r->toRow())
            ->all();
    }
}
