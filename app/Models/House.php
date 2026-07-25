<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class House extends Model
{
    use HasFactory;
    protected $fillable = [
        'no_rumah',
        'description',
    ];

    public function residentHouse()
    {
        return $this->hasMany(HouseResident::class);
    }
}
