<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Voucher>
 */
class VoucherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "phone" => fake()->phoneNumber(),
            "voucher_number" => fake()->uuid(),
            "total" => rand(1000,100000),
            "tax" => rand(10,100),
            "net_total" => rand(1000,100000),
            "user_id" => rand(1,5)
        ];
    }
}
