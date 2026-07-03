<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->enum('list_type', ['owned', 'wishlist']);
            $table->timestamps();

            $table->unique(['user_id', 'game_id', 'list_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_entries');
    }
};
