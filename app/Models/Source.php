<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Source extends Model
{
    protected $fillable = ['name', 'slug', 'base_url'];

    public function gameSources(): HasMany
    {
        return $this->hasMany(GameSource::class);
    }
}
