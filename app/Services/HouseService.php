<?php

namespace App\Services;

use App\Dto\CreateResidentHouseDTO;
use App\Dto\HouseDataDTO;
use App\Dto\ResidentHouseDTO;
use App\Models\House;
use App\Models\HouseResident;
use App\Models\Resident;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HouseService
{
    public function getList(Request $request): LengthAwarePaginator
    {
        $perPage = $request->query('per_page', 10);
        $search = $request->query('search');
        $status = $request->query('status');

        $houses = House::select(['id', 'no_rumah', 'updated_at'])
            ->when($search, function ($query) use ($search) {
                $query->where('no_rumah', 'like', "%{$search}%");
            })
            ->when($status === 'aktif', function ($query) {
                $query->whereHas('residentHouse', function ($q) {
                    $q->whereNull('end_at')->where('start_at', '<=', now());
                });
            })
            ->when($status === 'nonaktif', function ($query) {
                $query->whereDoesntHave('residentHouse', function ($q) {
                    $q->whereNull('end_at')->where('start_at', '<=', now());
                });
            })
            ->withExists(['residentHouse as is_occupied' => function ($q) {
                $q->whereNull('end_at')->where('start_at', '<=', now());
            }])
            ->withExists(['residentHouse as is_reserved' => function ($q) {
                $q->whereNull('end_at')->where('start_at', '>', now());
            }])
            ->with([
                'currentResident.resident:id,name,phone_number',
                'reservedResident.resident:id,name,phone_number',
            ])
            ->latest()
            ->paginate($perPage);

        $houses->getCollection()->transform(function ($house) {
            $house->current_resident = $house->currentResident?->resident;
            $house->end_at = $house->currentResident?->end_at;

            $house->reserved_resident = $house->reservedResident?->resident;
            $house->reserved_start_at = $house->reservedResident?->start_at;

            unset($house->currentResident, $house->reservedResident);
            return $house;
        });

        return $houses;
    }

    public function create(HouseDataDTO $data): House
    {
        return House::create([
            "no_rumah" => $data->noRumah,
            "description" => $data->description
        ]);
    }

    public function update(House $house, HouseDataDTO $data): House
    {
        $house->update([
            'no_rumah' => $data->noRumah,
            'description' => $data->description,
        ]);

        return $house;
    }

    public function getResidentHistoryByHouseId(int $houseId, Request $request): LengthAwarePaginator
    {
        $perPage = $request->query('per_page', 10);
        $houseResidents = HouseResident::query()
            ->select(['id', 'house_id', 'resident_id', 'start_at', 'end_at'])
            ->where('house_id', $houseId)
            ->with(['resident' => function ($query) {
                $query->select(['id', 'name', 'phone_number']);
            }])
            ->orderByRaw('end_at IS NOT NULL')
            ->orderByDesc('end_at')
            ->paginate($perPage);

        return $houseResidents;
    }

    public function updateLeavingResidentByHouse(int $houseID, int $resident_id, ResidentHouseDTO $leaving)
    {
        $houseResident = HouseResident::where("house_id", $houseID)->where("resident_id", $resident_id)->first();

        $update = $houseResident->update([
            "end_at" => $leaving->end_at
        ]);

        return $update;
    }

    public function createNewResidentToHouse(CreateResidentHouseDTO $resident)
    {
        // Get the most recent HouseResident record for this house (active or already left)
        $lastHouseResident = HouseResident::where('house_id', $resident->house_id)
            ->with('resident')
            ->latest('start_at')
            ->first();

        if ($lastHouseResident) {
            if (is_null($lastHouseResident->end_at)) {
                // There is still an active resident (no end_at yet) → ask for confirmation
                $cacheKey = "new_resident_{$resident->house_id}_payload";
                $expiresAt = Carbon::now()->addMinutes(30);
                $suggestedEndAt = Carbon::parse($resident->start_at)->subDay();

                Cache::put($cacheKey, [
                    'house_id' => $resident->house_id,
                    'resident_id' => $resident->resident_id,
                    'start_at' => $resident->start_at,
                ], $expiresAt);

                throw ValidationException::withMessages([
                    'exception' => 'There is still an active resident in this house.',
                    'new_resident_start_at' => $resident->start_at,
                    'payload_stale_at' => $expiresAt,
                ])->status(409)->errorBag('confirmation');
            }

            if (Carbon::parse($resident->start_at)->lt(Carbon::parse($lastHouseResident->end_at))) {
                // New start_at is earlier than the last resident's end_at → invalid range
                throw ValidationException::withMessages([
                    'exception' => "There is still a resident living here until {$lastHouseResident->end_at}.",
                ]);
            }
        }

        // All checks passed → create the data
        return HouseResident::create([
            'house_id' => $resident->house_id,
            'resident_id' => $resident->resident_id,
            'start_at' => $resident->start_at,
        ]);
    }

    public function confirmNewResidentToHouse(int $houseId)
    {
        $cacheKey = "new_resident_{$houseId}_payload";

        /** @var CreateResidentHouseDTO|null $cachedPayload */
        $cachedPayload = Cache::get($cacheKey);

        if (!$cachedPayload) {
            throw ValidationException::withMessages([
                'exception' => 'Confirmation payload has expired or does not exist. Please submit the form again.',
            ])->status(410);
        }

        return DB::transaction(function () use ($cachedPayload, $houseId, $cacheKey) {
            // Find the currently active resident in this house
            $activeResident = HouseResident::where('house_id', $houseId)
                ->whereNull('end_at')
                ->latest('start_at')
                ->first();

            if ($activeResident) {
                $suggestedEndAt = Carbon::parse($cachedPayload['start_at'])->subDay();
                $activeResident->update([
                    'end_at' => $suggestedEndAt,
                ]);
            }

            // Create the new resident record
            $newResident = HouseResident::create([
                'house_id' => $houseId,
                'resident_id' => $cachedPayload['resident_id'],
                'start_at' => $cachedPayload['start_at'],
            ]);


            // Clear the cache since it's already been used
            Cache::forget($cacheKey);

            return $newResident;
        });
    }
}
