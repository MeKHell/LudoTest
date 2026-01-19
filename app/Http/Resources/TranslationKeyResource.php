<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TranslationKeyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $translations = [];
        foreach ($this->fullTranslations as $translation) {
            $translations[$translation->language_code] = $translation->text;
        }
        return $translations;
    }
}
