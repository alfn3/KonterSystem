<?php

namespace Database\Seeders;

use App\Models\WeeklyPerformance;
use Illuminate\Database\Seeder;

class WeeklyPerformanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $performances = [
            ['week' => 'Minggu 1', 'omzet' => 125000000, 'hpp' => 93750000],
            ['week' => 'Minggu 2', 'omzet' => 103750000, 'hpp' => 100000000],
            ['week' => 'Minggu 3', 'omzet' => 125000000, 'hpp' => 82500000],
            ['week' => 'Minggu 4', 'omzet' => 82500000, 'hpp' => 41250000],
        ];

        foreach ($performances as $perf) {
            WeeklyPerformance::create($perf);
        }
    }
}
