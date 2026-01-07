<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Description extends Model
{
    /** @use HasFactory<\Database\Factories\DescriptionFactory> */
    use HasFactory;
    protected $guarded = ['id'];

    protected $hidden = ['id', 'created_at', 'updated_at', 'game_id', 'en_hash'];

    public $timestamps = false;

    public function writtenIn(): HasOne
    {
        return $this->hasOne(Language::class, 'lang', 'code');
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id');
    }
}
