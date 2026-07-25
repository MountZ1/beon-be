<?php

namespace App\Http\Controllers;

use App\Dto\ResidentDtO;
use App\Models\Resident;
use App\Services\ResidentService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ResidentController extends Controller
{
    use ApiResponse;

    public function __construct(protected ResidentService $service) {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $residents = $this->service->getList($request);

        return $this->success($residents, 'Data resident berhasil diambil');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'ktp' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'resident_status' => 'required|in:kontrak,tetap',
                'phone_number' => 'required|string|max:20',
                'married' => 'nullable|boolean',
            ]);

            $house = $this->service->create(ResidentDto::fromRequest($request));

            return $this->success($house, 'Resident berhasil ditambahkan', 201);
        } catch (\Throwable $th) {
            Log::error("failed to create new house", $th->getMessage());
            return $this->error($th->getMessage(), "Gagal menambahan penghuni baru", 500);
        }
    }

    /**
     * Display the specified resource.
     */
    // Controller
    public function show(Resident $resident)
    {
        return $this->success($resident, 'Data resident berhasil diambil');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Resident $resident)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Resident $resident)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'ktp' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'resident_status' => 'required|in:kontrak,tetap',
                'phone_number' => 'required|string|max:20',
                'married' => 'nullable|boolean',
            ]);

            $updated = $this->service->update($resident, ResidentDtO::fromRequest($request));

            return $this->success($updated, 'Resident berhasil diupdate');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Resident $resident)
    {
        //
    }
}
