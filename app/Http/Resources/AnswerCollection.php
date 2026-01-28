<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AnswerCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        $mapping = [];
        foreach ($this->collection as $answer) {
            $mapping['Q' . $answer->question_id] = $answer->value;
        }

        return $mapping;
    }
}
