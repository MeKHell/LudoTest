<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    /** @use HasFactory<\Database\Factories\CommentFactory> */
    use HasFactory;

    protected $fillable = ['content', 'writer', 'editor', 'game_id', 'lang'];

    /**
     * @return BelongsTo<User,Comment>
     */
    public function writer(): BelongsTo
    {
        return $this->belongsTo(User::class, $ownerKey = 'writer');
    }

    /**
     * @return BelongsTo<User,Comment>
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, $ownerKey = 'editor');
    }

    /**
     * @return BelongsTo<Game,Comment>
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * @return BelongsTo<Game,Comment>
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
