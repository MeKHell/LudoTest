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
                ['EN' => 'Q1: Quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est ?',
                        'FR' => 'Q1: Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua ?',
                        'DE' => 'Q1: Der Kater liegt auf der warmen Fensterbank und schläft friedlich ?',
                        'IT' => 'Q1: Sint occaecati cupiditate non provident, similique sunt in culpa qui officia ?'],
                ['EN' => 'Q2: Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit ?',
                        'FR' => 'Q2: Omnis dolor repellendus temporibus autem quibusdam et aut officiis debitis ?',
                        'DE' => 'Q2: Der Regen fällt sanft auf die Erde und erfrischt die Natur ?',
                        'IT' => 'Q2: Sint occaecati cupiditate non provident, similique sunt in culpa qui officia ?'],
                ['EN' => 'Q3: Omnis dolor repellendus temporibus autem quibusdam et aut officiis debitis ?',
                        'FR' => 'Q3: Quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est ?',
                        'DE' => 'Q3: Der Kaffee duftet wunderbar und schmeckt köstlich am Morgen ?',
                        'IT' => 'Q3: Et molestiae non recusandae itaque earum rerum hic tenetur a sapiente delectus ?'],
                ['EN' => 'Q4: Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris ?',
                        'FR' => 'Q4: Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore ?',
                        'DE' => 'Q4: Das Buch liegt auf dem Tisch und wartet darauf, gelesen zu werden ?',
                        'IT' => 'Q4: Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit ?'],
                ['EN' => 'Q5: Sint occaecati cupiditate non provident, similique sunt in culpa qui officia?',
                        'FR' => 'Q5: Et harum quidem rerum facilis est et expedita distinctio ?',
                        'DE' => 'Q5: Die Sonne scheint hell am blauen Himmel und wärmt die Erde ?',
                        'IT' => 'Q5: Ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus ?']
        ];

        foreach ($questions as $question) {
            $hash = hash('sha512', $question['EN']);
            $tk = TranslationKey::firstOrCreate(['hash' => $hash], ['context' => 'question']);
            foreach ($question as $key => $value) {
                $tk->fullTranslations()->upsert(['language_code'=> $key, 'text' => $value], ["language_code", "translation_id"]);
            }
            Question::firstOrCreate(['translation_id' => $tk->id], ['lang' => 'IT']);
        }
    }
}
