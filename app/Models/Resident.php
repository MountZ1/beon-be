<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
