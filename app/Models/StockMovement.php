<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_name',
        'product_sku',
        'product_category',
        'branch_name',
        'quantity_change',
        'final_stock',
        'type',
        'customer_phone',
        'customer_id',
        'destination_number',
        'payment_method',
        'payment_change',
        'reference_no',
        'operator',
        'saldo_elektrik_remaining',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
