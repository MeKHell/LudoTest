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
            default => throw new \InvalidArgumentException("Unsupported source: {$slug}"),
        };
    }

    public function search(string $query, ?string $sourceSlug = null): Collection
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
        $gameSources = GameSource::where('source_id', $source->id)
            ->whereIn('external_id', $externalIds)
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

        return $results->sortByDesc(fn($item) => [
            $item['score'],
            $item['game'] ? $item['game']->pub_year : ($item['external'] ? $item['external']->pubYear : 0)
        ])->values();
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
