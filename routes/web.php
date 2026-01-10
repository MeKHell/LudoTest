<?php

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

    Route::get('game/{id}', function ($id) {
        syncLangFiles(['game', 'menu']);
        return Inertia::render('game', ['id' => $id]);
    })->name('game');
});

require __DIR__ . '/settings.php';
