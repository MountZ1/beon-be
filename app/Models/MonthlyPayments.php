<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyPayments extends Model
{
    use HasFactory;

    protected $fillable = [
        "resident_id",
        "type_payment",
        "month",
        "year",
        "flow_type",
        "value",
        "description"
    ];
}
