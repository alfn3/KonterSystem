<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Transaction;
use App\Models\Expense;

class DailyCashController extends Controller
{
    public function index(Request $request)
    {
        $branches = Branch::all();
        $selectedBranchId = $request->input('branch_id');
        $date = $request->input('date', date('Y-m-d'));

        // Pemasukan dari Setoran Uang Cash Harian
        $incomeQuery = \App\Models\DailyDeposit::where('date', $date);
        if ($selectedBranchId) {
            $incomeQuery->where('branch_id', $selectedBranchId);
        }
        $incomeDeposits = $incomeQuery->get();
        $pemasukan = $incomeDeposits->sum('amount');

        // Pengeluaran dari Expense (Diinput manual)
        $expenseQuery = Expense::whereDate('created_at', $date);
        if ($selectedBranchId) {
            $expenseQuery->where('branch_id', $selectedBranchId);
        }
        $expenses = $expenseQuery->get();
        $pengeluaran = $expenses->sum('amount');

        // Kalkulasi Saldo Awal (total dari hari-hari sebelumnya)
        $historicalIncomeQuery = \App\Models\DailyDeposit::where('date', '<', $date);
        $historicalExpenseQuery = Expense::whereDate('created_at', '<', $date);
        if ($selectedBranchId) {
            $historicalIncomeQuery->where('branch_id', $selectedBranchId);
            $historicalExpenseQuery->where('branch_id', $selectedBranchId);
        }
        $historicalIncome = $historicalIncomeQuery->sum('amount');
        $historicalExpense = $historicalExpenseQuery->sum('amount');
        $saldoAwal = $historicalIncome - $historicalExpense;

        // Gabungkan transaksi dan pengeluaran ke dalam satu list untuk tabel
        $mergedTransactions = collect();

        foreach($incomeDeposits as $tx) {
            $mergedTransactions->push((object)[
                'created_at' => \Carbon\Carbon::parse($tx->created_at),
                'description' => $tx->description ?? 'Setoran Tunai Toko',
                'category' => 'Pendapatan',
                'amount' => $tx->amount,
                'type' => 'in',
            ]);
        }

        foreach($expenses as $exp) {
            $mergedTransactions->push((object)[
                'created_at' => $exp->created_at,
                'description' => $exp->description ?? 'Pengeluaran',
                'category' => $exp->category,
                'amount' => $exp->amount,
                'type' => 'out',
            ]);
        }

        $transactions = $mergedTransactions->sortByDesc('created_at')->values();

        $stats = [
            'saldo_awal' => $saldoAwal,
            'pemasukan' => $pemasukan,
            'pengeluaran' => $pengeluaran,
            'saldo_akhir' => $saldoAwal + $pemasukan - $pengeluaran,
        ];

        return view('finance.daily_cash', compact('branches', 'selectedBranchId', 'date', 'stats', 'transactions'));
    }
}
