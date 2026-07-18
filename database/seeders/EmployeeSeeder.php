<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Branch;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run(): void
     {
         $employees = [
             [
                 'name' => 'Andini (Kasir)',
                 'role' => 'Kasir',
                 'email' => 'andini@mobilecell.com',
                 'phone' => '0812-3456-7890',
                 'status' => 'Aktif',
                 'home_address' => 'Bandung, Jawa Barat',
                 'start_date' => '2023-01-15',
             ],
             [
                 'name' => 'Budi Santoso',
                 'role' => 'Kasir',
                 'email' => 'budi@mobilecell.com',
                 'phone' => '0813-9876-5432',
                 'status' => 'Aktif',
                 'home_address' => 'Semarang, Jawa Tengah',
                 'start_date' => '2023-03-20',
             ],
             [
                 'name' => 'Siti Aminah',
                 'role' => 'Kasir',
                 'email' => 'siti@mobilecell.com',
                 'phone' => '0878-1122-3344',
                 'status' => 'Aktif',
                 'home_address' => 'Yogyakarta, DIY',
                 'start_date' => '2024-02-10',
             ],
             [
                 'name' => 'Dewi Lestari',
                 'role' => 'Kasir',
                 'email' => 'dewi@mobilecell.com',
                 'phone' => '0852-5555-6666',
                 'status' => 'Aktif',
                 'home_address' => 'Surabaya, Jawa Timur',
                 'start_date' => '2024-05-01',
             ],
             [
                 'name' => 'Rian Hidayat',
                 'role' => 'Supervisor',
                 'email' => 'rian@mobilecell.com',
                 'phone' => '0811-2233-4455',
                 'status' => 'Aktif',
                 'home_address' => 'Jakarta Selatan, DKI Jakarta',
                 'start_date' => '2022-11-01',
             ],
         ];

         foreach ($employees as $emp) {
             Employee::updateOrCreate(['name' => $emp['name']], $emp);
         }
     }
}
