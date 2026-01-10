<?php

namespace App\Http\Resources;

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
                'version_of'];

        foreach ($to_unset as $key) {
            unset($game[$key]);
        }

        $descriptions = [];
        foreach ($game['descriptions'] as $description) {
            $descriptions[$description['lang']] = $description['description'];
        }
        $game['descriptions'] = $descriptions;

        $artists = [];
        $publishers = [];
        $designers = [];
        foreach ($game['worked_on'] as $role) {
            switch ($role['role']) {
                case 'Artist':
                    $artists[] = $role['name'];
                    break;
                case 'Publisher':
                    $publishers[] = $role['name'];
                    break;
                case 'Designer':
                    $designers[] = $role['name'];
                    break;
            }
        }
        unset($game['worked_on']);
        $game['artists'] = $artists;
        $game['publishers'] = $publishers;
        $game['designers'] = $designers;

        return $game;
    }
}
