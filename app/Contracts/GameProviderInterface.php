<?php

namespace App\Contracts;

use App\DTOs\ExternalGameData;
use Illuminate\Support\Collection;

interface GameProviderInterface
{
    /**
     * Fetch a single game by its external ID.
     */
    public function fetchById(string $externalId): ExternalGameData;

    /**
     * Search for games matching a query string.
     * 
     * @return Collection<int, ExternalGameData>
     */
    public function search(string $query): Collection;
}
