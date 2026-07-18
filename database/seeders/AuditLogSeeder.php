<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use Illuminate\Database\Seeder;

class AuditLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $logs = [
            [
                'date' => '2026-05-28',
                'time' => '14:32:00',
                'branch_name' => 'mobil2',
                'auditor' => 'Ahmad Fauzi',
                'status' => 'Selisih',
            ],
            [
                'date' => '2026-05-28',
                'time' => '11:15:00',
                'branch_name' => 'mobil1',
                'auditor' => 'Siti Nurhaliza',
                'status' => 'Selesai',
            ],
            [
                'date' => '2026-05-27',
                'time' => '17:45:00',
                'branch_name' => 'mobil4',
                'auditor' => 'Budi Santoso',
                'status' => 'Pending',
            ],
        ];

        foreach ($logs as $log) {
            AuditLog::create($log);
        }
    }
}
