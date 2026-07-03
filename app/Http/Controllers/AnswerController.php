<?php

namespace App\Http\Controllers;

use App\Http\Resources\AnswerCollection;
use App\Http\Resources\QuestionResource;
use App\Models\Answer;
use App\Models\Game;
use App\Models\Question;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnswerController extends Controller
{

    public function get(string $game_id)
    {
        $answers = Game::find($game_id)?->answers()->select('question_id', DB::raw('avg(value) as value_avg'))->groupBy('question_id')->get();
        $questions = QuestionResource::collection(Question::where('is_enabled', true)->get())->resolve();
        $mapped_answers = [];
        foreach ($answers as $answer) {
            $mapped_answers['Q'.$answer->question_id] = $answer->value_avg;
        }
        $self_answers = User::with('vote')->find(auth()->id())->vote->where('game_id', $game_id);
        return response()->json(['answers' => $mapped_answers ?? (object)[],
                                        'questions' => $questions,
                                        'self_votes' => ($self_answers) ?  AnswerCollection::make($self_answers) : (object)[]]);
    }

    public function vote(Request $request, string $game_id)
    {
        $value = $request->input('value');
        $question_id = $request->input('question_id');

        $question = Question::find($question_id);

        if (!$question || !$question->is_enabled) {
            return back()->withErrors(['ok' => false, 'message' => 'game.question_not_found']);
        }
        if (($question->max_val && $value > $question->max_val) || ($question->min_val && $value < $question->min_val)) {
            return back()->withErrors(['ok' => false, 'message' => 'game.vote_not_in_bounds']);
        }
        if(!($user_id = auth()->id())){
            return back()->withErrors(['ok' => false, 'message' => 'game.unknown_user']);
        }
        Answer::updateOrCreate(['question_id' => $question_id, 'game_id' => $game_id, 'user_id' => $user_id], ['value' => $value]);
        return back();
    }

}
