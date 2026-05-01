<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperGame
 */
class Game extends Model
{
    /** @use HasFactory<\Database\Factories\GameFactory> */
    //use HasFactory;

    protected $guarded = ['id', 'updated_at', 'created_at'];

    protected $with = ['languages'];

    protected $casts = [
            'last_sync_at' => 'datetime:Y-m-d',
    ];


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
                'id',
                'code');
    }

    /**
     * @return HasMany<Comment,Game>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'game_id', 'id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class, 'game_id', 'id');
    }


    public function worked_on(): HasMany {
        return $this->hasMany(GameRole::class, 'game_id', 'id');
    }

    public function translationKey(): BelongsTo
    {
        return $this->belongsTo(TranslationKey::class, "translation_id");
    }
    public function versions(): HasMany
    {
        return $this->hasMany(Game::class, 'version_of', 'id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'version_of', 'id');
    }

    public function gameSources(): HasMany
    {
        return $this->hasMany(GameSource::class);
    }
}
