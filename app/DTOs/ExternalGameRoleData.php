<?php

namespace App\DTOs;

readonly class ExternalGameRoleData
{
    public function __construct(
        public string $name,
        public string $role, // Artist, Publisher, Designer
        public string $sourceSlug,
        public ?string $externalId = null,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'role' => $this->role,
            'source_slug' => $this->sourceSlug,
            'external_id' => $this->externalId,
        ];
    }
}
