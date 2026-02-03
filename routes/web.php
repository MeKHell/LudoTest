<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
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

    Route::get('game/{src?}/{id}', function ($id, $src = null) {
        syncLangFiles(['game', 'menu']);
        return Inertia::render('game', ['id' => $id, 'src' => $src]);
    })->name('game')->whereNumber('id')->whereIn('src', array_keys(config("app.src")));

    Route::get('search', function () {
        syncLangFiles(['search', 'menu']);
        return Inertia::render('search');
    });
});

require __DIR__ . '/settings.php';
