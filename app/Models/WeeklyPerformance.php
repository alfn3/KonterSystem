<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyPerformance extends Model
{
    use HasFactory;

    protected $table = 'weekly_performances';

    protected $fillable = [
        'week',
        'omzet',
        'hpp',
    ];

    protected $casts = [
        'omzet' => 'decimal:2',
        'hpp' => 'decimal:2',
    ];
}
