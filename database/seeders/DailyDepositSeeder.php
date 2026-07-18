<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DailyDeposit;
use App\Models\Branch;

class DailyDepositSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = Branch::all();
        $dates = [
            date('Y-m-d', strtotime('-2 days')),
            date('Y-m-d', strtotime('-1 days')),
            date('Y-m-d')
        ];

        foreach ($branches as $branch) {
            foreach ($dates as $date) {
                
                $bendel_jutaan = rand(1, 3) * 10000000;
                $bendel_puluhan = rand(1, 5) * 1000000;
                $bendel_ribuan = rand(1, 5) * 100000;
                $koin = rand(10, 50) * 1000;
                
                $sisa_100_50 = rand(5, 20) * 100000;
                $sisa_20_10_5 = rand(5, 20) * 20000;
                $sisa_2_1 = rand(10, 50) * 2000;
                $sisa_lain = rand(1, 10) * 500;
                
                $amount = $bendel_jutaan + $bendel_puluhan + $bendel_ribuan + $koin + $sisa_100_50 + $sisa_20_10_5 + $sisa_2_1 + $sisa_lain;
                
                DailyDeposit::create([
                    'branch_id' => $branch->id,
                    'date' => $date,
                    'bendel_jutaan' => $bendel_jutaan,
                    'bendel_puluhan' => $bendel_puluhan,
                    'bendel_ribuan' => $bendel_ribuan,
                    'koin' => $koin,
                    'sisa_100_50' => $sisa_100_50,
                    'sisa_20_10_5' => $sisa_20_10_5,
                    'sisa_2_1' => $sisa_2_1,
                    'sisa_lain' => $sisa_lain,
                    'amount' => $amount,
                    'description' => 'Setoran Harian ' . $date,
                    'created_at' => $date . ' 17:00:00'
                ]);
            }
        }
    }
}
