<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SetLang extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $lang = $request->input('lang');
        if (!in_array($lang, config('app.locales'))) {
            $lang = config('app.locale');
        }

        $request->session()->put('lang', $lang);
    }
}
