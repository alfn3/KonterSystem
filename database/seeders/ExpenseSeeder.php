<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Expense;
use App\Models\Branch;

class ExpenseSeeder extends Seeder
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
        
        $categories = ['Operasional', 'Listrik', 'Konsumsi', 'Lainnya'];

        foreach ($branches as $branch) {
            foreach ($dates as $date) {
                // Buat 1-2 pengeluaran per hari per cabang
                $count = rand(1, 2);
                for($i = 0; $i < $count; $i++) {
                    Expense::create([
                        'branch_id' => $branch->id,
                        'category' => $categories[array_rand($categories)],
                        'amount' => rand(50000, 200000),
                        'description' => 'Pengeluaran harian ' . $date,
                        'created_at' => $date . ' ' . rand(10, 16) . ':00:00'
                    ]);
                }
            }
        }
    }
}
