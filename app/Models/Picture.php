<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Model;

class Picture extends Model
{
    /** @use HasFactory<\Database\Factories\PictureFactory> */
    use HasFactory;

    protected $primaryKey = 'url';

    protected $keyType = 'string';

    protected $fillable = ['url', 'blurhash'];

    /**
     * @return BelongsToMany<Game,Picture,Pivot>
     */
    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class);
    }
}
