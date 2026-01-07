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
        Schema::create('descriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Language::class, 'lang');
            $table->foreignIdFor(Game::class, 'game_id');
            $table->string('en_hash');
            $table->longText('description');
            $table->unique(['lang', 'game_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('descriptions');
    }
};
