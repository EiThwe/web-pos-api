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
            "name" => fake()->sentence(),
            "brand_id" => rand(1, 20),
            "user_id" => rand(1, 5),
            "actual_price" => rand(1000, 10000),
            "sale_price" => rand(10000, 100000),
            "total_stock" => rand(10000, 100000),
            "unit" => "pack",
            "more_information" => fake()->text(),
            "photo" => "https://www.italiangourmetuk.co.uk/cdn/shop/products/pepsi-soft-drink-pepsi-cola-pet-500ml-29674745168037.jpg?v=1650657442&width=480"
        ];
    }
}
