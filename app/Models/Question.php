<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperQuestion
 */
class Question extends Model
{
    protected $guarded = ['id'];

    protected $with = ['writtenIn', 'translations'];

    public function writtenIn(): BelongsTo{
        return $this->belongsTo(Language::class, 'lang', 'code');
    }

    public function translations(): BelongsTo
    {
        return $this->belongsTo(TranslationKey::class, 'translation_id');
    }

    public function answers(): HasMany {
        return $this->hasMany(Answer::class, 'question_id');
    }
}
