<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(),
            'description' => $this->faker->text(),
            // Public image URL so we don't need uploads/storage for this assignment.
            'image_url' => 'https://picsum.photos/seed/' . $this->faker->unique()->numberBetween(1, 999999) . '/600/400',
            'price' => $this->faker->numberBetween(100, 1000),
            'sku' => $this->faker->unique()->numberBetween(100000, 999999),
            'stock_quantity' => $this->faker->numberBetween(0, 100),
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
            //
        ];
    }
}
