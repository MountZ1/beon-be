<?php

namespace App\Dto;

use Illuminate\Http\Request;

class MassMonthlyPaymentDTO
{
    public function __construct(
        public readonly int $resident_id,
        public readonly string $type_payment,
        public readonly int $month_total,
    ) {}

    public static function fromRequest(Request $req): self
    {
        return new self(
            resident_id: (int) $req->input('resident_id'),
            type_payment: $req->input('type_payment'),
            month_total: (int) $req->input('month_total'),
        );
    }
}
