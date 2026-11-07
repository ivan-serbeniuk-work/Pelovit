<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalculatorTier extends Model
{
    protected $fillable = [
        'min_quantity',
        'discount_percent',
    ];

    protected $casts = [
        'min_quantity' => 'integer',
        'discount_percent' => 'float',
    ];
}
