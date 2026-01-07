<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller
{
    public function setLocale(int $id, string $lang): void
    {
        if (!$lang || !in_array($lang, config('app.available_locales'))) {
            return;
        }
        $user = User::find($id);
        $user->lang = $lang;
        $user->save();
    }
}
