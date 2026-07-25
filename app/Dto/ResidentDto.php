<?php

namespace App\Dto;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class ResidentDto
{
    public function __construct(
        public readonly string $name,
        public readonly UploadedFile $ktp,
        public readonly string $resident_status,
        public readonly string $phone_number,
        public readonly bool $married
    ) {}

    public static function fromRequest(Request $req): self
    {
        return new self(
            name: $req->input('name'),
            ktp: $req->file('ktp'),
            resident_status: $req->input('resident_status'),
            phone_number: $req->input('phone_number'),
            married: $req->boolean('married'),
        );
    }
}
