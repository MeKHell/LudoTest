<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Game;
use Illuminate\Http\Request;
use function App\rm_blank_lines;

class CommentController extends Controller
{
    public function storeComment(Request $request) {

        $id = $request->input('game_id');
        $comment = $request->input('comment');
        $uid = $request->user()->id;


        $lang = $request->user()->lang;
        $clean_comment = trim(rm_blank_lines(strip_tags($comment)));

        if (!Game::find($id)) {
            return back()->withErrors(['ok' => false, 'message' => 'game.not_found']);
        }
        if (!$lang) {
            return back()->withErrors(['ok' => false, 'message' => 'game.user_language_not_found']);
        }
        if(strlen($clean_comment) > config('app.max_comment_length')) {
            return back()->withErrors(['ok' => false, 'message' => 'game.too_long_message' , 'length' => config('app.max_comment_length')]);
        }
        if (Comment::where(['game_id' => $id, 'writer' => $uid])->count() > 0) {
            return back()->withErrors(['ok' => false, 'message' => 'game.comment_already_exists']);
        }

        $tk = TranslationKeyController::ADD($clean_comment, $lang, "game_comment");
        Comment::create(['translation_id' => $tk->id, "game_id" => $id, 'writer' => $uid, 'lang' => $lang]);
        return back();
    }

    public function deleteComment(Request $request, Comment $comment)
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->json(['ok' => true]);
    }

    public function updateComment(Request $request, Comment $comment)
    {
        $this->authorize('update', $comment);

        $validated = $request->validate([
            'comment' => ['required', 'string', 'max:'.config('app.max_comment_length')],
        ]);

        $lang = $request->user()->lang ?? $comment->lang;
        $cleanComment = trim(rm_blank_lines(strip_tags($validated['comment'])));

        $tk = TranslationKeyController::ADD($cleanComment, $lang, 'game_comment');
        $comment->update([
            'translation_id' => $tk->id,
            'editor' => $request->user()->id,
        ]);

        return response()->json(CommentResource::make($comment->fresh())->resolve());
    }

    public function getComments(Request $request, string $gameId) {
        $game = Game::find($gameId);
        if (!$game || ($comments = $game->comments)->count() < 1){
            return response()->json([]);
        }

        return response()->json(CommentResource::collection($comments)->resolve());
    }
}
