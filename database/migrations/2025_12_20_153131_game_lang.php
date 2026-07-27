<?php

use App\Models\Game;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_language', function (Blueprint $table) {
            $table->id();
            $table->string('lang_id', 16);
            $table->foreign('lang_id')->references('code')->on('languages')->cascadeOnDelete();
            $table->foreignIdFor(Game::class, 'game_id')->constrained()->cascadeOnDelete();
            $table->unique(['lang_id', 'game_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_language');
    }
};
