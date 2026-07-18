<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [
            [
                'name' => 'mobil1',
                'status' => 'Online',
                'load' => 'Sangat Ramai',
                'revenue_mtd' => 42500000, // Rp 42.5jt (stored in numeric)
                'stock_available' => 8400,
                'stock_health' => 92,
                'address' => 'Jl. Merdeka No. 45',
                'profit_margin' => 28,
                'cash_status' => 'Cocok',
                'cash_matched' => true,
                'saldo_elektrik' => 15000000,
            ],
            [
                'name' => 'mobil2',
                'status' => 'Online',
                'load' => 'Sangat Ramai',
                'revenue_mtd' => 78100000, // Rp 78.1jt
                'stock_available' => 12200,
                'stock_health' => 85,
                'address' => 'Sudirman Tower Lt. 1',
                'profit_margin' => 15,
                'cash_status' => '- Rp1.250.000',
                'cash_matched' => false,
                'saldo_elektrik' => 5200000,
            ],
            [
                'name' => 'toko',
                'status' => 'Online',
                'load' => 'Normal',
                'revenue_mtd' => 19800000, // Rp 19.8jt
                'stock_available' => 3100,
                'stock_health' => 42,
                'address' => 'Setiabudi 102',
                'profit_margin' => 24,
                'cash_status' => 'Cocok',
                'cash_matched' => true,
                'saldo_elektrik' => 850000,
            ],
            [
                'name' => 'mobil4',
                'status' => 'Offline',
                'load' => 'Normal',
                'revenue_mtd' => 12400000, // Rp 12.4jt
                'stock_available' => 5800,
                'stock_health' => 78,
                'address' => 'Stasiun Gubeng Area',
                'profit_margin' => 22,
                'cash_status' => 'Locked',
                'cash_matched' => null,
                'saldo_elektrik' => 950000,
            ],
        ];

        foreach ($branches as $branch) {
            Branch::create($branch);
        }
    }
}
