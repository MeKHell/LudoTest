<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SetLang extends Controller
{
    public function update(Request $request)
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
