<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    /** @use HasFactory<\Database\Factories\LanguageFactory> */
    use HasFactory;

    protected $fillable = ['code', 'bgg_index', 'bgg_name'];

    protected $primaryKey = 'code';

    protected $keyType = 'string';

    /**
     * @return BelongsToMany<Game,Language,Pivot>
     */
    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class);
    }

    /**
     * @return BelongsToMany<Model,Language,Pivot>
     */
    public function comments(): BelongsToMany
    {
        return $this->belongsToMany(Comment);
    }
}
