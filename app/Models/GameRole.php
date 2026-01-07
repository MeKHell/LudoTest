<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameRole extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $hidden = ['id', 'created_at', 'updated_at', 'bgge_id'];
    public function games_drawn(): BelongsTo {
        return $this->belongsTo(Game::class, 'bgge_id', 'bgge_id');
    }
}
