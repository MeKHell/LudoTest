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

    protected $fillable = ['name', 'bgg_id', 'bgg_version_id', 'lang', 'description'];

    /**
     * @return BelongsToMany<Picture,Game,Pivot>
     */
    public function pictures(): BelongsToMany
    {
        return $this->belongsToMany(Picture::class);
    }

    /**
     * @return HasOne<Language,Game>
     */
    public function language(): HasOne
    {
        return $this->hasOne(Language::class, $localKey = 'lang');
    }

    /**
     * @return HasMany<Comment,Game>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
