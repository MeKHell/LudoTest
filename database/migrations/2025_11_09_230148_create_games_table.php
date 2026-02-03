<?php

use App\Models\Game;
use App\Models\TranslationKey;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->timestamp("last_sync_at");
            $table->string('src');
            $table->string("src_id");
            $table->string('name');
            $table->string('thumb_url')->nullable();
            $table->string('thumb_hash')->nullable();
            $table->string('thumb_blurhash')->nullable();
            $table->string('image_url')->nullable();
            $table->string('image_hash')->nullable();
            $table->string('image_blurhash')->nullable();
            $table->integer('pub_year')->nullable();
            $table->integer('min_age')->nullable();
            $table->integer('min_players')->nullable();
            $table->integer('max_players')->nullable();
            $table->integer('box_time')->nullable();
            $table->integer('min_time')->nullable();
            $table->integer('max_time')->nullable();
            $table->foreignIdFor(Game::class, 'version_of')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(TranslationKey::class, 'translation_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unique("src", "src_id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
