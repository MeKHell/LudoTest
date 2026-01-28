<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\TranslationKey;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $questions = [
                ['en' => 'Q1: Quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est ?',
                        'fr' => 'Q1: Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua ?',
                        'de' => 'Q1: Der Kater liegt auf der warmen Fensterbank und schläft friedlich ?',
                        'it' => 'Q1: Sint occaecati cupiditate non provident, similique sunt in culpa qui officia ?'],
                ['en' => 'Q2: Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit ?',
                        'fr' => 'Q2: Omnis dolor repellendus temporibus autem quibusdam et aut officiis debitis ?',
                        'de' => 'Q2: Der Regen fällt sanft auf die Erde und erfrischt die Natur ?',
                        'it' => 'Q2: Sint occaecati cupiditate non provident, similique sunt in culpa qui officia ?'],
                ['en' => 'Q3: Omnis dolor repellendus temporibus autem quibusdam et aut officiis debitis ?',
                        'fr' => 'Q3: Quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est ?',
                        'de' => 'Q3: Der Kaffee duftet wunderbar und schmeckt köstlich am Morgen ?',
                        'it' => 'Q3: Et molestiae non recusandae itaque earum rerum hic tenetur a sapiente delectus ?'],
                ['en' => 'Q4: Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris ?',
                        'fr' => 'Q4: Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore ?',
                        'de' => 'Q4: Das Buch liegt auf dem Tisch und wartet darauf, gelesen zu werden ?',
                        'it' => 'Q4: Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit ?'],
                ['en' => 'Q5: Sint occaecati cupiditate non provident, similique sunt in culpa qui officia?',
                        'fr' => 'Q5: Et harum quidem rerum facilis est et expedita distinctio ?',
                        'de' => 'Q5: Die Sonne scheint hell am blauen Himmel und wärmt die Erde ?',
                        'it' => 'Q5: Ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus ?']
        ];

        foreach ($questions as $question) {
            $hash = hash('sha512', $question['en']);
            $tk = TranslationKey::firstOrCreate(['hash' => $hash], ['context' => 'question']);
            foreach ($question as $key => $value) {
                $tk->fullTranslations()->upsert(['language_code'=> $key, 'text' => $value], ["language_code", "translation_id"]);
            }
            Question::firstOrCreate(['translation_id' => $tk->id], ['lang' => 'it']);
        }
    }
}
