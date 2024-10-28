<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Defaulter>
 */
class DefaulterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'name' => fake()->lastName(),
            'debt_balance' => fake()->numberBetween($min = -1000,  $max = 2000),
            'discount_balance' => fake()->numberBetween($min = -1000,  $max = 2000),
            'total_balance' => fake()->numberBetween($min = -1000,  $max = 2000),
            'is_deleted' => fake()->boolean($chanceOfGettingTrue = 50),
            'created_at' => fake()->date(),
        ];
    }
}
