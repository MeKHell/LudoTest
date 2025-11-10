<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class SetLang extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $lang = $request->input('lang');
        if (!in_array($lang, config('app.available_locales'))) {
            $lang = config('app.locale');
        }
        $request->session()->put('locale', $lang);
        app()->setLocale($lang);
        Log::debug($lang);
        return redirect()->back();
    }
}
