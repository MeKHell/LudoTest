<?php

use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('translation_id')->constrained('translation_keys')->cascadeOnDelete();
            $table->string('lang', 16);
            $table->foreign('lang')->references('code')->on('languages')->cascadeOnDelete();
            $table->foreignIdFor(Game::class, 'game_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'writer')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'editor')->nullable();
            $table->unique(['writer', 'game_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
