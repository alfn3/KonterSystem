<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyDeposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'date',
        'bendel_jutaan',
        'bendel_puluhan',
        'bendel_ribuan',
        'koin',
        'sisa_100_50',
        'sisa_20_10_5',
        'sisa_2_1',
        'sisa_lain',
        'amount',
        'description',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
