<?php

namespace App\Http\Controllers;

use App\Models\monthly_payments;
use App\Services\MonthlyPaymentService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class MonthlyPaymentsController extends Controller
{
    use ApiResponse;

    public function __construct(protected MonthlyPaymentService $service) {}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(monthly_payments $monthly_payments)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(monthly_payments $monthly_payments)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, monthly_payments $monthly_payments)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(monthly_payments $monthly_payments)
    {
        //
    }
}
