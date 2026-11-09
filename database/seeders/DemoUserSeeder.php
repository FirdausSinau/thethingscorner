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
            $user = User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => 'rahasia123',
                ]
            );

            $user->forceFill([
                'role' => $account['role'],
                'is_active' => true,
            ])->save();
        }
    }
}
