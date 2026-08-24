<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Master;
use App\Models\User;
use App\Support\Crm;
use App\Support\Dates;
use App\Support\Ids;
use App\Support\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminService
{
    public function __construct(
        private ActivityLogger $logger,
        private FileService $files,
    ) {}

    // ── AGENTS ───────────────────────────────────────────────

    public function agentsList(): array
    {
        return ['ok' => true, 'rows' => Agent::rows()];
    }

    public function agentsSave(Session $session, array $p): array
    {
        $name = Crm::s($p['agentName'] ?? '');
        if ($name === '') {
            return ['ok' => false, 'error' => 'MISSING_NAME'];
        }

        $fields = [
            'agentName' => $name,
            'country' => Crm::s($p['country'] ?? ''),
            'contactName' => Crm::s($p['contactName'] ?? ''),
            'mobile' => Crm::s($p['mobile'] ?? ''),
            'email' => Crm::s($p['email'] ?? ''),
            'notes' => Crm::s($p['notes'] ?? ''),
        ];

        if (! empty($p['agentCode'])) {
            $agent = Agent::find(Crm::s($p['agentCode']));
            if (! $agent) {
                return ['ok' => false, 'error' => 'NOTFOUND'];
            }
            $agent->update($fields + ['status' => Crm::s($p['status'] ?? '') ?: 'active']);
            $this->logger->log($session, 'AGENT_UPDATE', 'Agents', $agent->agentCode, $name);

            return ['ok' => true, 'agentCode' => $agent->agentCode];
        }

        // agentCode is the primary key, so it doubles as the human-facing code
        $code = Ids::make('AG');
        Agent::create($fields + [
            'agentCode' => $code,
            'status' => 'active',
            'createdAt' => Dates::nowStr(),
        ]);
        $this->logger->log($session, 'AGENT_CREATE', 'Agents', $code, $name);

        return ['ok' => true, 'agentCode' => $code];
    }

    // ── USERS ────────────────────────────────────────────────

    public function usersList(): array
    {
        // hashes never leave the server
        $rows = User::query()->get()->map(fn (User $u) => [
            'userId' => $u->userId,
            'username' => $u->username,
            'role' => $u->role,
            'agentCode' => (string) $u->agentCode,
            'displayName' => (string) $u->displayName,
            'status' => $u->status,
            'createdAt' => (string) $u->createdAt,
            'lastLogin' => (string) $u->lastLogin,
            'department' => (string) $u->department,
        ])->all();

        return ['ok' => true, 'rows' => $rows];
    }

    public function usersSave(Session $session, array $p): array
    {
        $role = ($p['role'] ?? '') === 'agent' ? 'agent' : 'operator';

        if (! empty($p['userId'])) {
            $user = User::find(Crm::s($p['userId']));
            if (! $user) {
                return ['ok' => false, 'error' => 'NOTFOUND'];
            }
            $user->update([
                'role' => $role,
                'agentCode' => $role === 'agent' ? Crm::s($p['agentCode'] ?? '') : '',
                'displayName' => Crm::s($p['displayName'] ?? ''),
                'status' => Crm::s($p['status'] ?? '') ?: 'active',
                'department' => $role === 'operator' ? Crm::s($p['department'] ?? '') : '',
            ]);
            $this->logger->log($session, 'USER_UPDATE', 'Users', $user->userId, Crm::s($p['username'] ?? ''));

            return ['ok' => true];
        }

        $username = Str::lower(trim(Crm::s($p['username'] ?? '')));
        $password = (string) ($p['password'] ?? '');
        if ($username === '' || $password === '') {
            return ['ok' => false, 'error' => 'MISSING'];
        }
        if (strlen($password) < 6) {
            return ['ok' => false, 'error' => 'WEAK'];
        }
        if (User::query()->whereRaw('LOWER(username) = ?', [$username])->exists()) {
            return ['ok' => false, 'error' => 'DUPLICATE'];
        }
        if ($role === 'agent' && empty($p['agentCode'])) {
            return ['ok' => false, 'error' => 'NO_AGENT'];
        }

        $id = Ids::make('U');
        User::create([
            'userId' => $id,
            'username' => $username,
            'passwordHash' => Hash::make($password),
            'role' => $role,
            'agentCode' => $role === 'agent' ? Crm::s($p['agentCode']) : '',
            'displayName' => Crm::s($p['displayName'] ?? '') ?: $username,
            'status' => 'active',
            'createdAt' => Dates::nowStr(),
            'lastLogin' => '',
            'department' => $role === 'operator' ? Crm::s($p['department'] ?? '') : '',
        ]);
        $this->logger->log($session, 'USER_CREATE', 'Users', $id, $username.' ('.$role.')');

        return ['ok' => true, 'userId' => $id];
    }

    // ── MASTERS ──────────────────────────────────────────────

    public function mastersList(Session $session, array $p): array
    {
        $query = Master::query();
        if (! empty($p['type'])) {
            $query->where('type', Crm::s($p['type']));
        }
        if ($session->isAgent()) {
            $query->where('active', 'yes');
        }

        return ['ok' => true, 'rows' => $query->get()->map(fn (Master $m) => $m->toRow())->all()];
    }

    public function mastersSave(Session $session, array $p): array
    {
        $type = Crm::s($p['type'] ?? '');
        $ar = Crm::s($p['value_ar'] ?? '');
        $en = Crm::s($p['value_en'] ?? '');
        if ($type === '' || ($ar === '' && $en === '')) {
            return ['ok' => false, 'error' => 'MISSING'];
        }

        // companyName rows can carry a logo image; meta holds its URL
        $meta = Crm::s($p['meta'] ?? '');
        if ($type === 'companyName' && ! empty($p['logoBase64'])) {
            $url = $this->files->saveLogo((string) $p['logoBase64'], Crm::s($p['logoName'] ?? ''));
            if ($url !== '') {
                $meta = $url;
            }
        }

        $fields = [
            'type' => $type,
            'value_ar' => $ar,
            'value_en' => $en,
            'active' => ($p['active'] ?? '') === 'no' ? 'no' : 'yes',
            'meta' => $meta,
        ];
        $label = $type.': '.($en !== '' ? $en : $ar);

        if (! empty($p['masterId'])) {
            $master = Master::find(Crm::s($p['masterId']));
            if (! $master) {
                return ['ok' => false, 'error' => 'NOTFOUND'];
            }
            $master->update($fields);
            $this->logger->log($session, 'MASTER_UPDATE', 'Masters', $master->masterId, $label);

            return ['ok' => true];
        }

        $id = Ids::make('M');
        Master::create($fields + ['masterId' => $id, 'active' => 'yes']);
        $this->logger->log($session, 'MASTER_CREATE', 'Masters', $id, $label);

        return ['ok' => true, 'masterId' => $id];
    }

    // ── LOGS ─────────────────────────────────────────────────

    public function logsList(array $p): array
    {
        return ['ok' => true, 'rows' => $this->logger->list(Crm::i($p['limit'] ?? 100) ?: 100)];
    }

    /** Company identity (name + logo) for document headers. */
    public function company(): array
    {
        $row = Master::query()->where('type', 'companyName')->where('active', 'yes')->first();

        return [
            'en' => $row?->value_en ?: 'UMRAH OPERATOR',
            'ar' => $row?->value_ar ?: 'شركة العمرة',
            'logo' => (string) ($row?->meta ?? ''),
        ];
    }

    /** Vehicle capacity, read from the Masters `meta` column. */
    public function vehicleCapacity(string $vehicleType): int
    {
        if ($vehicleType === '') {
            return 0;
        }
        $row = Master::query()
            ->where('type', 'vehicleType')->where('active', 'yes')
            ->where(fn ($q) => $q->where('value_en', $vehicleType)->orWhere('value_ar', $vehicleType))
            ->first();

        return $row ? Crm::i($row->meta) : 0;
    }
}
