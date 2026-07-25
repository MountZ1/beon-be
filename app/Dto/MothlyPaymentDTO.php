<?php

namespace App\Dto;

use Carbon\Month;
use Illuminate\Http\Request;
use Ramsey\Uuid\Type\Integer;

class MonthlyPaymentDTO
{
    public function __construct(
        public readonly int $resident_id,
        public readonly string $type_payment,
        public readonly Month $month,
        public readonly int $year,
        public readonly string $flow_type,
        public readonly ?int $money_value,
        public readonly ?string $description
    ) {}

    public static function fromRequest(Request $req): self
    {
        return new self(
            resident_id: $req->input('resident_id'),
            type_payment: $req->input('type_payment'),
            month: $req->input('month'),
            year: $req->input('year'),
            flow_type: $req->input('flow_type'),
            money_value: $req->input('money_value'),
            description: $req->input('description'),
        );
    }
}
