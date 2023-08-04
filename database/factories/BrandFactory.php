<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Brand>
 */
class BrandFactory extends Factory
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
            "company" => fake()->sentence(),
            "information" => fake()->text(),
            "user_id" => rand(1,5),
            "photo" => "https://m.media-amazon.com/images/I/71PTGKxXdDL._AC_SR920,736_.jpg"
        ];
    }
}
