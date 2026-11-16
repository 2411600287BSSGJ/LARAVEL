<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        if (! User::withTrashed()->whereKey(1)->exists()) {
            User::factory()->create([
                'id' => 1,
                'name' => 'Inventory Automation',
                'email' => 'inventory-automation@example.invalid',
                'password' => Str::random(48),
                'role' => 'staff',
            ]);
        }

        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Str::random(48),
                'role' => 'staff',
            ],
        );

        $adminPassword = config('inventory.admin_password');
        $admin = User::firstOrCreate(
            ['email' => 'admin@gym.test'],
            [
                'name' => 'Gym Administrator',
                'password' => $adminPassword ?: Str::random(48),
                'role' => 'admin',
                'email_verified_at' => now(),
            ],
        );
        $admin->forceFill([
            'role' => 'admin',
            'email_verified_at' => $admin->email_verified_at ?? now(),
        ])->save();
        if ($adminPassword) {
            $admin->forceFill(['password' => $adminPassword])->save();
        }

        $this->call(InventorySeeder::class);
    }
}
