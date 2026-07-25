<?php

namespace App\Services;

use App\Dto\MonthlyPaymentDTO;
use App\Models\MonthlyPayments;
use Illuminate\Validation\ValidationException;

class MonthlyPaymentService
{
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

        return MonthlyPayments::create([
            'resident_id' => $dto->resident_id,
            'type_payment' => $dto->type_payment,
            'month' => $dto->month,
            'year' => $dto->year,
            'flow_type' => $dto->flow_type,
            'value' => $dto->money_value,
            'description' => $dto->description
        ]);
    }
}
