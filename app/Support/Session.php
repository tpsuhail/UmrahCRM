<?php

namespace App\Support;

/**
 * The authenticated caller, as resolved from the bearer token. Carries only
 * what authorisation decisions need — hashes and salts never leave the server.
 */
class Session
{
    public function __construct(
        public readonly string $userId,
        public readonly string $username,
        public readonly string $role,
        public readonly string $agentCode = '',
        public readonly string $displayName = '',
        public readonly string $department = '',
    ) {}

    public static function fromArray(array $a): self
    {
        return new self(
            (string) ($a['userId'] ?? ''),
            (string) ($a['username'] ?? ''),
            (string) ($a['role'] ?? ''),
            (string) ($a['agentCode'] ?? ''),
            (string) ($a['displayName'] ?? ''),
            (string) ($a['department'] ?? ''),
        );
    }

    public function toArray(): array
    {
        return [
            'userId' => $this->userId,
            'username' => $this->username,
            'role' => $this->role,
            'agentCode' => $this->agentCode,
            'displayName' => $this->displayName,
            'department' => $this->department,
        ];
    }

    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }
}
