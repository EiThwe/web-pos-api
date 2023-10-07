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
        $actual_price = rand(100, 10000);
        return [
            "name" => fake()->sentence(5),
            "brand_id" => rand(1, 20),
            "user_id" => rand(1, 5),
            "actual_price" => $actual_price,
            "sale_price" => $actual_price + 1000,
            "total_stock" => 0,
            "unit" => "pack",
            "more_information" => fake()->text(),
            "photo" => "https://m.media-amazon.com/images/I/71PTGKxXdDL._AC_SR920,736_.jpg"
        ];
    }
}
