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
            $table->string('question_id');
            $table->foreignIdFor(Language::class, 'lang');
            $table->foreignIdFor(TranslationKey::class, "translation_id")->constrained()->cascadeOnDelete();
            $table->string('question');
            $table->integer('max_val')->nullable();
            $table->integer('min_val')->nullable();
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
