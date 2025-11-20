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
        User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'username' => 'admin',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'user_type' => 'admin',
            'password' => bcrypt('password'),
        ]);

        $this->call([
            AcademicLevelSeeder::class
        ]);
    }
}
