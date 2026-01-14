<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Auth\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        User::create([
           'name' => 'Admin User',
           'username' => 'abubakar.it.dev',
           'email' => 'abubakar.it.dev@gmail.com',
           'password' => bcrypt('#Zahrah04'),
            'role' => 'admin',
        ]);

        User::create([
           'name' => 'Test User',
           'username' => 'test',
           'email' => 'test@gmail.com',
           'password' => bcrypt('test'),
            'role' => 'user',
            'address' => 'Jl. Test Address No.123, Jakarta',
            'date_expired' => now()->addMonth(),
            'api_key' => bin2hex(random_bytes(16)),
        ]);



    }
}
