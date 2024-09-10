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

        for ($i = 0; $i < 10; $i++) {
            DB::table('defaulters')->insert([
                'name'  => $faker->name,
                'negative_balance' => $faker->numberBetween($min = -1000, $max = 2000),
                'positive_balance' => $faker->numberBetween($min = -1000, $max = 2000),
                'total_balance'    => $faker->numberBetween($min = -1000, $max = 2000),
            ]);
        }
    }
}