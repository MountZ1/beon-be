<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HouseResident extends Model
{
    use HasFactory;
    protected $fillable = [
        "resident_id",
        "house_id",
        "start_at",
        "end_at"
    ];

    public function house()
    {
        return $this->belongsTo(House::class);
    }

    public function resident()
    {
        return $this->belongsTo(Resident::class, 'resident_id');
    }
}
