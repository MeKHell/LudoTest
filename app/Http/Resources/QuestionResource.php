<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
                'id' => $this->id,
                'translations' => new TranslationKeyResource($this->translations),
                'max_val' => $this->min_val,
                'min_val' => $this->max_val,
                'original_lang' => $this->writtenIn
        ];
    }
}
