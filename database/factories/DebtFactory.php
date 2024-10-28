<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Debt>
 */
class DebtFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'defaulter_id' => fake()->numberBetween($min = 1, $max = 3),
            'thing_id' => fake()->numberBetween($min = 1, $max = 10),
            'unit_price' => fake()->numberBetween($min = -1000, $max = 2000),
            'quantity' => fake()->numberBetween($min = 1, $max = 3),
            'retired_at' => fake()->date(),
            'filed_at' => fake()->date(),
            'was_paid' => fake()->boolean(25),
        ];
    }
}
