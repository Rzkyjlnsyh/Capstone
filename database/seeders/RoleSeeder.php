<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'description' => 'Administrator with full access',
            ],
            [
                'name' => 'finance',
                'description' => 'Finance user for purchase history and HPE calculations',
            ],
            [
                'name' => 'viewer',
                'description' => 'Read-only user for reports and dashboards',
            ],
        ];

        foreach ($roles as $role) {
            Role::query()->firstOrCreate(
                ['name' => $role['name']],
                ['description' => $role['description']]
            );
        }
    }
}
