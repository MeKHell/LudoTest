<?php

namespace App\Services;

use App\Contracts\GameProviderInterface;
use App\DTOs\ExternalGameData;
use App\Models\Game;
use App\Models\GameSource;
use App\Models\Language;
use App\Models\Source;
use App\Models\TranslationKey;
use App\Http\Controllers\TranslationKeyController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GameService
{
    private Collection $languages;

    public function __construct()
    {
        $this->languages = Language::all()->keyBy('code');
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
        $slug = $sourceSlug ?: config('app.default_src', 'bgg');
        return $this->resolveProvider($slug)->search($query);
    }

    public function ensureGameInDb(string $externalId, string $sourceSlug): Game
    {
        $source = Source::where('slug', $sourceSlug)->firstOrFail();
        
        $gameSource = GameSource::where('source_id', $source->id)
            ->where('external_id', $externalId)
            ->first();

        if ($gameSource && $gameSource->last_sync_at->isAfter(now()->subDay())) {
            return $gameSource->game;
        }

        $externalGame = $this->resolveProvider($sourceSlug)->fetchById($externalId);
        
        return DB::transaction(function () use ($externalGame, $source) {
            return $this->persistExternalGame($externalGame, $source);
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
             // This needs to be smarter based on how languages are mapped in the DB
             // For now, let's just use the ones we have matches for.
             // $game->languages()->sync($this->mapLanguages($data->languages));
        }

        if ($data->versions) {
            foreach ($data->versions as $versionData) {
                $this->persistExternalGame($versionData, $source, $game);
            }
        }

        return $game;
    }

    private function findGameIdBySource(int $sourceId, string $externalId): ?int
    {
        return GameSource::where('source_id', $sourceId)
            ->where('external_id', $externalId)
            ->value('game_id');
    }
}
