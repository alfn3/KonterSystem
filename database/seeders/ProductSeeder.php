<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Branch;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sudirman = Branch::where('name', 'mobil2')->first();
        $bandung = Branch::where('name', 'mobil1')->first();
        $semarang = Branch::where('name', 'toko')->first();
        $surabaya = Branch::where('name', 'mobil4')->first();

        // 1. Gudang Products (branch_id = null)
        $gudangProducts = [
            [
                'brand' => 'Telkomsel',
                'name' => 'Perdana Telkomsel Simpati 10GB Jabodetabek',
                'sku' => 'TS-SIM-10-JKT',
                'category' => 'Perdana',
                'initial_stock' => 500,
                'incoming_stock' => 0,
                'final_stock' => 450,
                'sold_stock' => 50,
                'price' => 35000,
                'hpp' => 28000,
                'status' => 'Aman',
                'branch_id' => null,
            ],
            [
                'brand' => 'Tri',
                'name' => 'Perdana Tri Happy 2GB + 1GB Chat',
                'sku' => 'TRI-HAP-3',
                'category' => 'Perdana',
                'initial_stock' => 150,
                'incoming_stock' => 0,
                'final_stock' => 120,
                'sold_stock' => 30,
                'price' => 20000,
                'hpp' => 15000,
                'status' => 'Aman',
                'branch_id' => null,
            ],
            [
                'brand' => 'Smartfren',
                'name' => 'Perdana Smartfren Unlimited 7 Hari',
                'sku' => 'SF-UNL-7D',
                'category' => 'Perdana',
                'initial_stock' => 1000,
                'incoming_stock' => 0,
                'final_stock' => 800,
                'sold_stock' => 200,
                'price' => 25000,
                'hpp' => 18000,
                'status' => 'Aman',
                'branch_id' => null,
            ],
            [
                'brand' => 'Indosat',
                'name' => 'Voucher Indosat Freedom 3GB',
                'sku' => 'IND-FRE-3',
                'category' => 'Voucher',
                'initial_stock' => 2500,
                'incoming_stock' => 0,
                'final_stock' => 2100,
                'sold_stock' => 400,
                'price' => 15000,
                'hpp' => 11000,
                'status' => 'Aman',
                'branch_id' => null,
            ],
            [
                'brand' => 'XL',
                'name' => 'Voucher XL Combo Flex 12GB',
                'sku' => 'XL-COM-12',
                'category' => 'Voucher',
                'initial_stock' => 6000,
                'incoming_stock' => 0,
                'final_stock' => 5400,
                'sold_stock' => 600,
                'price' => 45000,
                'hpp' => 35000,
                'status' => 'Aman',
                'branch_id' => null,
            ],
            [
                'brand' => 'Orico',
                'name' => 'Kabel Data Type-C Orico 1M Black',
                'sku' => 'ACC-ORI-TYPC-BK',
                'category' => 'Aksesoris',
                'initial_stock' => 30,
                'incoming_stock' => 0,
                'final_stock' => 25,
                'sold_stock' => 5,
                'price' => 25000,
                'hpp' => 15000,
                'status' => 'Aman',
                'branch_id' => null,
            ],
            [
                'brand' => 'Apple',
                'name' => 'Silicon Case iPhone 14 Pro Max Clear',
                'sku' => 'ACC-IP14PM-SIL',
                'category' => 'Aksesoris',
                'initial_stock' => 10,
                'incoming_stock' => 0,
                'final_stock' => 0,
                'sold_stock' => 10,
                'price' => 75000,
                'hpp' => 45000,
                'status' => 'Habis',
                'branch_id' => null,
            ],
        ];

        foreach ($gudangProducts as $gp) {
            $gp['is_digital'] = false;
            Product::create($gp);
        }

        // 2. mobil2 Products (branch_id = $sudirman->id)
        if ($sudirman) {
            $sudirmanProducts = [
                [
                    'brand' => 'Telkomsel',
                    'name' => 'Perdana Telkomsel Simpati 10GB Jabodetabek',
                    'sku' => 'TS-SIM-10-JKT',
                    'category' => 'Perdana',
                    'initial_stock' => 10,
                    'incoming_stock' => 0,
                    'final_stock' => 8,
                    'sold_stock' => 2,
                    'price' => 35000,
                    'hpp' => 28000,
                    'status' => 'Kritis',
                    'branch_id' => $sudirman->id,
                ],
                [
                    'brand' => 'Tri',
                    'name' => 'Perdana Tri Happy 2GB + 1GB Chat',
                    'sku' => 'TRI-HAP-3',
                    'category' => 'Perdana',
                    'initial_stock' => 5,
                    'incoming_stock' => 0,
                    'final_stock' => 2,
                    'sold_stock' => 3,
                    'price' => 20000,
                    'hpp' => 15000,
                    'status' => 'Kritis',
                    'branch_id' => $sudirman->id,
                ],
                [
                    'brand' => 'Smartfren',
                    'name' => 'Perdana Smartfren Unlimited 7 Hari',
                    'sku' => 'SF-UNL-7D',
                    'category' => 'Perdana',
                    'initial_stock' => 40,
                    'incoming_stock' => 0,
                    'final_stock' => 32,
                    'sold_stock' => 8,
                    'price' => 25000,
                    'hpp' => 18000,
                    'status' => 'Aman',
                    'branch_id' => $sudirman->id,
                ],
                [
                    'brand' => 'Indosat',
                    'name' => 'Voucher Indosat Freedom 3GB',
                    'sku' => 'IND-FRE-3',
                    'category' => 'Voucher',
                    'initial_stock' => 50,
                    'incoming_stock' => 0,
                    'final_stock' => 45,
                    'sold_stock' => 5,
                    'price' => 15000,
                    'hpp' => 11000,
                    'status' => 'Aman',
                    'branch_id' => $sudirman->id,
                ],
                [
                    'brand' => 'XL',
                    'name' => 'Voucher XL Combo Flex 12GB',
                    'sku' => 'XL-COM-12',
                    'category' => 'Voucher',
                    'initial_stock' => 200,
                    'incoming_stock' => 0,
                    'final_stock' => 156,
                    'sold_stock' => 44,
                    'price' => 45000,
                    'hpp' => 35000,
                    'status' => 'Aman',
                    'branch_id' => $sudirman->id,
                ],
                [
                    'brand' => 'Orico',
                    'name' => 'Kabel Data Type-C Orico 1M Black',
                    'sku' => 'ACC-ORI-TYPC-BK',
                    'category' => 'Aksesoris',
                    'initial_stock' => 15,
                    'incoming_stock' => 0,
                    'final_stock' => 12,
                    'sold_stock' => 3,
                    'price' => 25000,
                    'hpp' => 15000,
                    'status' => 'Aman',
                    'branch_id' => $sudirman->id,
                ],
                [
                    'brand' => 'Apple',
                    'name' => 'Silicon Case iPhone 14 Pro Max Clear',
                    'sku' => 'ACC-IP14PM-SIL',
                    'category' => 'Aksesoris',
                    'initial_stock' => 8,
                    'incoming_stock' => 0,
                    'final_stock' => 5,
                    'sold_stock' => 3,
                    'price' => 75000,
                    'hpp' => 45000,
                    'status' => 'Kritis',
                    'branch_id' => $sudirman->id,
                ],
                [
                    'brand' => 'Dana',
                    'name' => 'Top Up Dana 100k',
                    'sku' => 'DIG-DANA-100K',
                    'category' => 'Digital',
                    'initial_stock' => 0,
                    'incoming_stock' => 0,
                    'final_stock' => 0,
                    'sold_stock' => 0,
                    'price' => 102000,
                    'hpp' => 100000,
                    'status' => 'Aman',
                    'branch_id' => $sudirman->id,
                ],
                [
                    'brand' => 'XL',
                    'name' => 'Pulsa XL 30k',
                    'sku' => 'DIG-PULSA-30K',
                    'category' => 'Digital',
                    'initial_stock' => 0,
                    'incoming_stock' => 0,
                    'final_stock' => 0,
                    'sold_stock' => 0,
                    'price' => 33000,
                    'hpp' => 30000,
                    'status' => 'Aman',
                    'branch_id' => $sudirman->id,
                ],
            ];

            foreach ($sudirmanProducts as $sp) {
                $isDigital = ($sp['category'] === 'Digital');
                $sp['is_digital'] = $isDigital;
                if ($isDigital) {
                    $sp['initial_stock'] = null;
                    $sp['incoming_stock'] = null;
                    $sp['final_stock'] = null;
                    $sp['sold_stock'] = null;
                    $sp['status'] = null;
                } else {
                    $sp['is_digital'] = false;
                }
                Product::create($sp);
            }
        }

        // 3. Seed other branches with dummy data so they don't look empty when toggled
        $otherBranches = array_filter([$bandung, $semarang, $surabaya]);
        foreach ($otherBranches as $branch) {
            foreach ($gudangProducts as $gp) {
                $branchProduct = $gp;
                $branchProduct['branch_id'] = $branch->id;
                // Add some variations
                $branchProduct['initial_stock'] = rand(10, 50);
                $branchProduct['final_stock'] = rand(2, $branchProduct['initial_stock']);
                $branchProduct['sold_stock'] = $branchProduct['initial_stock'] - $branchProduct['final_stock'];
                $branchProduct['incoming_stock'] = rand(0, 10);
                $branchProduct['is_digital'] = false;
                
                $status = 'Aman';
                if ($branchProduct['final_stock'] == 0) {
                    $status = 'Habis';
                } elseif ($branchProduct['final_stock'] <= 5) {
                    $status = 'Kritis';
                } elseif ($branchProduct['final_stock'] <= 10) {
                    $status = 'Tipis';
                }
                $branchProduct['status'] = $status;
                Product::create($branchProduct);
            }
        }

        // 4. Seed comprehensive digital products for all branches
        $digitalProducts = [
            ['brand' => 'Telkomsel', 'name' => 'Pulsa Telkomsel 5.000', 'sku' => 'p_tsel_5', 'category' => 'PULSA', 'price' => 7000, 'hpp' => 5000],
            ['brand' => 'Telkomsel', 'name' => 'Pulsa Telkomsel 10.000', 'sku' => 'p_tsel_10', 'category' => 'PULSA', 'price' => 12000, 'hpp' => 10000],
            ['brand' => 'Telkomsel', 'name' => 'Pulsa Telkomsel 20.000', 'sku' => 'p_tsel_20', 'category' => 'PULSA', 'price' => 22000, 'hpp' => 20000],
            ['brand' => 'Telkomsel', 'name' => 'Pulsa Telkomsel 50.000', 'sku' => 'p_tsel_50', 'category' => 'PULSA', 'price' => 51500, 'hpp' => 50000],
            ['brand' => 'Telkomsel', 'name' => 'Pulsa Telkomsel 100.000', 'sku' => 'p_tsel_100', 'category' => 'PULSA', 'price' => 101000, 'hpp' => 100000],
            ['brand' => 'Telkomsel', 'name' => 'Internet OMG! 4.5 GB / 30 Hari', 'sku' => 'd_tsel_1', 'category' => 'PAKET_DATA', 'price' => 32000, 'hpp' => 30000],
            ['brand' => 'Telkomsel', 'name' => 'Internet OMG! 14 GB / 30 Hari', 'sku' => 'd_tsel_2', 'category' => 'PAKET_DATA', 'price' => 68000, 'hpp' => 65000],
            ['brand' => 'Telkomsel', 'name' => 'Combo Sakti 25 GB / 30 Hari', 'sku' => 'd_tsel_3', 'category' => 'PAKET_DATA', 'price' => 95000, 'hpp' => 90000],
            ['brand' => 'Indosat', 'name' => 'Pulsa Indosat 5.000', 'sku' => 'p_isat_5', 'category' => 'PULSA', 'price' => 7000, 'hpp' => 5000],
            ['brand' => 'Indosat', 'name' => 'Pulsa Indosat 10.000', 'sku' => 'p_isat_10', 'category' => 'PULSA', 'price' => 12000, 'hpp' => 10000],
            ['brand' => 'Indosat', 'name' => 'Pulsa Indosat 50.000', 'sku' => 'p_isat_50', 'category' => 'PULSA', 'price' => 51500, 'hpp' => 50000],
            ['brand' => 'Indosat', 'name' => 'Freedom Internet 3 GB / 30 Hari', 'sku' => 'd_isat_1', 'category' => 'PAKET_DATA', 'price' => 20000, 'hpp' => 18000],
            ['brand' => 'Indosat', 'name' => 'Freedom Internet 9 GB / 30 Hari', 'sku' => 'd_isat_2', 'category' => 'PAKET_DATA', 'price' => 45000, 'hpp' => 42000],
            ['brand' => 'XL', 'name' => 'Pulsa XL 10.000', 'sku' => 'p_xl_10', 'category' => 'PULSA', 'price' => 12000, 'hpp' => 10000],
            ['brand' => 'XL', 'name' => 'Pulsa XL 50.000', 'sku' => 'p_xl_50', 'category' => 'PULSA', 'price' => 51500, 'hpp' => 50000],
            ['brand' => 'XL', 'name' => 'Xtra Combo Lite 8 GB', 'sku' => 'd_xl_1', 'category' => 'PAKET_DATA', 'price' => 35000, 'hpp' => 32000],
            ['brand' => 'Axis', 'name' => 'Pulsa Axis 10.000', 'sku' => 'p_axis_10', 'category' => 'PULSA', 'price' => 12000, 'hpp' => 10000],
            ['brand' => 'Axis', 'name' => 'Bronet 24 Jam 5 GB / 30 Hari', 'sku' => 'd_axis_1', 'category' => 'PAKET_DATA', 'price' => 25000, 'hpp' => 22000],
            ['brand' => 'Gopay', 'name' => 'Top-up Gopay 20.000', 'sku' => 'w_gopay_20', 'category' => 'E_WALLET', 'price' => 22000, 'hpp' => 20000],
            ['brand' => 'Gopay', 'name' => 'Top-up Gopay 50.000', 'sku' => 'w_gopay_50', 'category' => 'E_WALLET', 'price' => 52000, 'hpp' => 50000],
            ['brand' => 'Gopay', 'name' => 'Top-up Gopay 100.000', 'sku' => 'w_gopay_100', 'category' => 'E_WALLET', 'price' => 102000, 'hpp' => 100000],
            ['brand' => 'Dana', 'name' => 'Top-up DANA 20.000', 'sku' => 'w_dana_20', 'category' => 'E_WALLET', 'price' => 21500, 'hpp' => 20000],
            ['brand' => 'Dana', 'name' => 'Top-up DANA 50.000', 'sku' => 'w_dana_50', 'category' => 'E_WALLET', 'price' => 51500, 'hpp' => 50000],
            ['brand' => 'Dana', 'name' => 'Top-up DANA 100.000', 'sku' => 'w_dana_100', 'category' => 'E_WALLET', 'price' => 101500, 'hpp' => 100000],
            ['brand' => 'OVO', 'name' => 'Top-up OVO 20.000', 'sku' => 'w_ovo_20', 'category' => 'E_WALLET', 'price' => 22000, 'hpp' => 20000],
            ['brand' => 'OVO', 'name' => 'Top-up OVO 50.000', 'sku' => 'w_ovo_50', 'category' => 'E_WALLET', 'price' => 52000, 'hpp' => 50000],
            ['brand' => 'OVO', 'name' => 'Top-up OVO 100.000', 'sku' => 'w_ovo_100', 'category' => 'E_WALLET', 'price' => 102000, 'hpp' => 100000],
            ['brand' => 'LinkAja', 'name' => 'Top-up LinkAja 20.000', 'sku' => 'w_linkaja_20', 'category' => 'E_WALLET', 'price' => 21000, 'hpp' => 20000],
            ['brand' => 'LinkAja', 'name' => 'Top-up LinkAja 50.000', 'sku' => 'w_linkaja_50', 'category' => 'E_WALLET', 'price' => 51000, 'hpp' => 50000],
            ['brand' => 'LinkAja', 'name' => 'Top-up LinkAja 100.000', 'sku' => 'w_linkaja_100', 'category' => 'E_WALLET', 'price' => 101000, 'hpp' => 100000],
            ['brand' => 'ShopeePay', 'name' => 'Top-up ShopeePay 20.000', 'sku' => 'w_shopeepay_20', 'category' => 'E_WALLET', 'price' => 21500, 'hpp' => 20000],
            ['brand' => 'ShopeePay', 'name' => 'Top-up ShopeePay 50.000', 'sku' => 'w_shopeepay_50', 'category' => 'E_WALLET', 'price' => 51500, 'hpp' => 50000],
            ['brand' => 'ShopeePay', 'name' => 'Top-up ShopeePay 100.000', 'sku' => 'w_shopeepay_100', 'category' => 'E_WALLET', 'price' => 101500, 'hpp' => 100000],
            ['brand' => 'Mobile Legends', 'name' => 'Mobile Legends 86 Diamonds', 'sku' => 'g_ml_86', 'category' => 'GAME', 'price' => 20000, 'hpp' => 18000],
            ['brand' => 'Mobile Legends', 'name' => 'Mobile Legends 172 Diamonds', 'sku' => 'g_ml_172', 'category' => 'GAME', 'price' => 40000, 'hpp' => 36000],
            ['brand' => 'Mobile Legends', 'name' => 'Mobile Legends 257 Diamonds', 'sku' => 'g_ml_257', 'category' => 'GAME', 'price' => 60000, 'hpp' => 54000],
            ['brand' => 'Mobile Legends', 'name' => 'Mobile Legends 706 Diamonds', 'sku' => 'g_ml_706', 'category' => 'GAME', 'price' => 160000, 'hpp' => 145000],
            ['brand' => 'Free Fire', 'name' => 'Free Fire 70 Diamonds', 'sku' => 'g_ff_70', 'category' => 'GAME', 'price' => 10000, 'hpp' => 9000],
            ['brand' => 'Free Fire', 'name' => 'Free Fire 140 Diamonds', 'sku' => 'g_ff_140', 'category' => 'GAME', 'price' => 20000, 'hpp' => 18000],
            ['brand' => 'Free Fire', 'name' => 'Free Fire 355 Diamonds', 'sku' => 'g_ff_355', 'category' => 'GAME', 'price' => 50000, 'hpp' => 45000],
            ['brand' => 'Free Fire', 'name' => 'Free Fire 720 Diamonds', 'sku' => 'g_ff_720', 'category' => 'GAME', 'price' => 100000, 'hpp' => 90000],
            ['brand' => 'PUBG Mobile', 'name' => 'PUBG Mobile 52 UC', 'sku' => 'g_pubg_52', 'category' => 'GAME', 'price' => 10000, 'hpp' => 9000],
            ['brand' => 'PUBG Mobile', 'name' => 'PUBG Mobile 105 UC', 'sku' => 'g_pubg_105', 'category' => 'GAME', 'price' => 20000, 'hpp' => 18000],
            ['brand' => 'PUBG Mobile', 'name' => 'PUBG Mobile 263 UC', 'sku' => 'g_pubg_263', 'category' => 'GAME', 'price' => 50000, 'hpp' => 45000],
            ['brand' => 'PUBG Mobile', 'name' => 'PUBG Mobile 525 UC', 'sku' => 'g_pubg_525', 'category' => 'GAME', 'price' => 100000, 'hpp' => 90000],
            ['brand' => 'Genshin Impact', 'name' => 'Genshin Impact 60 Crystals', 'sku' => 'g_genshin_60', 'category' => 'GAME', 'price' => 16000, 'hpp' => 14000],
            ['brand' => 'Genshin Impact', 'name' => 'Genshin Impact 300 Crystals', 'sku' => 'g_genshin_300', 'category' => 'GAME', 'price' => 79000, 'hpp' => 72000],
            ['brand' => 'Genshin Impact', 'name' => 'Genshin Impact 980 Crystals', 'sku' => 'g_genshin_980', 'category' => 'GAME', 'price' => 249000, 'hpp' => 225000],
            ['brand' => 'BPJS Kesehatan', 'name' => 'BPJS Kesehatan Kelas 1', 'sku' => 't_bpjs_k1', 'category' => 'TAGIHAN', 'price' => 152500, 'hpp' => 150000],
            ['brand' => 'BPJS Kesehatan', 'name' => 'BPJS Kesehatan Kelas 2', 'sku' => 't_bpjs_k2', 'category' => 'TAGIHAN', 'price' => 102500, 'hpp' => 100000],
            ['brand' => 'BPJS Kesehatan', 'name' => 'BPJS Kesehatan Kelas 3', 'sku' => 't_bpjs_k3', 'category' => 'TAGIHAN', 'price' => 37500, 'hpp' => 35000],
            ['brand' => 'PDAM', 'name' => 'PDAM Rumah Tangga', 'sku' => 't_pdam_rt', 'category' => 'TAGIHAN', 'price' => 77500, 'hpp' => 75000],
            ['brand' => 'PDAM', 'name' => 'PDAM Bisnis / Komersial', 'sku' => 't_pdam_bs', 'category' => 'TAGIHAN', 'price' => 252500, 'hpp' => 250000],
            ['brand' => 'PLN Pascabayar', 'name' => 'PLN Pascabayar Listrik', 'sku' => 't_pln_pasca', 'category' => 'TAGIHAN', 'price' => 123000, 'hpp' => 120000],
            ['brand' => 'Indihome', 'name' => 'Telkom Indihome Internet', 'sku' => 't_indihome', 'category' => 'TAGIHAN', 'price' => 355000, 'hpp' => 350000],
            ['brand' => 'BCA', 'name' => 'Transfer Bank BCA 50.000', 'sku' => 'b_bca_50', 'category' => 'TRANSFER', 'price' => 52500, 'hpp' => 50000],
            ['brand' => 'BCA', 'name' => 'Transfer Bank BCA 100.000', 'sku' => 'b_bca_100', 'category' => 'TRANSFER', 'price' => 102500, 'hpp' => 100000],
            ['brand' => 'BCA', 'name' => 'Transfer Bank BCA 500.000', 'sku' => 'b_bca_500', 'category' => 'TRANSFER', 'price' => 502500, 'hpp' => 500000],
            ['brand' => 'BCA', 'name' => 'Transfer Bank BCA 1.000.000', 'sku' => 'b_bca_1M', 'category' => 'TRANSFER', 'price' => 1002500, 'hpp' => 1000000],
            ['brand' => 'Mandiri', 'name' => 'Transfer Bank Mandiri 50.000', 'sku' => 'b_mandiri_50', 'category' => 'TRANSFER', 'price' => 52500, 'hpp' => 50000],
            ['brand' => 'Mandiri', 'name' => 'Transfer Bank Mandiri 100.000', 'sku' => 'b_mandiri_100', 'category' => 'TRANSFER', 'price' => 102500, 'hpp' => 100000],
            ['brand' => 'Mandiri', 'name' => 'Transfer Bank Mandiri 500.000', 'sku' => 'b_mandiri_500', 'category' => 'TRANSFER', 'price' => 502500, 'hpp' => 500000],
            ['brand' => 'Mandiri', 'name' => 'Transfer Bank Mandiri 1.000.000', 'sku' => 'b_mandiri_1M', 'category' => 'TRANSFER', 'price' => 1002500, 'hpp' => 1000000],
            ['brand' => 'BNI', 'name' => 'Transfer Bank BNI 50.000', 'sku' => 'b_bni_50', 'category' => 'TRANSFER', 'price' => 52500, 'hpp' => 50000],
            ['brand' => 'BNI', 'name' => 'Transfer Bank BNI 100.000', 'sku' => 'b_bni_100', 'category' => 'TRANSFER', 'price' => 102500, 'hpp' => 100000],
            ['brand' => 'BNI', 'name' => 'Transfer Bank BNI 500.000', 'sku' => 'b_bni_500', 'category' => 'TRANSFER', 'price' => 502500, 'hpp' => 500000],
            ['brand' => 'BNI', 'name' => 'Transfer Bank BNI 1.000.000', 'sku' => 'b_bni_1M', 'category' => 'TRANSFER', 'price' => 1002500, 'hpp' => 1000000],
            ['brand' => 'BRI', 'name' => 'Transfer Bank BRI 50.000', 'sku' => 'b_bri_50', 'category' => 'TRANSFER', 'price' => 52500, 'hpp' => 50000],
            ['brand' => 'BRI', 'name' => 'Transfer Bank BRI 100.000', 'sku' => 'b_bri_100', 'category' => 'TRANSFER', 'price' => 102500, 'hpp' => 100000],
            ['brand' => 'BRI', 'name' => 'Transfer Bank BRI 500.000', 'sku' => 'b_bri_500', 'category' => 'TRANSFER', 'price' => 502500, 'hpp' => 500000],
            ['brand' => 'BRI', 'name' => 'Transfer Bank BRI 1.000.000', 'sku' => 'b_bri_1M', 'category' => 'TRANSFER', 'price' => 1002500, 'hpp' => 1000000],
            ['brand' => 'CIMB Niaga', 'name' => 'Transfer Bank CIMB Niaga 50.000', 'sku' => 'b_cimb_50', 'category' => 'TRANSFER', 'price' => 52500, 'hpp' => 50000],
            ['brand' => 'CIMB Niaga', 'name' => 'Transfer Bank CIMB Niaga 100.000', 'sku' => 'b_cimb_100', 'category' => 'TRANSFER', 'price' => 102500, 'hpp' => 100000],
            ['brand' => 'CIMB Niaga', 'name' => 'Transfer Bank CIMB Niaga 500.000', 'sku' => 'b_cimb_500', 'category' => 'TRANSFER', 'price' => 502500, 'hpp' => 500000],
            ['brand' => 'CIMB Niaga', 'name' => 'Transfer Bank CIMB Niaga 1.000.000', 'sku' => 'b_cimb_1M', 'category' => 'TRANSFER', 'price' => 1002500, 'hpp' => 1000000],
            ['brand' => 'PLN', 'name' => 'PLN Prabayar 20.000', 'sku' => 'd_pln_20', 'category' => 'VOUCHER', 'price' => 21500, 'hpp' => 20000],
            ['brand' => 'PLN', 'name' => 'PLN Prabayar 50.000', 'sku' => 'd_pln_50', 'category' => 'VOUCHER', 'price' => 51500, 'hpp' => 50000],
            ['brand' => 'PLN', 'name' => 'PLN Prabayar 100.000', 'sku' => 'd_pln_100', 'category' => 'VOUCHER', 'price' => 101500, 'hpp' => 100000],
            ['brand' => 'PLN', 'name' => 'PLN Prabayar 200.000', 'sku' => 'd_pln_200', 'category' => 'VOUCHER', 'price' => 201500, 'hpp' => 200000]
        ];

        $branches = Branch::all();
        foreach ($branches as $branch) {
            foreach ($digitalProducts as $dp) {
                // Prevent duplicate SKU for the same branch
                $exists = Product::where('sku', $dp['sku'])->where('branch_id', $branch->id)->exists();
                if (!$exists) {
                    Product::create([
                        'brand' => $dp['brand'],
                        'name' => $dp['name'],
                        'sku' => $dp['sku'],
                        'category' => $dp['category'],
                        'is_digital' => true,
                        'initial_stock' => null,
                        'incoming_stock' => null,
                        'final_stock' => null,
                        'sold_stock' => null,
                        'price' => $dp['price'],
                        'hpp' => $dp['hpp'],
                        'status' => null,
                        'branch_id' => $branch->id,
                    ]);
                }
            }
        }
    }
}
