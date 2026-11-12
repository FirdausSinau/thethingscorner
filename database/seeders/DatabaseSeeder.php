<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('Seeder demo dilewati di production.');

            return;
        }

        $this->call([
            DemoUserSeeder::class,
            CatalogSeeder::class,
        ]);
    }
}
