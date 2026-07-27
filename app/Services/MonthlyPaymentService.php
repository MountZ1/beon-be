<?php

namespace App\Services;

use App\Dto\MassMonthlyPaymentDTO;
use App\Dto\MonthlyPaymentDTO;
use App\Models\HouseResident;
use App\Models\MonthlyPayments;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class MonthlyPaymentService
{
    public function getList(Request $request): LengthAwarePaginator
    {
        $perPage = $request->query('per_page', 10);
        $month = $request->query('month', now()->month());
        $year = $request->query('year', now()->year());
        $typePayment = $request->query('type_payment');
        $flowType = $request->query('flow_type', "in");

        return MonthlyPayments::query()
            ->with(['resident' => function ($query) {
                $query->select('id', 'name', 'phone_number')
                    ->with(['houseResident' => function ($q) {
                        $q->whereNull('end_at')
                            ->with('house:id,no_rumah')
                            ->limit(1);
                    }]);
            }])
            ->when($month, fn($query) => $query->where('month', $month))
            ->when($year, fn($query) => $query->where('year', $year))
            ->when($typePayment, fn($query) => $query->where('type_payment', $typePayment))
            ->when($flowType, fn($query) => $query->where('flow_type', $flowType))
            ->latest()
            ->paginate($perPage);
    }

    public function create(MonthlyPaymentDTO $dto): MonthlyPayments
    {
        $checkMonthlyPayments = MonthlyPayments::where('resident_id', $dto->resident_id)
            ->where('year', $dto->year)
            ->where('month', $dto->month)
            ->where('type_payment', $dto->type_payment)
            ->exists();

        if ($checkMonthlyPayments) {
            throw ValidationException::withMessages([
                'month' => 'Pembayaran untuk bulan dan tahun ini sudah tercatat.',
            ]);
        }

        $value = $dto->flow_type === 'out'
            ? $dto->money_value
            : $this->getFixedPaymentValue($dto->type_payment);

        Log::info($value);

        return MonthlyPayments::create([
            'resident_id' => $dto->resident_id,
            'type_payment' => $dto->type_payment,
            'month' => $dto->month,
            'year' => $dto->year,
            'flow_type' => $dto->flow_type,
            'value' => (int)$value,
            'description' => $dto->description,
        ]);
    }

    public function massCreate(MassMonthlyPaymentDTO $dto): Collection
    {
        $houseResident = HouseResident::where('resident_id', $dto->resident_id)
            ->whereNull('end_at')
            ->latest('start_at')
            ->first();

        if (!$houseResident) {
            throw ValidationException::withMessages([
                'resident_id' => 'Resident ini tidak memiliki rumah aktif saat ini.',
            ]);
        }

        $startDate = Carbon::parse($houseResident->start_at);
        $residentStartDate = $startDate->day === 1
            ? $startDate->copy()->startOfMonth()
            : $startDate->copy()->addMonthNoOverflow()->startOfMonth();

        $lastPayment = MonthlyPayments::where('resident_id', $dto->resident_id)
            ->where('type_payment', $dto->type_payment)
            ->where('flow_type', 'in')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->first();

        $paymentStartDate = $lastPayment
            ? Carbon::create($lastPayment->year, $lastPayment->month, 1)->addMonth()
            : $residentStartDate;

        if ($paymentStartDate->lt($residentStartDate)) {
            $paymentStartDate = $residentStartDate;
        }

        $value = $this->getFixedPaymentValue($dto->type_payment);

        return DB::transaction(function () use ($dto, $paymentStartDate, $value) {
            $records = collect();

            for ($i = 0; $i < $dto->month_total; $i++) {
                $period = $paymentStartDate->copy()->addMonths($i);

                $exists = MonthlyPayments::where('resident_id', $dto->resident_id)
                    ->where('type_payment', $dto->type_payment)
                    ->where('year', $period->year)
                    ->where('month', $period->month)
                    ->exists();

                if ($exists) {
                    throw ValidationException::withMessages([
                        'month_total' => "Pembayaran bulan {$period->format('F Y')} sudah tercatat.",
                    ]);
                }

                $records->push(MonthlyPayments::create([
                    'resident_id' => $dto->resident_id,
                    'type_payment' => $dto->type_payment,
                    'month' => $period->month,
                    'year' => $period->year,
                    'flow_type' => 'in',
                    'value' => $value,
                ]));
            }

            return $records;
        });
    }
    private function getFixedPaymentValue(string $typePayment): int
    {
        return match ($typePayment) {
            'satpam' => 100000,
            'kebersihan' => 15000,
            default => throw new \InvalidArgumentException("Unknown type_payment: {$typePayment}"),
        };
    }

    public function getReportSummaryData(Request $request)
    {
        $year = $request->query('year', Carbon::now()->year);

        $monthlyData = MonthlyPayments::query()
            ->select('month', 'flow_type', DB::raw('SUM(value) as total'))
            ->where('year', $year)
            ->groupBy('month', 'flow_type')
            ->get();

        $grouped = [];
        foreach ($monthlyData as $row) {
            $grouped[$row->month][$row->flow_type] = (int) $row->total;
        }

        $monthlyIncome = collect(range(1, 12))->map(function ($month) use ($grouped) {
            return [
                'month' => Carbon::create()->month($month)->translatedFormat('F'),
                'incomes' => $grouped[$month]['in'] ?? 0,
                'outcomes' => $grouped[$month]['out'] ?? 0,
            ];
        });

        $totalIncome = MonthlyPayments::query()->where('flow_type', 'in')->sum('value');
        $totalExpense = MonthlyPayments::query()->where('flow_type', 'out')->sum('value');
        $balance = $totalIncome - $totalExpense;

        return [
            'year' => (int) $year,
            'monthly_income' => $monthlyIncome,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'balance' => $balance,
        ];
    }

    public function getMonthlyPaymentsByResidentId(Request $request, int $residentId)
    {
        $year = (int) $request->query('year', now()->year);
        $currentYear = now()->year;

        $houseResident = HouseResident::where('resident_id', $residentId)
            ->whereNull('end_at')
            ->latest('start_at')
            ->first();

        if (!$houseResident) {
            throw ValidationException::withMessages([
                'resident_id' => 'Resident ini tidak memiliki rumah aktif saat ini.',
            ]);
        }

        $startDate = Carbon::parse($houseResident->start_at);
        $firstPaymentMonth = $startDate->day === 1
            ? $startDate->copy()->startOfMonth()
            : $startDate->copy()->addMonthNoOverflow()->startOfMonth();

        $now = Carbon::now()->startOfMonth();

        $yearStart = Carbon::create($year, 1, 1)->startOfMonth();
        $yearEnd = Carbon::create($year, 12, 1)->startOfMonth();

        $periodStart = $firstPaymentMonth->gt($yearStart) ? $firstPaymentMonth : $yearStart;
        $periodEnd = $year < $currentYear ? $yearEnd : ($year > $currentYear ? $yearEnd : $now);

        $paidRecords = MonthlyPayments::where('resident_id', $residentId)
            ->where('flow_type', 'in')
            ->where('year', $year)
            ->get()
            ->keyBy(fn($item) => "{$item->type_payment}-{$item->year}-{$item->month}");

        $types = ['satpam', 'kebersihan'];
        $result = collect();

        if ($periodStart->gt($periodEnd)) {
            return $result;
        }

        $period = $periodStart->copy();
        while ($period->lte($periodEnd)) {
            foreach ($types as $type) {
                $key = "{$type}-{$period->year}-{$period->month}";
                $paid = $paidRecords->get($key);
                $result->push([
                    'id' => $paid->id ?? null,
                    'resident_id' => $residentId,
                    'type_payment' => $type,
                    'month' => $period->month,
                    'year' => $period->year,
                    'value' => $paid->value ?? $this->getFixedPaymentValue($type),
                    'is_pay' => (bool) $paid,
                ]);
            }
            $period->addMonth();
        }

        return $result
            ->sortBy(fn($item) => $item['year'] * 100 + $item['month'])
            ->values();
    }
}
