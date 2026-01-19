<?php

use App\Models\Game;
use App\Models\Language;
use App\Models\TranslationKey;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->longText('content');
            $table->foreignIdFor(TranslationKey::class, 'translation_id');
            $table->foreignIdFor(Language::class, 'lang')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Game::class, 'game_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'writer')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'editor')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
