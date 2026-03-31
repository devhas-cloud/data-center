<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'address' => null,
                'date_expired' => now()->addYear(),
                'api_key' => 'admin_api_key_12345678',
            ]
        );

        User::firstOrCreate(
            ['username' => 'user'],
            [
                'name' => 'Test User',
                'email' => 'user@example.com',
                'password' => bcrypt('password'),
                'role' => 'user',
                'address' => 'Jl. Test Address No.123, Jakarta',
                'date_expired' => now()->addMonth(),
                'api_key' => 'user_api_key_123456789',
            ]
        );
    }
}
