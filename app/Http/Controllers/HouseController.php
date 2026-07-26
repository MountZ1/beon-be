<?php

namespace App\Http\Controllers;

use App\Dto\CreateResidentHouseDTO;
use App\Dto\HouseDataDTO;
use App\Dto\ResidentHouseDTO;
use App\Models\House;
use App\Services\HouseService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class HouseController extends Controller
{
    use ApiResponse;

    public function __construct(protected HouseService $service) {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $houses = $this->service->getList($request);

        return $this->success($houses, 'Data rumah berhasil diambil');
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
                "no_rumah" => "required|string",
                "description" => "string"
            ]);

            $house = $this->service->create(HouseDataDTO::fromRequest($request));

            return $this->success($house, "success create house", 201);
        } catch (ValidationException $th) {
            return $this->error($th->getMessage(), 500, $th->errors());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(House $house)
    {
        return $this->success($house, "Data berhasil diambil");
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(House $house)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, House $house)
    {
        try {
            $request->validate([
                "no_rumah" => "required|string",
                "description" => "string"
            ]);

            $house = $this->service->update($house, HouseDataDTO::fromRequest($request));

            $this->success($house, "success update house", 201);
        } catch (ValidationException $th) {
            return $this->error($th->getMessage(), 500, $th->errors());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(House $house)
    {
        //
    }

    public function getHistoryResident(int $houseID, Request $req)
    {
        return $this->success($this->service->getResidentHistoryByHouseId($houseID, $req), "success mengambil data", 200);
    }

    public function updateLeavingResident(int $houseID, int $residentID, Request $req)
    {
        try {
            $req->validate([
                "end_at" => "required|string"
            ]);

            $houseResident = $this->service->updateLeavingResidentByHouse($houseID, $residentID, ResidentHouseDTO::fromRequest($req));

            return $this->success($houseResident, "success update leving resident", 200);
        } catch (ValidationException $th) {
            return $this->error($th->getMessage(), 500, $th->errors());
        }
    }

    public function addNewResidenttoHouse(Request $req)
    {
        try {
            $req->validate([
                "house_id" => "required|integer|exists:houses,id",
                "resident_id" => "required|integer|exists:residents,id",
                "start_at" => "required|string"
            ]);

            $houseResident = $this->service->createNewResidentToHouse(CreateResidentHouseDTO::fromRequest($req));

            return $this->success($houseResident, "success create new resident into this house", 200);
        } catch (ValidationException $th) {
            return $this->error($th->getMessage(), 500, $th->errors());
        }
    }

    public function confirmUpdateLeavingandNewResidenttoHouse(int $houseID)
    {
        try {
            return $this->success($this->service->confirmNewResidentToHouse($houseID), "success update and create new resident into this house", 200);
        } catch (ValidationException $th) {
            return $this->error($th->getMessage(), 500, $th->errors());
        }
    }
}
