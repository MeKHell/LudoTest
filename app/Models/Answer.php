<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperAnswer
 */
class Answer extends Model
{

    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $hidden = ['id'];

    protected $with = ['user', 'question', 'writtenIn'];

    public function user(): BelongsTo{
        return $this->belongsTo(User::class)->select('name');
    }

    public function game(): BelongsTo{
        return $this->belongsTo(Game::class);
    }

    public function question(): BelongsTo{
        return $this->belongsTo(Question::class);
    }


}
