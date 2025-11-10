<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetLang extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $lang = $request->input('lang');
        if (!in_array($lang, config('app.available_locales'))) {
            $lang = config('app.locale');
        }

        // Permanent memory
        (new UserController())->setlocale(Auth::user()->id, $lang);
        $request->session()->put('locale', $lang);

        // One shot memory
        app()->setLocale($lang);
        return redirect()->back();
    }
}
