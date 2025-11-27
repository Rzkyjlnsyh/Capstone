<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminId = User::query()->where('email', 'admin@hpe.local')->value('id');

        $products = [
            [
                'code' => 'PRD-TB01',
                'name' => 'Tongkat Baseball',
                'description' => 'Peralatan olahraga untuk latihan dan pertandingan.',
                'category' => 'Olahraga',
            ],
            [
                'code' => 'PRD-RB02',
                'name' => 'Raket Badminton',
                'description' => 'Raket badminton kelas turnamen.',
                'category' => 'Olahraga',
            ],
            [
                'code' => 'PRD-HP03',
                'name' => 'Helm Proyek',
                'description' => 'Helm keselamatan untuk konstruksi.',
                'category' => 'Konstruksi',
            ],
        ];

        foreach ($products as $product) {
            Product::query()->updateOrCreate(
                ['code' => $product['code']],
                [
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'category' => $product['category'],
                    'status' => 'active',
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                ]
            );
        }
    }
}
