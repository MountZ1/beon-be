<?php

use App\Http\Controllers\HouseController;
use App\Http\Controllers\MonthlyPaymentsController;
use App\Http\Controllers\ResidentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource("/residents", ResidentController::class)->except("destroy");

Route::group([
    'prefix' => 'houses',
    'as' => 'houses.',
    'controller' => HouseController::class,
], function () {
    Route::group([
        'prefix' => 'residents',
        'as' => 'residents.',
    ], function () {
        Route::post("", "addNewResidenttoHouse")->name("new-residents");
        Route::post("/{id}", "confirmUpdateLeavingandNewResidenttoHouse")->name("confirm-update-create-resident");
        Route::get('/{id}', 'getHistoryResident')->name('history');
        Route::post('/{id}/leaving/{resident_id}', 'updateLeavingResident')->name('leaving');
    });

    Route::apiResource('/', HouseController::class)->except(['destroy']);
});

Route::group([
    'prefix' => 'monthly-payments',
    'as' => 'monthly-payment.',
    'controller' => MonthlyPaymentsController::class
], function () {
    Route::apiResource('/', MonthlyPaymentsController::class)->except(['update', 'destroy']);
});
