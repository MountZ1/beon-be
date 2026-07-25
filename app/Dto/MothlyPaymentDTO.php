<?php

namespace App\Dto;

use Carbon\Month;
use Illuminate\Http\Request;

class MonthlyPaymentDTO
{
    public function __construct(
        public readonly int $resident_id,
        public readonly string $type_payment,
        public readonly Month $month,
        public readonly int $year,
        public readonly string $flow_type,
        public readonly ?int $money_value,
        public readonly ?string $description,
    ) {}

    public static function fromRequest(Request $req): self
    {
        return new self(
            resident_id: (int) $req->input('resident_id'),
            type_payment: $req->input('type_payment'),
            month: Month::from((int) $req->input('month')),
            year: (int) $req->input('year'),
            flow_type: $req->input('flow_type'),
            money_value: $req->filled('money_value') ? (int) $req->input('money_value') : null,
            description: $req->input('description'),
        );
    }
}
