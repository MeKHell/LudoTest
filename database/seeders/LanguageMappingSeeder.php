<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * @deprecated Language mappings are seeded with their source in SourceSeeder.
 * Kept so existing `db:seed --class=LanguageMappingSeeder` calls still work.
 */
class LanguageMappingSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(SourceSeeder::class);
    }
}
