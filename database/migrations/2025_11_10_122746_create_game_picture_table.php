<?php

use App\Models\Game;
use App\Models\Picture;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('game_picture', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Game::class);
            $table->foreignIdFor(Picture::class);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_picture');
    }
};
