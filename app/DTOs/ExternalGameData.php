<?php

namespace App\DTOs;

readonly class ExternalGameData
{
    /**
     * @param ExternalGameRoleData[] $roles
     * @param ExternalGameData[] $versions
     */
    public function __construct(
        public string $externalId,
        public string $sourceSlug,
        public string $name,
        public ?string $description = null,
        public ?string $thumbUrl = null,
        public ?string $imageUrl = null,
        public ?int $pubYear = null,
        public ?int $minAge = null,
        public ?int $minPlayers = null,
        public ?int $maxPlayers = null,
        public ?int $boxTime = null,
        public ?int $minTime = null,
        public ?int $maxTime = null,
        public array $roles = [],
        public array $languages = [], 
        public array $versions = [],
        public array $rawData = [],
    ) {}
}
