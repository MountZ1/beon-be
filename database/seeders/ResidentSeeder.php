<?php

namespace Database\Seeders;

use App\Models\Resident;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as FakerFactory;

class ResidentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = FakerFactory::create('id_ID');

        // 15 resident dengan status tetap
        for ($i = 1; $i <= 15; $i++) {
            Resident::create([
                'name' => $faker->name(),
                'ktp' => null,
                'resident_status' => 'tetap',
                'phone_number' => $faker->numerify('08##########'),
                'married' => $faker->boolean(70),
            ]);
        }

        // 8 resident dengan status kontrak
        for ($i = 1; $i <= 8; $i++) {
            Resident::create([
                'name' => $faker->name(),
                'ktp' => null,
                'resident_status' => 'kontrak',
                'phone_number' => $faker->numerify('08##########'),
                'married' => $faker->boolean(50),
            ]);
        }
    }
}
