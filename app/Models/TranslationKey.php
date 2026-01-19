<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperTranslationKey
 */
class TranslationKey extends Model
{

    protected $guarded = ["id"];
    protected $with = ['fullTranslations'];

    public function fullTranslations(): HasMany {
        return $this->hasMany(Translation::class, 'translation_id');
    }

    public function translations(): HasMany{
        return $this->fullTranslations()->select(['lang', 'text']);
    }
}
