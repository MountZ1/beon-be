<?php

namespace App\Dto;

use Illuminate\Http\Request;

class HouseDataDTO
{
    public function __construct(
        public readonly string $noRumah,
        public readonly ?string $description
    ) {}

    public static function fromRequest(Request $req): self
    {
        return new self(
            noRumah: $req->validated("no_rumah"),
            description: $req->validated("description")
        );
    }
}
