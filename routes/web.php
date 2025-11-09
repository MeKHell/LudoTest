<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use LaravelLangSyncInertia\LangHelpers;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
    Route::get('/', function () {
        syncLangFiles(['welcome', 'auth']);
        return Inertia::render(
            'welcome',
        );
    })->name('home');
});

require __DIR__ . '/settings.php';
