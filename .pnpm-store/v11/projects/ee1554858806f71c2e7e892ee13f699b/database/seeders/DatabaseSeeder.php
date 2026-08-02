<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CoreSeeder::class,
            CatalogSeeder::class,
            ContentSeeder::class,
            BusinessSeeder::class,
            ContactSampleSeeder::class,
            PresentationSeeder::class,
            LocalizedRouteSeeder::class,
        ]);
    }
}
