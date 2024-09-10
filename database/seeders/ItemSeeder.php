<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 0; $i < 10; $i++) {
            DB::table('items')->insert([
                'defaulter_id' => 1,
                'name'  => $faker->name(),
                'unit_price' => $faker->numberBetween($min = -1000, $max = 2000),
                'quantity' => $faker->numberBetween($min = 1, $max = 3),
                'retirement_date'    => $faker->date(),
            ]);
        }
    }
}
