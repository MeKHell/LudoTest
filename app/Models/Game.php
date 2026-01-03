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

    protected $fillable = ['name', 'bgg_id', 'bgg_version_id', 'lang', 'description'];

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

    public function publishers(): HasMany {
        return $this->hasMany(Publisher::class, 'bgge_game_id');
    }

    public function artists(): HasMany {
        return $this->hasMany(Artist::class, 'bgge_game_id');
    }
}
