<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    /** @use HasFactory<\Database\Factories\LanguageFactory> */
    use HasFactory;

    protected $primaryKey = 'code';
    protected $keyType = 'string';
    protected $guarded = [];

    /**
     * @return hasMany<Language,Comment>
     */
    public function comments(): hasMany
    {
        return $this->hasMany(Comment::class, 'lang');
    }

    public function speaked_by(): HasMany {
        return $this->hasMany(User::class, 'lang');
    }

    public function descriptions(): HasMany {
        return $this->hasMany(Description::class, 'lang');
    }

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'game_language', 'lang_id', 'game_id');
    }
}
