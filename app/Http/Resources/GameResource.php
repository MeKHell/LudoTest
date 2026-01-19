<?php

namespace App\Http\Resources;

use App\Models\Publisher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $game = parent::toArray($request);

        $to_unset = ['updated_at',
                'created_at',
                'thumb_hash',
                'thumb_blurhash',
                'image_hash',
                'image_blurhash',
                'description_hash',
                'version_of',
                'translation_key',
                'translation_id',
                'last_sync_at'];

        foreach ($to_unset as $key) {
            unset($game[$key]);
        }

        $game['descriptions'] = new TranslationKeyResource($this->translationKey);

        $worked_on = [];
        if (key_exists('worked_on', $game)) {
            foreach ($game['worked_on'] as $role) {
                if (!key_exists($role['role'], $worked_on)) {
                    $worked_on[$role['role']] = [];
                }
                $worked_on[$role['role']][] = $role['name'];
            }
        }
        unset($game['worked_on']);
        $game['artists'] =  key_exists('Artist', $worked_on) ? $worked_on['Artist'] : [];
        $game['publishers'] = key_exists('Publisher', $worked_on) ? $worked_on['Publisher'] : [];
        $game['designers'] = key_exists('Designer', $worked_on) ? $worked_on['Designer'] : [];

        return $game;
    }
}
