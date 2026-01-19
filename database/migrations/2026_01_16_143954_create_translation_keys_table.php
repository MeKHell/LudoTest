<?php

use App\Models\Language;
use App\Models\TranslationKey;
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
        Schema::create('translation_keys', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('hash')->unique();
            $table->string('context')->nullable();
        });
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(TranslationKey::class, 'translation_id');
            $table->foreignIdFor(Language::class)->constrained()->cascadeOnDelete();
            $table->text('text');
            $table->unique(['translation_id', 'language_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translation_keys');
        Schema::dropIfExists('translations');
    }
};
