<?php

namespace App\Http\Resources;

use App\Models\LibraryEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchResultResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isLocal = isset($this->resource['game']) && $this->resource['game'] !== null;
        $game = $this->resource['game'];
        $external = $this->resource['external'];

        $libraryTypes = [];
        $inUserLibrary = false;

        if ($request->user() && $isLocal) {
            $libraryTypes = LibraryEntry::query()
                ->where('user_id', $request->user()->id)
                ->where('game_id', $game->id)
                ->pluck('list_type')
                ->all();
            $inUserLibrary = $libraryTypes !== [];
        }

        return [
            'id' => $isLocal ? $game->id : $external->externalId,
            'name' => $isLocal ? $game->name : $external->name,
            'thumb_url' => $isLocal ? $game->thumb_url : ($external->thumbUrl ?? null),
            'pub_year' => $isLocal ? $game->pub_year : ($external->pubYear ?? null),
            'is_local' => $isLocal,
            'in_user_library' => $inUserLibrary,
            'list_types' => $libraryTypes,
            'source' => $isLocal ? 'local' : $external->sourceSlug,
            'external_id' => $isLocal ? ($game->gameSources->first()?->external_id ?? null) : $external->externalId,
            'score' => $this->resource['score'] ?? 0,
            'min_players' => $isLocal ? $game->min_players : ($external->minPlayers ?? null),
            'max_players' => $isLocal ? $game->max_players : ($external->maxPlayers ?? null),
            'box_time' => $isLocal ? $game->box_time : ($external->boxTime ?? null),
            'languages' => $isLocal ? $game->languages->pluck('code')->toArray() : [],
        ];
    }
}
