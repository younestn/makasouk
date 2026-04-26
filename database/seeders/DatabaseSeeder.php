<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            CategorySeeder::class,
            MeasurementSeeder::class,
            ProductSeeder::class,
            ShopContentSeeder::class,
            DemoDataSeeder::class,
            ClientAppFixturesSeeder::class,
        ]);
    }
}
