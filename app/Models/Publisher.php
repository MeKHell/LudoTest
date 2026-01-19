<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperPublisher
 */
class Publisher extends Model
{

    public function games_published(): BelongsTo {
        return $this->belongsTo(Game::class, 'bgge_game_id');
    }
}
