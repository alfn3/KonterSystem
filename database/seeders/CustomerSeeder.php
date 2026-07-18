<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Branch;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mobil1 = Branch::where('name', 'mobil1')->first();
        $mobil2 = Branch::where('name', 'mobil2')->first();
        $toko = Branch::where('name', 'toko')->first();
        $mobil4 = Branch::where('name', 'mobil4')->first();

        $customers = [
            [
                'name' => 'Ahmad Yani',
                'phone' => '0812-3456-7890',
                'branch_id' => $mobil1 ? $mobil1->id : null,
                'service_type' => 'PULSA',
            ],
            [
                'name' => 'Yuni Token PLN',
                'phone' => '0812-1111-2222',
                'branch_id' => $mobil1 ? $mobil1->id : null,
                'service_type' => 'TOKEN_PLN',
            ],
            [
                'name' => 'Yuni Tagihan PLN',
                'phone' => '0812-5555-6666',
                'branch_id' => $mobil1 ? $mobil1->id : null,
                'service_type' => 'TAGIHAN_PLN',
            ],
            [
                'name' => 'Mas Dul',
                'phone' => '0812-3333-4444',
                'branch_id' => $mobil1 ? $mobil1->id : null,
                'service_type' => 'E_WALLET',
            ],
            [
                'name' => 'Bambang Wijaya',
                'phone' => '0813-1111-1111',
                'branch_id' => $mobil2 ? $mobil2->id : null,
                'service_type' => 'PULSA',
            ],
            [
                'name' => 'Citra Lestari',
                'phone' => '0878-1122-3344',
                'branch_id' => $mobil1 ? $mobil1->id : null,
                'service_type' => 'TRANSFER',
            ],
            [
                'name' => 'Doddy Hermawan',
                'phone' => '0896-9988-7766',
                'branch_id' => $mobil1 ? $mobil1->id : null,
                'service_type' => 'GAME',
            ],
            [
                'name' => 'Eka Saputra',
                'phone' => '0813-9876-5432',
                'branch_id' => $toko ? $toko->id : null,
                'service_type' => 'PULSA',
            ],
            [
                'name' => 'Fitri Handayani',
                'phone' => '0812-9988-7766',
                'branch_id' => $mobil4 ? $mobil4->id : null,
                'service_type' => 'GAME',
            ],
        ];

        foreach ($customers as $cust) {
            Customer::updateOrCreate(['phone' => $cust['phone']], $cust);
        }
    }
}
