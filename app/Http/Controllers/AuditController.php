<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\CashierReconciliation;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index()
    {
        // 1. Executive Metrics
        $completedAudits = AuditLog::where('status', 'Selesai')->count();
        $pendingAudits = AuditLog::where('status', 'Pending')->count();
        
        $totalDiscVal = abs(CashierReconciliation::where('gap', '<', 0)->sum('gap'));
        $totalDiscrepancy = number_format($totalDiscVal / 1000000, 1) . 'M';
        
        $avgAccuracy = Branch::avg('stock_health') ?? 98.2;

        $metrics = [
            [
                'title' => 'Audits Completed',
                'value' => $completedAudits,
                'trend' => '+12%',
                'trend_type' => 'up',
                'desc' => 'Total audit selesai',
                'border_class' => 'border-t-slate-200',
                'text_class' => 'text-slate-900',
            ],
            [
                'title' => 'Pending Audits',
                'value' => sprintf('%02d', $pendingAudits),
                'trend' => 'Critical',
                'trend_type' => 'critical',
                'desc' => 'Butuh peninjauan',
                'border_class' => 'border-t-commander-error',
                'text_class' => 'text-commander-error',
            ],
            [
                'title' => 'Total Discrepancy',
                'value' => $totalDiscrepancy,
                'prefix' => 'Rp',
                'trend' => null,
                'desc' => 'Dari rekonsiliasi kasir',
                'border_class' => 'border-t-slate-200',
                'text_color' => 'text-slate-900',
                'text_class' => 'text-slate-900',
            ],
            [
                'title' => 'Stock Accuracy',
                'value' => number_format($avgAccuracy, 1) . '%',
                'progress' => $avgAccuracy,
                'trend' => null,
                'border_class' => 'border-t-slate-200',
                'text_class' => 'text-slate-900',
            ],
        ];

        // 2. Critical Alert Banner based on cashier discrepancy
        $maxDiscrepancy = CashierReconciliation::where('gap', '<', -100000)
            ->orderBy('gap', 'asc')
            ->first();

        if ($maxDiscrepancy) {
            $alert = [
                'title' => 'Peringatan: Selisih Kas Besar',
                'message' => 'Kasir ' . $maxDiscrepancy->name . ' mencatat selisih sebesar Rp ' . number_format(abs($maxDiscrepancy->gap), 0, ',', '.') . ' pada ' . $maxDiscrepancy->shift . '. Segera lakukan verifikasi CCTV.',
                'action_text' => 'Tinjau Sekarang',
            ];
        } else {
            $alert = [
                'title' => 'Semua Kasir Aman',
                'message' => 'Tidak ditemukan selisih kas signifikan pada shifts terakhir.',
                'action_text' => 'Lihat Laporan',
            ];
        }

        // 3. Audit Logs
        $dbLogs = AuditLog::orderBy('date', 'desc')->orderBy('time', 'desc')->get();
        $audit_logs = [];
        
        foreach ($dbLogs as $log) {
            $statusClass = 'bg-slate-100 text-slate-500 border-slate-200';
            $statusIcon = 'hourglass_empty';
            if ($log->status === 'Selesai') {
                $statusClass = 'bg-emerald-50 text-emerald-600 border-emerald-100';
                $statusIcon = 'check_circle';
            } elseif ($log->status === 'Selisih') {
                $statusClass = 'bg-red-50 text-commander-error border-red-100';
                $statusIcon = 'error';
            }

            // Determine branch dot color by hashing branch name
            $hash = md5($log->branch_name);
            $dotColor = 'bg-slate-900';
            if (strpos($hash, 'a') === 0 || strpos($hash, '0') === 0) {
                $dotColor = 'bg-blue-400';
            } elseif (strpos($hash, 'b') === 0 || strpos($hash, '1') === 0) {
                $dotColor = 'bg-green-400';
            }

            $audit_logs[] = [
                'id' => $log->id,
                'date' => date('d M Y', strtotime($log->date)),
                'time' => date('H:i', strtotime($log->time)) . ' WIB',
                'branch' => $log->branch_name,
                'branch_dot' => $dotColor,
                'auditor' => $log->auditor,
                'status' => $log->status,
                'status_class' => $statusClass,
                'status_icon' => $statusIcon,
            ];
        }

        // Fetch list of branches for the new audit dropdown
        $branchesList = Branch::all();

        return view('audit.index', compact('metrics', 'alert', 'audit_logs', 'branchesList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_name' => 'required|string|max:100',
            'auditor' => 'required|string|max:100',
            'status' => 'required|string|in:Selesai,Selisih,Pending',
        ]);

        AuditLog::create([
            'date' => date('Y-m-d'),
            'time' => date('H:i:s'),
            'branch_name' => $validated['branch_name'],
            'auditor' => $validated['auditor'],
            'status' => $validated['status'],
        ]);

        // If status is Selisih, also update the branch's cash status to flag it
        if ($validated['status'] === 'Selisih') {
            $branch = Branch::where('name', $validated['branch_name'])->first();
            if ($branch) {
                $branch->update([
                    'cash_status' => '- Rp1.250.000', // Mock discrepancy flag
                    'cash_matched' => false,
                ]);
            }
        } else if ($validated['status'] === 'Selesai') {
            $branch = Branch::where('name', $validated['branch_name'])->first();
            if ($branch) {
                $branch->update([
                    'cash_status' => 'Cocok',
                    'cash_matched' => true,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Audit baru berhasil diinisiasi!');
    }
}
