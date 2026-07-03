<?php

namespace App\Providers\GameSources;

use App\Contracts\GameProviderInterface;
use App\DTOs\ExternalGameData;
use Illuminate\Support\Collection;

class StubProvider implements GameProviderInterface
{
    public function fetchById(string $externalId): ExternalGameData
    {
        return new ExternalGameData(
            externalId: $externalId,
            sourceSlug: 'stub',
            name: "Stub Game {$externalId}",
            description: 'A placeholder game from the stub provider.',
            pubYear: 2020,
            minAge: 10,
            minPlayers: 2,
            maxPlayers: 4,
            boxTime: 60,
            minTime: 45,
            maxTime: 90,
        );
    }

    public function search(string $query): Collection
    {
        if (strlen($query) < 2) {
            return collect();
        }

        return collect([
            $this->fetchById('1'),
            new ExternalGameData(
                externalId: '2',
                sourceSlug: 'stub',
                name: "Stub {$query} Deluxe",
                pubYear: 2021,
                minAge: 8,
                minPlayers: 1,
                maxPlayers: 6,
                boxTime: 30,
                minTime: 20,
                maxTime: 40,
            ),
        ]);
    }
}
