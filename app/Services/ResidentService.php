<?php

namespace App\Services;

use App\Dto\ResidentDtO;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class ResidentService
{
    public function getList(Request $request): LengthAwarePaginator
    {
        $perPage = $request->query('per_page', 10);
        $search = $request->query('search');
        $status = $request->query('status');
        $residentStatus = $request->query('resident_status');

        return Resident::select(["id", "name", "resident_status", "phone_number", "updated_at"])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                });
            })
            ->when($residentStatus, function ($query) use ($residentStatus) {
                $query->where('resident_status', $residentStatus);
            })
            ->when($status === 'aktif', function ($query) {
                $query->whereHas('houseResident', function ($q) {
                    $q->whereNull('end_at');
                });
            })
            ->when($status === 'nonaktif', function ($query) {
                $query->whereDoesntHave('houseResident', function ($q) {
                    $q->whereNull('end_at');
                });
            })
            ->with("houseResident", function ($q) {
                $q->select(['id', 'house_id', 'resident_id', 'end_at', 'start_at'])
                    ->whereNull('end_at')
                    ->where('start_at', '<=', now())
                    ->with('house:id,no_rumah')
                    ->latest('start_at')
                    ->limit(1);
            })
            ->latest()
            ->paginate($perPage);
    }

    public function create(ResidentDtO $data): Resident
    {
        $payload = [
            'name' => $data->name,
            'resident_status' => $data->resident_status,
            'phone_number' => $data->phone_number,
            'married' => $data->married,
            'ktp' => $data->ktp ? $data->ktp->store('ktp', 'public') : null,
        ];

        return Resident::create($payload);
    }

    public function update(Resident $resident, ResidentDtO $data): Resident
    {
        $payload = [
            'name' => $data->name,
            'resident_status' => $data->resident_status,
            'phone_number' => $data->phone_number,
            'married' => $data->married,
        ];

        if ($data->ktp) {
            if ($resident->ktp) {
                Storage::disk('public')->delete($resident->ktp);
            }

            $payload['ktp'] = $data->ktp->store('ktp', 'public');
        }

        $resident->update($payload);

        return $resident;
    }
}
