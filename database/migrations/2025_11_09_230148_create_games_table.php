<?php

use App\Models\Description;
use App\Models\Game;
use App\Models\Language;
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
            $table->string("bgge_id");
            $table->timestamps();
            $table->string('name');
            $table->string('thumb_url')->nullable();
            $table->string('thumb_hash')->nullable();
            $table->string('thumb_blurhash')->nullable();
            $table->string('image_url')->nullable();
            $table->string('image_hash')->nullable();
            $table->string('image_blurhash')->nullable();
            $table->integer('pub_year')->nullable();
            $table->integer('min_age')->nullable();
            $table->integer('box_time')->nullable();
            $table->integer('min_time')->nullable();
            $table->integer('max_time')->nullable();
            $table->string('description_hash');
            $table->foreignIdFor(Game::class, 'version_of');
            $table->foreignIdFor(Language::class, 'lang');
            $table->string('description_hash');
            // $table->fullText('description');
            // $table->fullText('name');
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
