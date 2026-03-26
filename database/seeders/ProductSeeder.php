<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Do not use factories here: production deploys typically skip dev dependencies.
        // Seed a predictable catalog (>= 9 products) for the assignment.
        $items = [
            [
                'sku' => 100001,
                'name' => 'Classic Hoodie',
                'description' => 'Warm everyday hoodie.',
                'price' => 39,
                'stock_quantity' => 25,
                'image_url' => 'https://picsum.photos/seed/hoodie/600/400',
            ],
            [
                'sku' => 100002,
                'name' => 'Minimal T-Shirt',
                'description' => 'Soft cotton t-shirt.',
                'price' => 19,
                'stock_quantity' => 50,
                'image_url' => 'https://picsum.photos/seed/tshirt/600/400',
            ],
            [
                'sku' => 100003,
                'name' => 'Running Shoes',
                'description' => 'Lightweight shoes for daily runs.',
                'price' => 79,
                'stock_quantity' => 15,
                'image_url' => 'https://picsum.photos/seed/shoes/600/400',
            ],
            [
                'sku' => 100004,
                'name' => 'Leather Wallet',
                'description' => 'Slim wallet with card slots.',
                'price' => 29,
                'stock_quantity' => 40,
                'image_url' => 'https://picsum.photos/seed/wallet/600/400',
            ],
            [
                'sku' => 100005,
                'name' => 'Coffee Mug',
                'description' => 'Ceramic mug for hot drinks.',
                'price' => 12,
                'stock_quantity' => 100,
                'image_url' => 'https://picsum.photos/seed/mug/600/400',
            ],
            [
                'sku' => 100006,
                'name' => 'Notebook',
                'description' => 'A5 dotted notebook.',
                'price' => 9,
                'stock_quantity' => 80,
                'image_url' => 'https://picsum.photos/seed/notebook/600/400',
            ],
            [
                'sku' => 100007,
                'name' => 'Wireless Mouse',
                'description' => 'Ergonomic wireless mouse.',
                'price' => 24,
                'stock_quantity' => 30,
                'image_url' => 'https://picsum.photos/seed/mouse/600/400',
            ],
            [
                'sku' => 100008,
                'name' => 'Desk Lamp',
                'description' => 'LED lamp with adjustable brightness.',
                'price' => 34,
                'stock_quantity' => 20,
                'image_url' => 'https://picsum.photos/seed/lamp/600/400',
            ],
            [
                'sku' => 100009,
                'name' => 'Backpack',
                'description' => 'Everyday backpack with laptop sleeve.',
                'price' => 49,
                'stock_quantity' => 18,
                'image_url' => 'https://picsum.photos/seed/backpack/600/400',
            ],
        ];

        foreach ($items as $data) {
            Product::query()->updateOrCreate(
                ['sku' => $data['sku']],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'stock_quantity' => $data['stock_quantity'],
                    'image_url' => $data['image_url'],
                ],
            );
        }
    }
}
