<?php

namespace Database\Seeders;

use App\Models\Component;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ComponentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $components = [
            [
                'code' => 'CMP-ALU',
                'name' => 'Aluminum Alloy',
                'unit' => 'kg',
                'description' => 'Bahan logam untuk tongkat baseball.',
            ],
            [
                'code' => 'CMP-PAINT',
                'name' => 'Cat Finishing',
                'unit' => 'liter',
                'description' => 'Cat finishing anti gores.',
            ],
            [
                'code' => 'CMP-GRIP',
                'name' => 'Pegangan Karet',
                'unit' => 'pcs',
                'description' => 'Pegangan karet ergonomis.',
            ],
            [
                'code' => 'CMP-STRING',
                'name' => 'Senar Nylon',
                'unit' => 'm',
                'description' => 'Senar nylon untuk raket.',
            ],
            [
                'code' => 'CMP-SHELL',
                'name' => 'Cangkang Helm',
                'unit' => 'pcs',
                'description' => 'Cangkang helm ABS.',
            ],
        ];

        foreach ($components as $component) {
            Component::query()->updateOrCreate(
                ['code' => $component['code']],
                [
                    'name' => $component['name'],
                    'unit' => $component['unit'],
                    'description' => $component['description'],
                ]
            );
        }
    }
}
