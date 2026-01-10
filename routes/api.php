<?php

use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;

Route::get('/search', [GameController::class, 'search'])->name('api.game.search');

Route::get('/trending', [GameController::class, 'getTrending'])->name('api.game.trending');

Route::get('/latest', [GameController::class, 'getLatest'])->name('api.game.latest');

Route::get('/game/{id}', [GameController::class, 'get'])->name('api.game.get');
