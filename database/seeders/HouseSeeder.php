<?php

namespace Database\Seeders;

use App\Models\House;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i < 21; $i++) {
            House::create([
                'no_rumah' => 'A-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'description' => 'Rumah tipe ' . fake()->randomElement(['36', '45', '60']),
                'is_occupied' => false,
            ]);
        }
    }
}
