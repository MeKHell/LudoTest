<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
            'created_at'=> $this->created_at,
            'updated_at'=>$this->updated_at,
            'original_lang' => $this->writtenIn,
            'comment' => new TranslationKeyResource($this->translations),
            'writer' => $this->writer()->first()->name,
            'editor' => $this->editor()->first()?->name,

        ];
    }
}
