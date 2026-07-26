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
    public function currentResident()
    {
        return $this->hasOne(HouseResident::class)
            ->where('start_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('end_at')
                    ->orWhere('end_at', '>', now());
            })
            ->latest('start_at');
    }

    public function reservedResident()
    {
        return $this->hasOne(HouseResident::class)
            ->where('start_at', '>', now())
            ->oldest('start_at');
    }
}
