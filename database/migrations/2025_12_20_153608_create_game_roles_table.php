<?php

use App\Models\Game;
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
        Schema::create('game_roles', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('role'); // Can be: Publisher, Designer, Artist
            $table->string("name");
            $table->foreignIdFor(Game::class, 'bgge_id');
            $table->unique(["bgge_id", "name", 'role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artists');
    }
};
