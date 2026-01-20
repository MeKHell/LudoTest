<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @mixin IdeHelperComment
 */
class Comment extends Model
{
    /** @use HasFactory<\Database\Factories\CommentFactory> */
    use HasFactory;

    protected $guarded = ['created_at', 'updated_at'];

    protected $with = ['translations','writtenIn', 'writer'];

    /**
     * @return BelongsTo<User,Comment>
     */
    public function writer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'writer');
    }

    /**
     * @return BelongsTo<User,Comment>
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editor');
    }

    /**
     * @return BelongsTo<Game,Comment>
     */
    public function onGame(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id');
    }

    /**
     * @return HasOne<Language,Comment>
     */
    public function writtenIn(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'lang');
    }

    public function translations(): BelongsTo
    {
        return $this->belongsTo(TranslationKey::class, 'translation_id');
    }
}
