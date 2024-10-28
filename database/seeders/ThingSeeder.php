<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ThingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 0; $i < 10; $i++) {
            DB::table('things')->insert([
                'name'  => $faker->word,
                'suggested_unit_price' => $faker->numberBetween($min = 1000, $max = 2000),
                'is_deleted' => $faker->boolean($chanceOfGettingTrue = 50),
            ]);
        }
    }
}