<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'agent_id' => 'operator1',
                'whatsapp' => '08123456789',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@mobilecell.com'],
            [
                'name' => 'Admin Mobilecell',
                'password' => bcrypt('admin123'),
                'agent_id' => 'admin',
                'whatsapp' => '08123456789',
                'is_active' => true,
            ]
        );

        $this->call([
            BranchSeeder::class,
            ProductSeeder::class,
            AuditLogSeeder::class,
            CashierReconciliationSeeder::class,
            WeeklyPerformanceSeeder::class,
            StockMovementSeeder::class,
            EmployeeSeeder::class,
            CustomerSeeder::class,
        ]);
    }
}
