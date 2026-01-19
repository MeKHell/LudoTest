<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Lang;

/**
 * @mixin IdeHelperQuestion
 */
class Question extends Model
{


    protected $guarded = ['id'];
    protected $hidden = ['id'];

    protected $with = ['writtenIn'];

    public function writtenIn(): BelongsTo{
        return $this->belongsTo(Lang::class, 'lang');
    }

    public function answers(): HasMany {
        return $this->hasMany(Answer::class, 'question_id');
    }
}
