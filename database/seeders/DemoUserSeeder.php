<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            ['name' => 'Admin Master', 'email' => 'admin@demo.local', 'role' => UserRole::AdminMaster],
            ['name' => 'Admin Biasa', 'email' => 'adminbiasa@demo.local', 'role' => UserRole::Admin],
            ['name' => 'Budi Santoso', 'email' => 'budi@demo.local', 'role' => UserRole::Buyer],
        ];

        foreach ($accounts as $account) {
            $user = User::firstOrNew(['email' => $account['email']]);

            if ($user->exists) {
                continue;
            }

            $user->forceFill([
                'name' => $account['name'],
                'password' => 'rahasia123',
                'role' => $account['role'],
                'is_active' => true,
            ])->save();
        }
    }
}
