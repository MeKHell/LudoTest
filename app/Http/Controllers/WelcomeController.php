<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class WelcomeController extends Controller
{
    public function __invoke()
    {
        syncLangFiles('welcome');
        return Inertia::render('Welcome');
    }
}
