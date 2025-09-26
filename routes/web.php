<?php

use App\Http\Controllers\SetLang;
use App\Http\Controllers\TestNight;
use App\Http\Controllers\WelcomeController;
use App\Http\Middleware\Lang;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest', Lang::class])->group(function () {
    Route::get('/', WelcomeController::class);
    Route::get('/games', TestNight::class);
    Route::post('/lang', SetLang::class);
});
