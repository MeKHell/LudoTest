<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperTranslation
 */
class Translation extends Model
{
    protected $guarded = ["id"];
}
