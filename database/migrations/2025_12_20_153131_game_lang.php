<?php

use App\Models\Game;
use App\Models\Language;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('game_language', function (Blueprint $table) {
            $table->foreignIdFor(Language::class, 'lang_code');
            $table->foreignIdFor(Game::class, 'game_id');
            $table->primary(['lang_code', 'game_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_lang');
    }
};
