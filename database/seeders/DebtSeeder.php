<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class DebtSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 0; $i < 15; $i++) {
            DB::table('defaulter_thing')->insert([
                'defaulter_id' => $faker->numberBetween($min = 1, $max = 3),
                'thing_id' => $faker->numberBetween($min = 1, $max = 10),
                'unit_price' => $faker->numberBetween($min = -1000, $max = 2000),
                'quantity' => $faker->numberBetween($min = 1, $max = 3),
                'retired_at' => $faker->dateTimeThisDecade($max = 'now', $timezone = "America/Argentina/Buenos_Aires"),
                'filed_at' => $faker->dateTimeThisDecade($max = 'now', $timezone = "America/Argentina/Buenos_Aires"),
                'was_paid' => $faker->boolean(25),
            ]);
        }
    }
}
