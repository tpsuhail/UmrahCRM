<?php

namespace App\Services;

use App\Models\User;
use App\Support\Dates;
use App\Support\Ids;
use App\Support\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Token sessions. A token is an opaque cache key with a sliding expiry —
 * the same contract the Apps Script version offered, minus its 6h cache cap.
 */
class AuthService
{
    public function __construct(private ActivityLogger $logger) {}

    private function ttl(): int
    {
        return (int) config('crm.session_ttl');
    }

    public function login(string $username, string $password): array
    {
        if ($username === '' || $password === '') {
            return ['ok' => false, 'error' => 'MISSING'];
        }

        $user = User::query()->whereRaw('LOWER(username) = ?', [Str::lower(trim($username))])->first();
        if (! $user) {
            return ['ok' => false, 'error' => 'INVALID'];
        }
        if ($user->status !== 'active') {
            return ['ok' => false, 'error' => 'DISABLED'];
        }
        if (! Hash::check($password, $user->passwordHash)) {
            $this->logger->log(null, 'LOGIN_FAILED', 'Users', $user->userId, $user->username);

            return ['ok' => false, 'error' => 'INVALID'];
        }

        $session = new Session(
            $user->userId,
            $user->username,
            $user->role,
            $user->agentCode ?: '',
            $user->displayName ?: $user->username,
            $user->department ?: '',
        );

        $token = (string) Str::uuid();
        Cache::put($this->key($token), $session->toArray(), $this->ttl());

        $user->update(['lastLogin' => Dates::nowStr()]);
        $this->logger->log($session, 'LOGIN', 'Users', $user->userId);

        return ['ok' => true, 'token' => $token, 'user' => $session->toArray()];
    }

    /** Validate a token and renew its expiry; null when it is unknown or stale. */
    public function resolve(?string $token): ?Session
    {
        if (! $token) {
            return null;
        }
        $data = Cache::get($this->key($token));
        if (! is_array($data)) {
            return null;
        }
        Cache::put($this->key($token), $data, $this->ttl());   // sliding renewal

        return Session::fromArray($data);
    }

    public function logout(Session $session, string $token): array
    {
        Cache::forget($this->key($token));
        $this->logger->log($session, 'LOGOUT', 'Users', $session->userId);

        return ['ok' => true];
    }

    public function changePassword(Session $session, array $p): array
    {
        $user = User::find($session->userId);
        if (! $user) {
            return ['ok' => false, 'error' => 'NOTFOUND'];
        }
        if (! Hash::check((string) ($p['oldPassword'] ?? ''), $user->passwordHash)) {
            return ['ok' => false, 'error' => 'WRONG_OLD'];
        }
        $new = (string) ($p['newPassword'] ?? '');
        if (strlen($new) < 6) {
            return ['ok' => false, 'error' => 'WEAK'];
        }

        $user->update(['passwordHash' => Hash::make($new)]);
        $this->logger->log($session, 'CHANGE_PASSWORD', 'Users', $user->userId);

        return ['ok' => true];
    }

    /** Operator resets another user's password and hands back a temporary one. */
    public function resetPassword(Session $session, array $p): array
    {
        $user = User::find((string) ($p['userId'] ?? ''));
        if (! $user) {
            return ['ok' => false, 'error' => 'NOTFOUND'];
        }
        $temp = 'Tmp@'.Str::lower(Str::random(6));
        $user->update(['passwordHash' => Hash::make($temp)]);
        $this->logger->log($session, 'RESET_PASSWORD', 'Users', $user->userId);

        return ['ok' => true, 'tempPassword' => $temp];
    }

    /** Create the very first operator account. */
    public static function makeUser(array $attributes, string $password): User
    {
        return User::create($attributes + [
            'userId' => Ids::make('U'),
            'passwordHash' => Hash::make($password),
            'createdAt' => Dates::nowStr(),
            'lastLogin' => '',
        ]);
    }

    private function key(string $token): string
    {
        return 'crm_sess_'.$token;
    }
}
