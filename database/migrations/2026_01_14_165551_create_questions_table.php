<?php

use App\Models\TranslationKey;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('lang', 16)->nullable();
            $table->foreign('lang')->references('code')->on('languages');
            $table->foreignIdFor(TranslationKey::class, 'translation_id')->constrained()->cascadeOnDelete();
            $table->integer('max_val')->nullable();
            $table->integer('min_val')->nullable();
            $table->boolean('is_enabled')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
