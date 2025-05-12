<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserFunctionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('user_functions')->insert([
            'user_id' => 1,
            'function' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
