<?php

namespace App\Http\Controllers;

use App\Http\Resources\QuestionResource;
use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    public function index(): JsonResponse
    {
        $questions = Question::where('is_enabled', true)
            ->orderBy('id')
            ->get()
            ->map(function (Question $question) {
                $topGames = Answer::query()
                    ->where('question_id', $question->id)
                    ->join('games', 'games.id', '=', 'answers.game_id')
                    ->select(
                        'games.id',
                        'games.name',
                        'games.thumb_url',
                        DB::raw('AVG(answers.value) as average_score'),
                        DB::raw('COUNT(*) as votes_count'),
                    )
                    ->groupBy('games.id', 'games.name', 'games.thumb_url')
                    ->orderByDesc('average_score')
                    ->limit(5)
                    ->get()
                    ->map(fn ($row) => [
                        'id' => $row->id,
                        'name' => $row->name,
                        'thumb_url' => $row->thumb_url,
                        'average_score' => round((float) $row->average_score, 2),
                        'votes_count' => (int) $row->votes_count,
                    ])
                    ->values();

                return array_merge(
                    (new QuestionResource($question))->resolve(),
                    ['top_games' => $topGames],
                );
            })
            ->values();

        return response()->json([
            'questions' => $questions,
        ]);
    }
}
