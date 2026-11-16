<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Primary Gym Administrator
        User::firstOrCreate(
            ['email' => 'admin@gym.com'],
            [
                'name' => 'Gym Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Alias for testing compatibility
        User::firstOrCreate(
            ['email' => 'admin@hardware.com'],
            [
                'name' => 'Gym Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // 2. Gym Staff User
        User::firstOrCreate(
            ['email' => 'staff@gym.com'],
            [
                'name' => 'Gym Floor Staff',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test Staff',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'email_verified_at' => now(),
            ]
        );

        // 3. Seed Gym Products, Categories, and Suppliers
        $this->call(ProductSeeder::class);
    }
}
