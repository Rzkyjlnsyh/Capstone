<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = Role::query()
            ->whereIn('name', ['admin', 'finance', 'viewer'])
            ->get()
            ->keyBy('name');

        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@hpe.local',
                'password' => 'Admin#123',
                'role' => 'admin',
            ],
            [
                'name' => 'Finance User',
                'email' => 'finance@hpe.local',
                'password' => 'Finance#123',
                'role' => 'finance',
            ],
            [
                'name' => 'Viewer User',
                'email' => 'viewer@hpe.local',
                'password' => 'Viewer#123',
                'role' => 'viewer',
            ],
        ];

        foreach ($users as $userData) {
            $role = $roles->get($userData['role']);

            if (! $role) {
                continue;
            }

            User::query()->updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => $userData['password'],
                    'role_id' => $role->id,
                    'status' => 'active',
                ]
            );
        }
    }
}
