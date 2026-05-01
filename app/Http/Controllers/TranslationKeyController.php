<?php

namespace App\Http\Controllers;

use App\Models\TranslationKey;
use function App\lt_translate;

class TranslationKeyController extends Controller
{
    public static function ADD(string $data, string $lang, ?string $context){
        // $lang should be one of the keys from the database

        $translations = lt_translate($data, $lang);
        $hash = hash('sha512', $data);
        $tk = TranslationKey::firstOrCreate(["hash" => $hash], ["context" => $context]);

        $translations = array_map(function($t) use ($tk) {
            $t['translation_id'] = $tk->id;
            return $t;
        }, $translations);

        $tk->fullTranslations()->upsert($translations, ["language_code", "translation_id"]);
        return $tk;
    }
}
