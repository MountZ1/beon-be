<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Resident extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "ktp",
        "resident_status",
        "married",
        "phone_number"
    ];

    public function houseResident()
    {
        return $this->hasMany(HouseResident::class);
    }

    protected function ktp(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? asset(Storage::url($value)) : null,
        );
    }
}
