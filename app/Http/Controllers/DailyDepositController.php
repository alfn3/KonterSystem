<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\DailyDeposit;

class DailyDepositController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));
        $branches = Branch::all();
        $selectedBranchId = $request->input('branch_id'); // If null, it means 'Semua'
        
        // Fetch deposits for the date
        $depositsQuery = DailyDeposit::where('date', $date);
        if ($selectedBranchId) {
            $depositsQuery->where('branch_id', $selectedBranchId);
        }
        $deposits = $depositsQuery->get()->keyBy('branch_id');
        
        return view('finance.daily_deposits', compact('date', 'branches', 'selectedBranchId', 'deposits'));
    }

    public function store(Request $request)
    {
        $date = $request->input('date');
        $branchId = $request->input('branch_id');
        $data = $request->all();

        $bendel_jutaan = isset($data['bendel_jutaan']) ? (float)str_replace(['Rp', '.', ' '], '', $data['bendel_jutaan']) : 0;
        $bendel_puluhan = isset($data['bendel_puluhan']) ? (float)str_replace(['Rp', '.', ' '], '', $data['bendel_puluhan']) : 0;
        $bendel_ribuan = isset($data['bendel_ribuan']) ? (float)str_replace(['Rp', '.', ' '], '', $data['bendel_ribuan']) : 0;
        $koin = isset($data['koin']) ? (float)str_replace(['Rp', '.', ' '], '', $data['koin']) : 0;
        
        $sisa_100_50 = isset($data['sisa_100_50']) ? (float)str_replace(['Rp', '.', ' '], '', $data['sisa_100_50']) : 0;
        $sisa_20_10_5 = isset($data['sisa_20_10_5']) ? (float)str_replace(['Rp', '.', ' '], '', $data['sisa_20_10_5']) : 0;
        $sisa_2_1 = isset($data['sisa_2_1']) ? (float)str_replace(['Rp', '.', ' '], '', $data['sisa_2_1']) : 0;
        $sisa_lain = isset($data['sisa_lain']) ? (float)str_replace(['Rp', '.', ' '], '', $data['sisa_lain']) : 0;
        
        // Total sum
        $amount = $bendel_jutaan + $bendel_puluhan + $bendel_ribuan + $koin + $sisa_100_50 + $sisa_20_10_5 + $sisa_2_1 + $sisa_lain;

        DailyDeposit::updateOrCreate(
            ['branch_id' => $branchId, 'date' => $date],
            [
                'bendel_jutaan' => $bendel_jutaan,
                'bendel_puluhan' => $bendel_puluhan,
                'bendel_ribuan' => $bendel_ribuan,
                'koin' => $koin,
                'sisa_100_50' => $sisa_100_50,
                'sisa_20_10_5' => $sisa_20_10_5,
                'sisa_2_1' => $sisa_2_1,
                'sisa_lain' => $sisa_lain,
                'amount' => $amount,
            ]
        );

        return redirect()->back()->with('success', 'Rincian setoran berhasil disimpan.');
    }
}
