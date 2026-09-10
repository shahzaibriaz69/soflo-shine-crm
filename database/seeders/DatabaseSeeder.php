<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CustomerSeeder::class,
            CustomerJobSeeder::class, // Reserved 'JobSeeder' name issue fixed
        ]);
    }
}