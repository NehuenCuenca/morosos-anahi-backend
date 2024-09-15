<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'defaulter_id' => 1,
            'name' => fake()->word(),
            'unit_price' => fake()->numberBetween($min = -1000, $max = 2000),
            'quantity' => fake()->numberBetween($min = 1, $max = 3),
            'retirement_date' => fake()->date(),
            'was_paid' => fake()->boolean(25),
        ];
    }
}
