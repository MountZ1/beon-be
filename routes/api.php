<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HouseController;
use App\Http\Controllers\MonthlyPaymentsController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AuthMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post("/login", [AuthController::class, "login"]);

Route::middleware(AuthMiddleware::class)->group(function () {
    Route::get("/me", [AuthController::class, "me"]);
    Route::post("/logout", [AuthController::class, "logout"]);

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
    });

    Route::apiResource('/houses', HouseController::class)->except(['destroy']);

    Route::group([
        'prefix' => 'monthly-payments',
        'as' => 'monthly-payment.',
        'controller' => MonthlyPaymentsController::class
    ], function () {
        Route::post("/mass-payment", "massPayment")->name("mass-payment");
        Route::get("/summary", "getYearlySummary")->name("yearly-summary");
        Route::get("/residents/{resident_id}", "getMonthlyPaymentHistoryByResidentId")->name("monthlyPaymentHistory");
    });

    Route::apiResource('/monthly-payments', MonthlyPaymentsController::class)->except(['update', 'destroy']);
    Route::apiResource("/users", UserController::class);
});
