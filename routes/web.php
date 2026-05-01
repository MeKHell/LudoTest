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
        $name = request()->query('name');
        $game = app(\App\Services\GameService::class)->ensureGameInDb($id, $src, $name);
        return redirect()->route('game_internal', ['id' => $game->id]);
    })->name('game')->whereIn('src', array_keys(config("app.src_list")));

    Route::get('game/{id}', function ($id) {
        \App\Models\Game::findOrFail($id);
        syncLangFiles(['game', 'menu']);
        return Inertia::render('game', ['id' => $id, 'src' => null]);
    })->name('game_internal')->whereNumber('id');

    Route::get('search', function () {
        syncLangFiles(['search', 'menu']);
        return Inertia::render('search');
    });

    Route::get('admin-test', function () {
        return 'Admin Only';
    })->middleware('admin');
});

require __DIR__ . '/settings.php';
