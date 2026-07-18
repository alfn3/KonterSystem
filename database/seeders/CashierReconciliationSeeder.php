<?php

namespace Database\Seeders;

use App\Models\CashierReconciliation;
use Illuminate\Database\Seeder;

class CashierReconciliationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reconciliations = [
            [
                'name' => 'Budi Santoso',
                'shift' => 'Shift Pagi',
                'sales' => 42500000, // Rp 42.5jt
                'gap' => 0,
                'bon' => 0,
                'incentive' => 150000,
                'status' => 'Matching',
            ],
            [
                'name' => 'Siti Aminah',
                'shift' => 'Shift Malam',
                'sales' => 38200000, // Rp 38.2jt
                'gap' => -125000, // -Rp 125k
                'bon' => 50000,
                'incentive' => 100000,
                'status' => 'Discrepancy',
            ],
            [
                'name' => 'Rian Hidayat',
                'shift' => 'Shift Pagi',
                'sales' => 51800000, // Rp 51.8jt
                'gap' => 25000, // +Rp 25k
                'bon' => 0,
                'incentive' => 200000,
                'status' => 'Surplus',
            ],
            [
                'name' => 'Dewi Lestari',
                'shift' => 'Shift Sore',
                'sales' => 29400000, // Rp 29.4jt
                'gap' => -50000, // -Rp 50k
                'bon' => 0,
                'incentive' => 0,
                'status' => 'Discrepancy',
            ],
        ];

        foreach ($reconciliations as $recon) {
            CashierReconciliation::create($recon);
        }
    }
}
