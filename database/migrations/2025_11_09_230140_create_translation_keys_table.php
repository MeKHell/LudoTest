<?php

use App\Models\TranslationKey;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translation_keys', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('hash')->unique();
            $table->string('context')->nullable();
        });

        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(TranslationKey::class, 'translation_id')->constrained('translation_keys')->cascadeOnDelete();
            $table->string('language_code', 16);
            $table->foreign('language_code')->references('code')->on('languages')->cascadeOnDelete();
            $table->text('text');
            $table->unique(['translation_id', 'language_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
        Schema::dropIfExists('translation_keys');
    }
};
