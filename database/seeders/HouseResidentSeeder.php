<?php

namespace Database\Seeders;

use App\Models\House;
use App\Models\HouseResident;
use App\Models\Resident;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HouseResidentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tetapResidents = Resident::where('resident_status', 'tetap')->orderBy('id')->get();
        $kontrakResidents = Resident::where('resident_status', 'kontrak')->orderBy('id')->get();
        $houses = House::orderBy('id')->get();

        $randomStartAt = function () {
            $start = Carbon::create(2023, 1, 1);
            $end = Carbon::now();
            return Carbon::createFromTimestamp(
                fake()->numberBetween($start->timestamp, $end->timestamp)
            );
        };

        foreach ($tetapResidents as $index => $resident) {
            $house = $houses[$index];
            $startAt = $randomStartAt();

            HouseResident::create([
                'resident_id' => $resident->id,
                'house_id' => $house->id,
                'start_at' => $startAt,
                'end_at' => null,
            ]);

            $house->update(['is_occupied' => true]);
        }

        $stillStaying = $kontrakResidents->slice(0, 2)->values();
        foreach ($stillStaying as $index => $resident) {
            $house = $houses[15 + $index];
            $startAt = $randomStartAt();

            HouseResident::create([
                'resident_id' => $resident->id,
                'house_id' => $house->id,
                'start_at' => $startAt,
                'end_at' => null,
            ]);

            $house->update(['is_occupied' => true]);
        }

        $alreadyLeft = $kontrakResidents->slice(2, 6)->values();
        $leftoverHouses = $houses->slice(17, 3)->values();

        foreach ($alreadyLeft as $index => $resident) {
            $house = $leftoverHouses[intdiv($index, 2)];
            $startAt = $randomStartAt();
            $endAt = (clone $startAt)->addMonths(fake()->numberBetween(1, 12));

            if ($endAt->greaterThan(now())) {
                $endAt = now()->subDays(fake()->numberBetween(1, 30));
            }

            HouseResident::create([
                'resident_id' => $resident->id,
                'house_id' => $house->id,
                'start_at' => $startAt,
                'end_at' => $endAt,
            ]);
        }

        foreach ($leftoverHouses as $house) {
            $house->update(['is_occupied' => false]);
        }
    }
}
