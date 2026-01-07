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
            $table->id();
            $table->foreignIdFor(Language::class, 'lang_id');
            $table->foreignIdFor(Game::class, 'game_id');
            $table->unique(['lang_id', 'game_id']);
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
