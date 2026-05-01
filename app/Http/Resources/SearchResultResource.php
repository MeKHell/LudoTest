<?php

namespace App\Http\Resources;

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

        return [
            'id' => $isLocal ? $game->id : $external->externalId,
            'name' => $isLocal ? $game->name : $external->name,
            'thumb_url' => $isLocal ? $game->thumb_url : ($external->thumbUrl ?? null),
            'pub_year' => $isLocal ? $game->pub_year : ($external->pubYear ?? null),
            'is_local' => $isLocal,
            'source' => $isLocal ? 'local' : $external->sourceSlug,
            'external_id' => $isLocal ? ($game->gameSources->first()?->external_id ?? null) : $external->externalId,
            'score' => $this->resource['score'] ?? 0,
            'min_players' => $isLocal ? $game->min_players : null,
            'max_players' => $isLocal ? $game->max_players : null,
            'box_time' => $isLocal ? $game->box_time : null,
            'languages' => $isLocal ? $game->languages->pluck('code')->toArray() : [],
        ];
    }
}
