<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\CashierReconciliation;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Sapaan Pintar / Alert Banner
        $criticalCount = Product::whereIn('category', ['Perdana', 'Voucher', 'Aksesoris'])
            ->where(function($q) {
                $q->where('status', 'Kritis')->orWhere('final_stock', '<=', 5);
            })->count();
        $greeting = [
            'user' => auth()->user()->name,
            'alert_title' => 'Peringatan Stok',
            'alert_message' => $criticalCount > 0 
                ? "$criticalCount item di Gudang telah mencapai batas minimum. Segera lakukan restok untuk menghindari kehilangan penjualan."
                : "Semua stok barang aman dan terkendali. Tidak ada item kritis hari ini.",
            'has_critical_alert' => $criticalCount > 0,
        ];

        // 2. The Pulse Metrics
        $totalRevenueVal = Branch::all()->sum('revenue_mtd'); // e.g. 1330000000
        $totalRevenueFormatted = 'Rp ' . number_format($totalRevenueVal, 0, ',', '.');
        
        $totalItemsSold = \App\Models\TransactionItem::whereHas('transaction', function($query) {
            $query->where('status', 'Sukses');
        })->sum('quantity');
        $totalTransactions = number_format($totalItemsSold);
        
        $avgTrxVal = $totalRevenueVal / max(1, \App\Models\Transaction::where('status', 'Sukses')->count());
        $avgTrx = 'Rp ' . number_format($avgTrxVal, 0, ',', '.');
        
        $slowMovingCount = Product::whereIn('category', ['Perdana', 'Voucher', 'Aksesoris'])
            ->whereNull('branch_id')
            ->where('sold_stock', '<', 20)
            ->count();
        $lowStockCount = Product::whereIn('category', ['Perdana', 'Voucher', 'Aksesoris'])->where('final_stock', '<=', 10)->count();

        $metrics = [
            [
                'title' => 'Total Omzet',
                'value' => $totalRevenueFormatted,
                'value_class' => 'text-2xl md:text-3xl',
                'trend' => '+15%',
                'trend_direction' => 'up',
                'trend_on_second_line' => true,
                'comparison' => 'vs kemarin',
                'border_color' => 'border-t-emerald-500',
                'link' => route('report.monthly'),
            ],
            [
                'title' => 'Total Transaksi',
                'value' => $totalTransactions,
                'trend' => 'item',
                'trend_direction' => 'neutral',
                'trend_class' => 'text-slate-900 text-xs font-semibold',
                'comparison' => 'Average ' . $avgTrx . ' / trx',
                'border_color' => 'border-t-slate-900',
                'link' => route('report.monthly'),
            ],
            [
                'title' => 'Produk Slowmoving',
                'value' => $slowMovingCount,
                'trend' => 'produk',
                'trend_direction' => 'neutral',
                'trend_class' => 'text-slate-900 text-xs font-semibold',
                'comparison' => 'Turnover lambat',
                'border_color' => 'border-t-amber-500',
                'link' => route('inventory.index'),
            ],
            [
                'title' => 'produk stok tipis',
                'value' => $lowStockCount,
                'trend' => 'produk',
                'trend_direction' => 'neutral',
                'trend_class' => 'text-slate-900 text-xs font-semibold',
                'comparison' => 'Perlu Restock Segera',
                'border_color' => 'border-t-amber-500',
                'link' => route('inventory.index'),
            ],
        ];

        // 3. Store Health Monitor (Branch Performance)
        $dbBranches = Branch::all();
        $branches = [];
        foreach ($dbBranches as $b) {
            $statusClass = 'bg-slate-400';
            $borderColor = 'border-t-slate-400';
            if ($b->status === 'Online' || $b->status === 'Open') {
                $statusClass = 'bg-green-500';
                $borderColor = $b->stock_health >= 80 ? 'border-t-green-500' : 'border-t-orange-500';
            }

            // Yesterday's revenue
            $yesterdayRevenue = \App\Models\Transaction::where('branch_id', $b->id)
                ->whereDate('created_at', now()->subDay()->toDateString())
                ->where('status', 'Sukses')
                ->sum('total_amount');

            // Yesterday's customer count
            $yesterdayTrxCount = \App\Models\Transaction::where('branch_id', $b->id)
                ->whereDate('created_at', now()->subDay()->toDateString())
                ->where('status', 'Sukses')
                ->count();
            $yesterdayCustomers = \App\Models\Transaction::where('branch_id', $b->id)
                ->whereDate('created_at', now()->subDay()->toDateString())
                ->where('status', 'Sukses')
                ->whereNotNull('customer_phone')
                ->distinct('customer_phone')
                ->count('customer_phone');
            if ($yesterdayCustomers === 0 && $yesterdayTrxCount > 0) {
                $yesterdayCustomers = $yesterdayTrxCount;
            }

            // Yesterday's gap (selisih)
            $gapText = 'Rp 0';
            $gapClass = 'text-slate-900';
            if ($b->cash_matched === false) {
                $gapText = str_replace(' ', '', $b->cash_status);
                $gapClass = 'text-commander-error';
            } elseif ($b->cash_matched === true) {
                $gapClass = 'text-emerald-600';
            } elseif ($b->cash_matched === null) {
                $gapText = '-';
            }

            // Yesterday's attendance
            $attendance = \App\Models\Attendance::where('branch_id', $b->id)
                ->whereDate('created_at', now()->subDay()->toDateString())
                ->first();

            $branches[] = [
                'id' => $b->id,
                'name' => $b->name,
                'status' => $b->status,
                'status_class' => $statusClass,
                'border_color' => $borderColor,
                'revenue_yesterday' => 'Rp ' . number_format($yesterdayRevenue, 0, ',', '.'),
                'gap_yesterday' => $gapText,
                'gap_class' => $gapClass,
                'customer_count' => $yesterdayCustomers,
                'yesterday_attendance' => $attendance ? $attendance->name : null,
                'address' => $b->address,
                'load' => $b->load ?? 'Normal',
                'last_active' => $b->last_active_at ? $b->last_active_at->diffForHumans() : null,
            ];
        }

        return view('dashboard.index', compact('greeting', 'metrics', 'branches'));
    }
}
