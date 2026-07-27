<?php

namespace App\Dto;

use Illuminate\Http\Request;

class MonthlyPaymentDTO
{
    public function __construct(
        public readonly ?int $resident_id,
        public readonly ?string $type_payment,
        public readonly int $month,
        public readonly int $year,
        public readonly string $flow_type,
        public readonly ?int $money_value,
        public readonly ?string $description,
    ) {}

    public static function fromRequest(Request $req): self
    {
        return new self(
            resident_id: $req->filled('resident_id') ? (int) $req->input('resident_id') : null,
            type_payment: $req->filled('type_payment') ? $req->input('type_payment') : null,
            month: (int) $req->input('month'),
            year: (int) $req->input('year'),
            flow_type: $req->input('flow_type'),
            money_value: $req->filled('value') ? (int) $req->input('value') : null,
            description: $req->input('description'),
        );
    }
}
