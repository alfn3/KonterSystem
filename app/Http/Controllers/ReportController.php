<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\CashierReconciliation;
use App\Models\WeeklyPerformance;
use App\Models\TransactionItem;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function monthly()
    {
        // 1. Executive Summary Cards
        $branches = Branch::all();
        $totalRevenueVal = $branches->sum('revenue_mtd');
        $totalMarginVal = $branches->sum(function($b) {
            return $b->revenue_mtd * ($b->profit_margin / 100);
        });
        
        $totalSales = CashierReconciliation::sum('sales');
        // Simulated transaction count (avg Rp35.5k per transaction)
        $transactionCount = $totalSales > 0 ? round($totalSales / 35500) : 12450;
        
        $totalGapVal = CashierReconciliation::sum('gap');

        // Formatting functions helper local
        $formatGap = function($val) {
            if ($val == 0) return 'Rp 0';
            $sign = $val < 0 ? '-' : '+';
            $abs = abs($val);
            if ($abs >= 1000000) {
                return $sign . 'Rp ' . number_format($abs / 1000000, 1) . 'M';
            }
            if ($abs >= 1000) {
                return $sign . 'Rp ' . number_format($abs / 1000, 0) . 'K';
            }
            return $sign . 'Rp ' . number_format($abs, 0);
        };

        $summaries = [
            [
                'title' => 'Total Omzet',
                'value' => 'Rp ' . number_format($totalRevenueVal / 1000000, 1) . 'M',
                'trend' => '+12.5%',
                'trend_desc' => 'vs bulan lalu',
                'icon' => 'payments',
                'border_color' => 'border-t-slate-900',
                'text_color' => 'text-slate-900',
            ],
            [
                'title' => 'Margin Laba',
                'value' => 'Rp ' . number_format($totalMarginVal / 1000000, 1) . 'M',
                'trend' => '+8.2%',
                'trend_desc' => 'vs bulan lalu',
                'icon' => 'trending_up',
                'border_color' => 'border-t-emerald-500',
                'text_color' => 'text-slate-900',
            ],
            [
                'title' => 'Total Transaksi',
                'value' => number_format($transactionCount),
                'trend' => '+5.1%',
                'trend_desc' => 'vs bulan lalu',
                'icon' => 'receipt',
                'border_color' => 'border-t-slate-500',
                'text_color' => 'text-slate-900',
            ],
            [
                'title' => 'Selisih Kas/Stok',
                'value' => $formatGap($totalGapVal),
                'trend' => abs($totalGapVal) < 500000 ? 'Low Risk' : 'High Risk',
                'trend_desc' => 'Butuh verifikasi',
                'icon' => 'warning',
                'border_color' => $totalGapVal < 0 ? 'border-t-commander-error' : 'border-t-emerald-500',
                'text_color' => $totalGapVal < 0 ? 'text-commander-error' : 'text-emerald-600',
            ],
        ];

        // 2. Weekly Performance Trends (Omzet vs HPP)
        $dbWeekly = WeeklyPerformance::all();
        $maxOmzet = WeeklyPerformance::max('omzet') ?: 1;
        
        $weekly_trends = [];
        foreach ($dbWeekly as $w) {
            $omzetPct = round(($w->omzet / $maxOmzet) * 100);
            $hppPct = round(($w->hpp / $maxOmzet) * 100);

            // Tailwind height values like h-[X%] are safer than h-full etc when dynamic,
            // but since Tailwind classes are compiled, we can write style="..." or use standard percentages.
            // Let's output height percentages to be mapped in Blade styles directly, or classes.
            $weekly_trends[] = [
                'week' => $w->week,
                'omzet_pct' => $omzetPct,
                'hpp_pct' => $hppPct,
                'omzet_height' => "h-[$omzetPct%]",
                'hpp_height' => "h-[$hppPct%]",
            ];
        }

        // 3. Category Distributions
        $totalSoldProducts = TransactionItem::sum('quantity') ?: 1;
        $dbCategories = TransactionItem::select('product_category as category', \DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_category')
            ->orderBy('total_sold', 'desc')
            ->get();
        
        $categories = [];
        foreach ($dbCategories as $cat) {
            $name = $cat->category;
            if ($name === 'Perdana') {
                $name = 'Perdana (Kartu)';
            } elseif ($name === 'Voucher') {
                $name = 'Voucher Data';
            } elseif ($name === 'PULSA') {
                $name = 'Pulsa Seluler';
            } elseif ($name === 'PAKET_DATA') {
                $name = 'Paket Data';
            } elseif ($name === 'E_WALLET') {
                $name = 'E-Wallet';
            } elseif ($name === 'TRANSFER') {
                $name = 'Transfer Bank';
            } else {
                $name = ucfirst(strtolower(str_replace('_', ' ', $name)));
            }

            $categories[] = [
                'name' => $name,
                'percentage' => round(($cat->total_sold / $totalSoldProducts) * 100),
            ];
        }

        // 4. Selisih per Kasir (BON & INSENTIF)
        $dbRecons = CashierReconciliation::all();
        $cashier_reconciliations = [];
        foreach ($dbRecons as $r) {
            $gapClass = 'text-emerald-600';
            if ($r->gap < 0) {
                $gapClass = 'text-commander-error';
            }

            $statusClass = 'bg-emerald-100 text-emerald-700';
            if ($r->status === 'Discrepancy') {
                $statusClass = 'bg-red-50 text-commander-error border border-red-100';
            } elseif ($r->status === 'Surplus') {
                $statusClass = 'bg-emerald-100 text-emerald-700';
            }

            $cashier_reconciliations[] = [
                'name' => $r->name,
                'shift' => $r->shift,
                'sales' => 'Rp ' . number_format($r->sales / 1000000, 1) . 'jt',
                'gap' => $formatGap($r->gap),
                'gap_class' => $gapClass,
                'bon' => 'Rp ' . number_format($r->bon, 0, ',', '.'),
                'incentive' => 'Rp ' . number_format($r->incentive, 0, ',', '.'),
                'status' => $r->status,
                'status_class' => $statusClass,
            ];
        }

        // 5. Branch Performances
        $dbBranches = Branch::all();
        $branch_performances = [];
        foreach ($dbBranches as $b) {
            $type = 'Retail Only';
            if (strpos($b->name, 'Pusat') !== false || strpos($b->name, 'Central') !== false) {
                $type = 'Retail & Service';
            } elseif (strpos($b->name, 'Square') !== false || strpos($b->name, 'Town') !== false) {
                $type = 'Kiosk';
            }

            $branch_performances[] = [
                'name' => $b->name,
                'type' => $type,
                'revenue' => number_format($b->revenue_mtd / 1000000, 1) . 'jt',
                'margin' => $b->profit_margin . '.0%',
                'status' => $b->cash_matched === false ? 'Ada Selisih' : 'Selesai',
                'status_class' => $b->cash_matched === false ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700',
                'status_icon' => $b->cash_matched === false ? 'error' : 'check_circle',
            ];
        }

        // 6. Fast Move Items
        $dbFast = Product::orderBy('sold_stock', 'desc')->take(4)->get();
        $fast_move_items = [];
        foreach ($dbFast as $f) {
            // Get initials
            $words = explode(' ', $f->name);
            $initials = '';
            foreach ($words as $w) {
                $initials .= strtoupper(substr($w, 0, 1));
            }
            $initials = substr($initials, 0, 2);

            $fast_move_items[] = [
                'name' => $f->name,
                'sold' => number_format($f->sold_stock) . ' Terjual',
                'price' => 'Rp ' . number_format($f->price / 1000, 0) . 'k',
                'avatar_text' => $initials ?: 'PR',
            ];
        }

        return view('report.monthly', compact(
            'summaries',
            'weekly_trends',
            'categories',
            'cashier_reconciliations',
            'branch_performances',
            'fast_move_items'
        ));
    }
}
