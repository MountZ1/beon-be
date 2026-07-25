<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resident extends Model
{
    public function houseResident()
    {
        return $this->hasMany(HouseResident::class);
    }
}
