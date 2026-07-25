<?php

namespace App\Dto;

use Illuminate\Http\Request as HttpRequest;

class CreateResidentHouseDTO
{
    public function __construct(
        public readonly int $resident_id,
        public readonly int $house_id,
        public readonly string $start_at,
    ) {}

    public static function fromRequest(HttpRequest $req): self
    {
        return new self(
            resident_id: $req->input('resident_id'),
            house_id: $req->input("house_id"),
            start_at: $req->input('start_at'),
        );
    }
}
