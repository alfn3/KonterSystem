<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashierReconciliation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'shift',
        'sales',
        'gap',
        'bon',
        'incentive',
        'status',
    ];

    protected $casts = [
        'sales' => 'decimal:2',
        'gap' => 'decimal:2',
        'bon' => 'decimal:2',
        'incentive' => 'decimal:2',
    ];
}
