<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    /** @use HasFactory<\Database\Factories\GameFactory> */
    //use HasFactory;
    protected $primaryKey = 'bgge_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = ['updated_at', 'created_at'];

    protected $with = ['languages', 'worked_on', 'descriptions'];

    protected $hidden = ['updated_at', 'created_at', 'thumb_hash', 'thumb_blurhash', 'image_hash', 'image_blurhash'];

    /**
     * @return HasOne<Language,Game>
     */
    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class,
                'game_language',
                'game_id',
                'lang_id',
                'bgge_id',
                'code');
    }

    /**
     * @return HasMany<Comment,Game>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }


    public function worked_on(): HasMany {
        return $this->hasMany(GameRole::class, 'bgge_id', 'bgge_id');
    }

    public function descriptions(): HasMany
    {
        return $this->hasMany(Description::class, 'game_id', 'bgge_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(Game::class, 'version_of', 'bgge_id');
    }
}
