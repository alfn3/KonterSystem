<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand',
        'name',
        'sku',
        'category',
        'is_digital',
        'initial_stock',
        'incoming_stock',
        'final_stock',
        'sold_stock',
        'price',
        'hpp',
        'status',
        'branch_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_digital' => 'boolean',
    ];

    protected $attributes = [
        'is_digital' => false,
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
