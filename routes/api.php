<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;

Route::get('/search', [GameController::class, 'search'])->name('api.game.search');

Route::get('/trending', [GameController::class, 'getTrending'])->name('api.game.trending');

Route::get('/latest', [GameController::class, 'getLatest'])->name('api.game.latest');

Route::get('/game/{id}', [GameController::class, 'get'])->name('api.game.get');

// Comments
Route::post('/comment', [CommentController::class, 'storeComment'])->name('api.comment.store');
Route::get('/comments/{id}', [CommentController::class, 'getComments'])->name('api.comment.get');
Route::put('/comment/{id}', [CommentController::class, 'updateComment'])->name('api.comment.post');
Route::delete('/comment/{id}', [CommentController::class, 'deleteComment'])->name('api.comment.delete');
