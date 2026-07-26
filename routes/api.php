<?php

use App\Http\Controllers\AnswerController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\QuestionController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [DashboardController::class, 'show'])->name('api.dashboard.show');

Route::middleware('throttle:search')->get('/search', [GameController::class, 'search'])->name('api.game.search');

Route::get('/trending', [GameController::class, 'getTrending'])->name('api.game.trending');

Route::get('/latest', [GameController::class, 'getLatest'])->name('api.game.latest');

Route::get('/random', [GameController::class, 'getRandom'])->name('api.game.random');

Route::get('/game/{src}/{id}', function($src, $id) {
    return app(GameController::class)->get($id, $src);
})->name('api.game.get')->whereIn('src', array_keys(config("app.src_list")));

Route::get('/game/{id}', [GameController::class, 'get'])
    ->name('api.game.get.default')
    ->whereNumber('id');

// Comments
Route::post('/comment', [CommentController::class, 'storeComment'])->name('api.comment.store');
Route::get('/comments/{game_id}', [CommentController::class, 'getComments'])->name('api.comment.get');
Route::put('/comment/{comment}', [CommentController::class, 'updateComment'])->name('api.comment.update');
Route::delete('/comment/{comment}', [CommentController::class, 'deleteComment'])->name('api.comment.delete');

// Library
Route::get('/library', [LibraryController::class, 'index'])->name('api.library.index');
Route::post('/library', [LibraryController::class, 'store'])->name('api.library.store');
Route::delete('/library/{game}/{listType}', [LibraryController::class, 'destroyByCategory'])
    ->whereIn('listType', ['owned', 'wishlist'])
    ->name('api.library.destroyByCategory');
Route::delete('/library/{libraryEntry}', [LibraryController::class, 'destroy'])->name('api.library.destroy');

// Answers & Questions
Route::get('/questions', [QuestionController::class, 'index'])->name('api.question.index');
Route::get('/answers/{game_id}', [AnswerController::class, 'get'])->name('api.answer.get');
Route::post('/answers/{game_id}', [AnswerController::class, 'vote'])->name('api.answer.post');
