<?php

namespace App\Services;

use App\Contracts\GameProviderInterface;
use App\DTOs\ExternalGameData;
use App\Models\Game;
use App\Models\GameSource;
use App\Models\Language;
use App\Models\Source;
use App\Models\TranslationKey;
use App\Models\LanguageMapping;
use App\Http\Controllers\TranslationKeyController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GameService
{
    public function __construct()
    {
    }

    private function resolveProvider(string $slug): GameProviderInterface
    {
        return match ($slug) {
            'bgg', 'bggv' => new \App\Providers\GameSources\BggProvider($slug),
            'stub' => new \App\Providers\GameSources\StubProvider(),
            default => throw new \InvalidArgumentException("Unsupported source: {$slug}"),
        };
    }

    public function search(string $query, ?string $sourceSlug = null, array $filters = [], string $filterMode = 'strict'): Collection
    {
        $sourceSlug = $sourceSlug ?: config('app.default_src', 'bgg');
        $source = Source::where('slug', $sourceSlug)->firstOrFail();

        // 1. Local Search (Title & Description)
        $localGames = Game::query()
            ->select('games.*')
            ->selectSub(function ($q) use ($query) {
                $q->selectRaw('count(*)')
                    ->from('translations')
                    ->whereColumn('translations.translation_id', 'games.translation_id')
                    ->where('translations.text', 'LIKE', "%{$query}%");
            }, 'description_match_count')
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhereHas('translationKey.translations', function ($tq) use ($query) {
                        $tq->where('text', 'LIKE', "%{$query}%");
                    });
            })
            ->with(['gameSources'])
            ->when($filters !== [], fn ($q) => $this->applySearchFiltersToQuery($q, $filters, $filterMode))
            ->get();

        // 2. External Search
        try {
            $externalResults = $this->resolveProvider($sourceSlug)->search($query);
        } catch (\Exception $e) {
            // Log error or handle failure
            $externalResults = collect();
        }

        // 3. De-duplicate & Merge
        $results = collect();
        $externalIds = $externalResults->pluck('externalId')->toArray();
        $localExternalIds = GameSource::where('source_id', $source->id)
            ->whereIn('external_id', $externalIds)
            ->pluck('external_id');
        $gameSources = GameSource::where('source_id', $source->id)
            ->whereIn('external_id', $externalIds)
            ->when($filters !== [], fn ($q) => $q->whereHas(
                'game',
                fn ($gameQuery) => $this->applySearchFiltersToQuery($gameQuery, $filters, $filterMode)
            ))
            ->with(['game' => function($q) use ($query) {
                $q->select('games.*')
                  ->selectSub(function ($sq) use ($query) {
                      $sq->selectRaw('count(*)')
                          ->from('translations')
                          ->whereColumn('translations.translation_id', 'games.translation_id')
                          ->where('translations.text', 'LIKE', "%{$query}%");
                  }, 'description_match_count');
            }])
            ->get()
            ->keyBy('external_id');

        // Merge external results
        foreach ($externalResults as $extData) {
            $localGameSource = $gameSources->get($extData->externalId);
            $localGame = $localGameSource ? $localGameSource->game : null;

            if (! $localGame && $localExternalIds->contains($extData->externalId)) {
                continue;
            }

            if (! $localGame && ! $this->passesSearchFilters(
                $extData->minPlayers,
                $extData->maxPlayers,
                $extData->minTime,
                $extData->maxTime ?? $extData->boxTime,
                $extData->minAge,
                $filters,
                $filterMode
            )) {
                continue;
            }
            
            if ($localGame) {
                // If we found it externally and it's already in DB, remove from localGames to avoid duplicates
                $localGames = $localGames->reject(fn($g) => $g->id === $localGame->id);
            }

            $results->push([
                'game' => $localGame,
                'external' => $extData,
                'score' => $this->calculateRelevance($extData->name, $query, $localGame)
            ]);
        }

        // Add remaining local games that weren't in external search results
        foreach ($localGames as $localGame) {
             $results->push([
                'game' => $localGame,
                'external' => null,
                'score' => $this->calculateRelevance($localGame->name, $query, $localGame)
            ]);
        }

        $results = $results->sortByDesc(fn($item) => [
            $item['score'],
            $item['game'] ? $item['game']->pub_year : ($item['external'] ? $item['external']->pubYear : 0)
        ])->values();

        return $this->applySearchFilters($results, $filters, $filterMode);
    }

    private function applySearchFiltersToQuery($query, array $filters, string $filterMode): void
    {
        $minPlayers = $filters['min_players'] ?? null;
        $maxPlayers = $filters['max_players'] ?? null;
        $minTime = $filters['min_time'] ?? null;
        $maxTime = $filters['max_time'] ?? null;

        if ($filterMode === 'loose') {
            if ($minPlayers !== null) {
                $query->where(fn ($q) => $q
                    ->whereNull('max_players')
                    ->orWhere('max_players', '>=', $minPlayers));
            }

            if ($maxPlayers !== null) {
                $query->where(fn ($q) => $q
                    ->whereNull('min_players')
                    ->orWhere('min_players', '<=', $maxPlayers));
            }

            if ($minTime !== null) {
                $query->where(function ($q) use ($minTime) {
                    $q->whereNull('max_time')
                        ->where(fn ($timeQuery) => $timeQuery
                            ->whereNull('box_time')
                            ->orWhere('box_time', '>=', $minTime))
                        ->orWhere('max_time', '>=', $minTime);
                });
            }

            if ($maxTime !== null) {
                $query->where(fn ($q) => $q
                    ->whereNull('min_time')
                    ->orWhere('min_time', '<=', $maxTime));
            }
        } else {
            if ($minPlayers !== null) {
                $query->where(fn ($q) => $q
                    ->whereNull('min_players')
                    ->orWhere('min_players', '>=', $minPlayers));
            }

            if ($maxPlayers !== null) {
                $query->where(fn ($q) => $q
                    ->whereNull('max_players')
                    ->orWhere('max_players', '<=', $maxPlayers));
            }

            if ($minTime !== null) {
                $query->where(fn ($q) => $q
                    ->whereNull('min_time')
                    ->orWhere('min_time', '>=', $minTime));
            }

            if ($maxTime !== null) {
                $query->where(function ($q) use ($maxTime) {
                    $q->whereNull('max_time')
                        ->where(fn ($timeQuery) => $timeQuery
                            ->whereNull('box_time')
                            ->orWhere('box_time', '<=', $maxTime))
                        ->orWhere('max_time', '<=', $maxTime);
                });
            }

        }

        if (isset($filters['min_age'])) {
            $query->where(fn ($q) => $q
                ->whereNull('min_age')
                ->orWhere('min_age', '<=', $filters['min_age']));
        }
    }

    private function applySearchFilters(Collection $results, array $filters, string $filterMode): Collection
    {
        if ($filters === []) {
            return $results;
        }

        return $results->filter(function (array $item) use ($filters, $filterMode) {
            $game = $item['game'];
            $external = $item['external'];

            return $this->passesSearchFilters(
                $game?->min_players ?? $external?->minPlayers,
                $game?->max_players ?? $external?->maxPlayers,
                $game?->min_time ?? $external?->minTime,
                $game?->max_time ?? $external?->maxTime ?? $game?->box_time ?? $external?->boxTime,
                $game?->min_age ?? $external?->minAge,
                $filters,
                $filterMode
            );
        })->values();
    }

    private function passesSearchFilters(
        ?int $minPlayers,
        ?int $maxPlayers,
        ?int $minTime,
        ?int $maxTime,
        ?int $minAge,
        array $filters,
        string $filterMode
    ): bool {
        if ($filterMode === 'loose') {
            if (isset($filters['min_players']) && $maxPlayers !== null && $maxPlayers < $filters['min_players']) {
                return false;
            }

            if (isset($filters['max_players']) && $minPlayers !== null && $minPlayers > $filters['max_players']) {
                return false;
            }

            if (isset($filters['min_time']) && $maxTime !== null && $maxTime < $filters['min_time']) {
                return false;
            }

            if (isset($filters['max_time']) && $minTime !== null && $minTime > $filters['max_time']) {
                return false;
            }
        } else {
            if (isset($filters['min_players']) && $minPlayers !== null && $minPlayers < $filters['min_players']) {
                return false;
            }

            if (isset($filters['max_players']) && $maxPlayers !== null && $maxPlayers > $filters['max_players']) {
                return false;
            }

            if (isset($filters['min_time']) && $minTime !== null && $minTime < $filters['min_time']) {
                return false;
            }

            if (isset($filters['max_time']) && $maxTime !== null && $maxTime > $filters['max_time']) {
                return false;
            }
        }

        if (isset($filters['min_age']) && $minAge !== null && $minAge > $filters['min_age']) {
            return false;
        }

        return true;
    }

    private function calculateRelevance(string $name, string $query, ?Game $localGame): int
    {
        $name = strtolower($name);
        $query = strtolower($query);
        $score = 0;

        if ($name === $query) {
            $score += 100;
        } elseif (str_starts_with($name, $query)) {
            $score += 80;
        } elseif (preg_match("/\b" . preg_quote($query, '/') . "\b/", $name)) {
            $score += 70;
        } elseif (str_contains($name, $query)) {
            $score += 60;
        }

        // Description check
        if ($localGame && ($localGame->description_match_count ?? 0) > 0) {
            $score += 40;
        }

        return $score;
    }

    public function ensureGameInDb(string $externalId, string $sourceSlug, ?string $targetName = null): Game
    {
        $source = Source::where('slug', $sourceSlug)->firstOrFail();
        
        $gameSource = GameSource::where('source_id', $source->id)
            ->where('external_id', $externalId)
            ->first();

        if ($gameSource && $gameSource->last_sync_at->isAfter(now()->subDay())) {
            $game = $gameSource->game;
            
            if ($targetName && $game->name !== $targetName) {
                $version = $game->versions()->where('name', $targetName)->first();
                if ($version) return $version;
            }
            
            return $game;
        }

        $externalGame = $this->resolveProvider($sourceSlug)->fetchById($externalId);
        
        return DB::transaction(function () use ($externalGame, $source, $targetName) {
            $parentGame = $this->persistExternalGame($externalGame, $source);
            
            if ($targetName && $externalGame->name !== $targetName) {
                // Refresh parent to get the saved versions
                $parentGame->load('versions');
                foreach ($parentGame->versions as $version) {
                    if ($version->name === $targetName) {
                        return $version;
                    }
                }
            }
            
            return $parentGame;
        });
    }

    private function persistExternalGame(ExternalGameData $data, Source $source, ?Game $parent = null): Game
    {
        $game = Game::updateOrCreate(
            ['id' => $this->findGameIdBySource($source->id, $data->externalId)],
            [
                'name' => $data->name,
                'thumb_url' => $data->thumbUrl,
                'image_url' => $data->imageUrl,
                'pub_year' => $data->pubYear,
                'min_age' => $data->minAge,
                'min_players' => $data->minPlayers,
                'max_players' => $data->maxPlayers,
                'box_time' => $data->boxTime,
                'min_time' => $data->minTime,
                'max_time' => $data->maxTime,
                'version_of' => $parent?->id,
            ]
        );

        GameSource::updateOrCreate(
            ['source_id' => $source->id, 'external_id' => $data->externalId],
            [
                'game_id' => $game->id,
                'raw_data' => $data->rawData,
                'last_sync_at' => now(),
            ]
        );

        if ($data->description) {
            $tk = TranslationKeyController::ADD($data->description, "en", "game_description");
            $game->translationKey()->associate($tk);
            $game->save();
        }

        if ($data->roles) {
            foreach ($data->roles as $roleData) {
                $game->worked_on()->updateOrCreate(
                    [
                        'source_id' => $source->id,
                        'external_id' => $roleData->externalId,
                        'role' => $roleData->role,
                    ],
                    ['name' => $roleData->name]
                );
            }
        }

        if ($data->languages) {
             $game->languages()->sync($this->mapLanguages($data->languages, $source->id));
        }

        if ($data->versions) {
            foreach ($data->versions as $versionData) {
                $this->persistExternalGame($versionData, $source, $game);
            }
        }

        return $game;
    }

    private function mapLanguages(array $externalLanguageIds, int $sourceId): array
    {
        return LanguageMapping::where('source_id', $sourceId)
            ->whereIn('external_id', $externalLanguageIds)
            ->pluck('language_code')
            ->toArray();
    }

    private function findGameIdBySource(int $sourceId, string $externalId): ?int
    {
        return GameSource::where('source_id', $sourceId)
            ->where('external_id', $externalId)
            ->value('game_id');
    }
}
