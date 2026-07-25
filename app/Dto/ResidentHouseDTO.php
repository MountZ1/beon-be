<?php

namespace App\Dto;

use GuzzleHttp\Psr7\Request;
use Illuminate\Http\Request as HttpRequest;

class ResidentHouseDTO
{
    public function __construct(
        public readonly string $end_at
    ) {}

    public static function fromRequest(HttpRequest $req)
    {
        return new self(
            end_at: $req->input("end_at")
        );
    }
}
