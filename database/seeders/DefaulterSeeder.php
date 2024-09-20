<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class DefaulterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 0; $i < 30; $i++) {
            DB::table('defaulters')->insert([
                'name'  => $faker->name,
                'debt_balance' => $faker->numberBetween($min = -1000, $max = 2000),
                'discount_balance' => $faker->numberBetween($min = -1000, $max = 2000),
                'total_balance'    => $faker->numberBetween($min = -1000, $max = 2000),
                'created_at'    => fake()->date(),
            ]);
        }
    }
}