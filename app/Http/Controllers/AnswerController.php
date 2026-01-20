<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Game;
use App\Models\Question;
use Illuminate\Http\Request;

class AnswerController extends Controller
{

    public function get(string $game_id){
            $answers = Game::find($game_id)?->answers()->groupBy('question_id')->avg('value');
            $questions = Question::where('is_enabled', true)->get()->toResourceCollection();
        return response()->json(['answers' => $answers ?? (object) [], 'questions' => $questions]);
    }

}
