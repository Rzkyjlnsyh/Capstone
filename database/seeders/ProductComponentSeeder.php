<?php

namespace Database\Seeders;

use App\Models\Component;
use App\Models\Product;
use App\Models\ProductComponent;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductComponentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::query()->get()->keyBy('code');
        $components = Component::query()->get()->keyBy('code');

        $relations = [
            ['product' => 'PRD-TB01', 'component' => 'CMP-ALU', 'quantity' => 2],
            ['product' => 'PRD-TB01', 'component' => 'CMP-PAINT', 'quantity' => 1],
            ['product' => 'PRD-TB01', 'component' => 'CMP-GRIP', 'quantity' => 1],
            ['product' => 'PRD-RB02', 'component' => 'CMP-STRING', 'quantity' => 5],
            ['product' => 'PRD-RB02', 'component' => 'CMP-GRIP', 'quantity' => 1],
            ['product' => 'PRD-HP03', 'component' => 'CMP-SHELL', 'quantity' => 1],
            ['product' => 'PRD-HP03', 'component' => 'CMP-PAINT', 'quantity' => 0.5],
        ];

        foreach ($relations as $relation) {
            $product = $products->get($relation['product']);
            $component = $components->get($relation['component']);

            if (! $product || ! $component) {
                continue;
            }

            ProductComponent::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'component_id' => $component->id,
                ],
                [
                    'quantity' => $relation['quantity'],
                ]
            );
        }
    }
}
