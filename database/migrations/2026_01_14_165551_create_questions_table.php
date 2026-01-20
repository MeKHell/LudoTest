<?php

use App\Models\TranslationKey;
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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Language::class, 'lang')->nullable()->constrained();
            $table->foreignIdFor(TranslationKey::class, "translation_id")->constrained()->cascadeOnDelete();
            $table->integer('max_val')->nullable();
            $table->integer('min_val')->nullable();
            $table->boolean('is_enabled')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
