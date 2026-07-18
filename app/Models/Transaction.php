<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'branch_id',
        'total_amount',
        'payment_method',
        'cash_paid',
        'change',
        'payment_change',
        'qris',
        'status',
        'proof_image',
        'customer_id',
        'customer_phone',
        'operator',
        'saldo_elektrik_remaining',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }
}
