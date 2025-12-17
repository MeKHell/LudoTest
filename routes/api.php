<?php

use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;

Route::get('/search', [GameController::class, 'search']);

Route::get('/trending', [GameController::class, 'getTrending']);

Route::get('/latest', [GameController::class, 'getLatest']);

Route::get('/game/{id}', [GameController::class, 'get']);
