<?php

use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;


Route::middleware('guest')->group(function () {
    Route::get('/', WelcomeController::class);
});
