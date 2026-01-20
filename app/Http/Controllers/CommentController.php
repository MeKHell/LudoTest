<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Game;
use App\Models\Language;
use App\Models\User;
use Illuminate\Http\Request;
use function App\rm_blank_lines;

class CommentController extends Controller
{
    public function storeComment(Request $request) {

        $id = $request->input('game_id');
        $comment = $request->input('comment');
        $uid = $request->user()->id;
        \Log::debug($uid .' '. $comment .' '. $id);


        $lang = $request->user()->lang;
        $clean_comment = trim(rm_blank_lines(strip_tags($comment)));

        if (!Game::find($id)) {
            return response()->json(['ok' => false, 'message' => 'Game not found'], 404);
        }
        if (!$lang) {
            return response()->json(['ok' => false, 'message' => 'User or language not found'], 404);
        }
        if(strlen($clean_comment) > config('app.max_comment_length')) {
            return response()->json(['ok' => false, 'message' => 'Comment cannot be longer than ' . config('app.max_comment_length')]);
        }
        if (Comment::where(['game_id' => $id, 'writer' => $uid])->count() > 0) {
            return response()->json(['ok' => false, 'message' => 'Comment already exists'], 404);
        }

        $tk = TranslationKeyController::ADD($clean_comment, $lang, "game_comment");
        Comment::create(['translation_id' => $tk->id, "game_id" => $id, 'writer' => $uid, 'lang' => $lang]);
        return response()->json(['ok' => true, 'message'=>'']);
    }

    public function deleteComment(Request $request, string $commentId) {}

    public function updateComment(Request $request, string $commentId) {}

    public function getComments(Request $request, string $gameId) {}
}
