<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\Thing::factory()->create([
            'name' => 'PASADA EN LIMPIO',
            'suggested_unit_price' => 0,
            'is_deleted' => false,
        ]);

        $this->call([
            DefaulterSeeder::class,
            ThingSeeder::class,
            DebtSeeder::class,
        ]);
    }
}
