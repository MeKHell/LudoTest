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

    Route::get('game/{src}/{id}', function ($src, $id) {
        $game = app(\App\Services\GameService::class)->ensureGameInDb($id, $src);
        return redirect()->route('game.default', ['id' => $game->id]);
    })->name('game')->whereIn('src', array_keys(config("app.src_list")));

    Route::get('game/{id}', function ($id) {
        syncLangFiles(['game', 'menu']);
        return Inertia::render('game', ['id' => $id, 'src' => null]);
    })->name('game.default')->whereNumber('id');

    Route::get('search', function () {
        syncLangFiles(['search', 'menu']);
        return Inertia::render('search');
    });
});

require __DIR__ . '/settings.php';
