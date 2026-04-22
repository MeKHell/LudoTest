<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameSource extends Model
{
    protected $fillable = [
        'game_id',
        'source_id',
        'external_id',
        'raw_data',
        'last_sync_at',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'last_sync_at' => 'datetime',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }
}
