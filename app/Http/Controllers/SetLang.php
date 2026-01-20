<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class SetLang extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $lang = $request->input('lang');
        if (!$lang || !in_array($lang, config('app.available_locales'))) {
            $lang = App()->getLocale();
            if (!$lang || !in_array($lang, config('app.available_locales'))) {
                $lang = Config::get('app.fallback_locale');
            }
        }

        // Permanent memory
        User::find(Auth::id())->update(['lang' => $lang]);
        $request->session()->put('locale', $lang);

        // One shot memory
        app()->setLocale($lang);
        return redirect()->back();
    }
}
