<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupExpensePhotos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-expense-photos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup expense photos older than 1 day';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expenses = \App\Models\Expense::whereNotNull('photo_path')
            ->where('created_at', '<', now()->subDay())
            ->get();

        $count = 0;
        foreach ($expenses as $expense) {
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($expense->photo_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($expense->photo_path);
                $expense->photo_path = null;
                $expense->save();
                $count++;
            }
        }

        $this->info("Deleted {$count} expense photos.");
    }
}
