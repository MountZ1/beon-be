<?php

namespace App\Http\Controllers;

use App\Dto\MassMonthlyPaymentDTO;
use App\Dto\MonthlyPaymentDTO;
use App\Models\monthly_payments;
use App\Models\MonthlyPayments;
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
    public function index(Request $request)
    {
        $monthlyPayments = $this->service->getList($request);

        return $this->success($monthlyPayments, "success retrive data", 200);
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
        try {
            $request->validate([
                'resident_id' => 'required|integer|exists:residents,id',
                'type_payment' => 'required|string|in:satpam,kebersihan',
                'month' => 'required|integer|min:1|max:12',
                'year' => 'required|integer|min:2000|max:2100',
                'flow_type' => 'required|string|in:in,out',
                'value' => 'required_if:flow_type,out|numeric|min:0',
                'description' => 'required_if:flow_type,out|string',
            ]);
            $monthlyPayment = $this->service->create(MonthlyPaymentDTO::fromRequest($request));

            return $this->success($monthlyPayment, "Success create monthly payment", 200);
        } catch (\Throwable $th) {
            $this->error($th->getMessage(), 409);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(MonthlyPayments $monthly_payments)
    {
        return $this->success($monthly_payments, "data berhasil diambil");
    }

    public function massPayment(Request $request)
    {
        try {
            $request->validate([
                'resident_id' => 'required|integer|exists:residents,id',
                'type_payment' => 'required|string|in:satpam,kebersihan',
                'month_total' => 'required|integer|min:1|max:24',
            ]);

            $massPayment = $this->service->massCreate(MassMonthlyPaymentDTO::fromRequest($request));

            return $this->success($massPayment, "success make mass payment");
        } catch (\Throwable $th) {
            $this->error($th->getMessage(), 500);
        }
    }

    public function getYearlySummary(Request $request)
    {
        return $this->success($this->service->getReportSummaryData($request));
    }

    public function getMonthlyPaymentHistoryByResidentId(Request $req, int $resident_id)
    {
        return $this->success($this->service->getMonthlyPaymentsByResidentId($req, $resident_id));
    }
}
