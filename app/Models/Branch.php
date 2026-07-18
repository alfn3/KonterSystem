<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'agent_id',
        'status',
        'last_active_at',
        'load',
        'revenue_mtd',
        'stock_available',
        'stock_health',
        'address',
        'profit_margin',
        'cash_status',
        'cash_matched',
        'saldo_elektrik',
    ];

    protected $casts = [
        'revenue_mtd' => 'decimal:2',
        'cash_matched' => 'boolean',
        'saldo_elektrik' => 'decimal:2',
        'last_active_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::retrieved(function ($branch) {
            $today = now()->toDateString();
            
            $todayRevenue = (double)\App\Models\Transaction::where('branch_id', $branch->id)
                ->where('status', 'Sukses')
                ->whereDate('created_at', $today)
                ->sum('total_amount');

            $agentName = 'Budi Santoso';
            if ($branch->name === 'mobil1') {
                $agentName = 'Andini (Kasir)';
            } elseif ($branch->name === 'mobil2') {
                $agentName = 'Budi Santoso';
            } elseif ($branch->name === 'toko') {
                $agentName = 'Siti Aminah';
            } elseif ($branch->name === 'mobil4') {
                $agentName = 'Dewi Lestari';
            }

            $recon = \App\Models\CashierReconciliation::where('name', $agentName)
                ->whereDate('created_at', $today)
                ->first();

            $todayCashStatus = 'Cocok';
            $todayCashMatched = true;
            if ($recon) {
                if ($recon->gap < 0) {
                    $formattedGap = number_format(abs($recon->gap), 0, ',', '.');
                    $todayCashStatus = "- Rp{$formattedGap}";
                    $todayCashMatched = false;
                } else {
                    $todayCashStatus = 'Cocok';
                    $todayCashMatched = true;
                }
            }

            $dbRevenue = (double)$branch->getRawOriginal('revenue_mtd');
            $dbCashStatus = $branch->getRawOriginal('cash_status');
            $dbCashMatched = (bool)$branch->getRawOriginal('cash_matched');

            if ($dbRevenue != $todayRevenue || $dbCashStatus != $todayCashStatus || $dbCashMatched != $todayCashMatched) {
                \Illuminate\Support\Facades\DB::table('branches')
                    ->where('id', $branch->id)
                    ->update([
                        'revenue_mtd' => $todayRevenue,
                        'cash_status' => $todayCashStatus,
                        'cash_matched' => $todayCashMatched,
                        'updated_at' => now(),
                    ]);
                
                $branch->setRawAttributes(array_merge($branch->getAttributes(), [
                    'revenue_mtd' => $todayRevenue,
                    'cash_status' => $todayCashStatus,
                    'cash_matched' => $todayCashMatched,
                ]));
            }
        });
    }

    /**
     * Dynamically determine status based on mobile-counter API check-in activity.
     */
    public function getStatusAttribute($value)
    {
        if ($this->last_active_at) {
            return $this->last_active_at->gt(now()->subMinutes(1)) ? 'Online' : 'Offline';
        }
        return $value;
    }

    /**
     * Dynamically calculate today's revenue from successful transactions.
     */
    public function getRevenueMtdAttribute($value)
    {
        $sum = \App\Models\Transaction::where('branch_id', $this->id)
            ->where('status', 'Sukses')
            ->whereDate('created_at', now()->toDateString())
            ->sum('total_amount');

        return (double)$sum;
    }
}
