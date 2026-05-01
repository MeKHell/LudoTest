<?php

use App\Models\Language;
use App\Models\Source;
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
        Schema::create('language_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('language_code');
            $table->foreign('language_code')->references('code')->on('languages')->cascadeOnDelete();
            $table->foreignIdFor(Source::class)->constrained()->cascadeOnDelete();
            $table->string('external_id');
            $table->string('external_name');
            $table->timestamps();

            $table->unique(['source_id', 'external_id']);
            $table->unique(['source_id', 'language_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('language_mappings');
    }
};
