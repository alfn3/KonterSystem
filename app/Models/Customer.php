<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'branch_id',
        'service_type',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
