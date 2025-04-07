<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Example: Call the UserSeeder to seed users
        $this->call([
            UsersSeeder::class,
            CursosSeeder::class,
        ]);
    }
}
