<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    /** @use HasFactory<\Database\Factories\GameFactory> */
    use HasFactory;
    protected $primaryKey = 'bgge_id';
    protected $keyType = 'string';

    protected $guarded = ['updated_at', 'created_at'];

    /**
     * @return HasOne<Language,Game>
     */
    public function language(): BelongsToMany
    {
        return $this->belongsToMany(Language::class, 'game_language', 'game_id', 'lang_id');
    }

    /**
     * @return HasMany<Comment,Game>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }


    public function worked_on(): HasMany {
        return $this->hasMany(GameRole::class, 'bgge_game_id');
    }
}
