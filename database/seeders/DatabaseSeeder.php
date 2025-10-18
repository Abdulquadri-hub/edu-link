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
        // User::factory(10)->create();

        User::factory()->create([
            'email' => 'test@example.com',
            "username" => "test_user",
            "first_name" => "Abdul",
            "last_name" => "Quadri",
            "phone" => "9076189518",
            "password" => bcrypt("12345678"),
            "user_type" => "admin",
            "status" => "active",
            "email_verified_at" => now()
        ]);
    }
}
