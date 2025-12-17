<?php

use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        syncLangFiles(['welcome', 'auth', 'menu']);
        return Inertia::render('dashboard');
    })->name('dashboard');
    Route::get('/', function () {
        syncLangFiles(['welcome', 'auth', 'menu']);
        return Inertia::render(
            'welcome',
        );
    })->name('home');
});

require __DIR__ . '/settings.php';
