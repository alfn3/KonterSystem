<?php

namespace Database\Seeders;

use App\Models\StockMovement;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class StockMovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        if (app()->environment('testing')) {
            Carbon::setTestNow('2026-06-18 12:00:00');
        }

        // 1. Reset all stock movements, transactions, and logs
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        StockMovement::truncate();
        TransactionItem::truncate();
        Transaction::truncate();
        
        if (class_exists('App\Models\Expense')) {
            \App\Models\Expense::truncate();
        }
        if (class_exists('App\Models\CashierReconciliation')) {
            \App\Models\CashierReconciliation::truncate();
        }
        if (class_exists('App\Models\AuditLog')) {
            \App\Models\AuditLog::truncate();
        }
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        // 2. Reset product stocks to their initial values to start tracking chronologically
        $products = Product::all();
        foreach ($products as $product) {
            if ($product->is_digital) {
                $product->incoming_stock = null;
                $product->sold_stock = null;
                $product->final_stock = null;
                $product->status = null;
            } else {
                $product->incoming_stock = 0;
                $product->sold_stock = 0;
                $product->final_stock = $product->initial_stock;
                $product->status = $product->final_stock <= 0 ? 'Habis' : ($product->final_stock <= 5 ? 'Kritis' : ($product->final_stock <= 10 ? 'Tipis' : 'Aman'));
            }
            $product->save();
        }

        // 3. Define branches and track balance
        $branches = Branch::all();
        $branchBalances = [];
        foreach ($branches as $b) {
            $branchBalances[$b->name] = (double)$b->saldo_elektrik;
        }

        // Let's seed unique activities for the last 10 days (from 9 days ago to 0 days ago / today)
        for ($daysAgo = 9; $daysAgo >= 0; $daysAgo--) {
            $date = Carbon::now()->subDays($daysAgo);
            $dateString = $date->toDateString();

            foreach ($branches as $branch) {
                // Fetch products for this branch
                $branchProducts = Product::where('branch_id', $branch->id)->get();
                if ($branchProducts->isEmpty()) {
                    continue;
                }

                if ($daysAgo === 0) {
                    if ($branch->name === 'mobil2') {
                        // Seed the 4 specific transactions required for the DashboardTest
                    
                    // Transaction 1: Sales/MOBIL-{date}/001
                    // Customer: Pelanggan 1 (phone: 0813-1111-1111)
                    // Items: ACC-IP14PM-SIL (Silicon Case iPhone 14 Pro Max Clear, 75k) and TRI-HAP-3 (Perdana Tri Happy 2GB + 1GB Chat, 20k)
                    // Payment: Tunai (Kembali: Rp 5.000)
                    $refNo1 = "Sales/" . strtoupper(substr($branch->name, 0, 5)) . '-' . $date->format('md') . "/001";
                    $createdTime1 = $date->copy()->setTime(23, 10, 0);
                    $custPhone1 = '0813-1111-1111';
                    $paymentMethod1 = 'Tunai';
                    $paymentChange1 = 'Kembali: Rp 5.000';
                    
                    $pSilicon = $branchProducts->firstWhere('sku', 'ACC-IP14PM-SIL');
                    $pTri = $branchProducts->firstWhere('sku', 'TRI-HAP-3');
                    
                    $totalTrx1 = ($pSilicon ? $pSilicon->price : 0) + ($pTri ? $pTri->price : 0);
                    
                    $trx1 = Transaction::create([
                        'id' => $refNo1,
                        'branch_id' => $branch->id,
                        'total_amount' => $totalTrx1,
                        'payment_method' => $paymentMethod1,
                        'cash_paid' => $totalTrx1 + 5000,
                        'change' => 5000,
                        'payment_change' => $paymentChange1,
                        'status' => 'Sukses',
                        'customer_id' => 'Pelanggan 1',
                        'customer_phone' => $custPhone1,
                        'operator' => 'Andini (Kasir)',
                        'saldo_elektrik_remaining' => $branchBalances[$branch->name],
                        'created_at' => $createdTime1,
                        'updated_at' => $createdTime1,
                    ]);

                    if ($pSilicon) {
                        TransactionItem::create([
                            'transaction_id' => $trx1->id,
                            'product_id' => $pSilicon->id,
                            'product_sku' => $pSilicon->sku,
                            'product_name' => $pSilicon->name,
                            'product_category' => $pSilicon->category,
                            'quantity' => 1,
                            'price' => $pSilicon->price,
                            'created_at' => $createdTime1,
                            'updated_at' => $createdTime1,
                        ]);

                        if (!$pSilicon->is_digital) {
                            $pSilicon->final_stock -= 1;
                            $pSilicon->sold_stock += 1;
                            $pSilicon->save();
                            
                            StockMovement::create([
                                'product_id' => $pSilicon->id,
                                'product_name' => $pSilicon->name,
                                'product_sku' => $pSilicon->sku,
                                'product_category' => $pSilicon->category,
                                'branch_name' => $branch->name,
                                'quantity_change' => -1,
                                'final_stock' => $pSilicon->final_stock,
                                'type' => 'Penjualan',
                                'customer_phone' => $custPhone1,
                                'customer_id' => 'Pelanggan 1',
                                'destination_number' => null,
                                'payment_method' => $paymentMethod1,
                                'payment_change' => $paymentChange1,
                                'reference_no' => $refNo1,
                                'operator' => 'Andini (Kasir)',
                                'saldo_elektrik_remaining' => $branchBalances[$branch->name],
                                'created_at' => $createdTime1,
                                'updated_at' => $createdTime1,
                            ]);
                        }
                    }
                    
                    if ($pTri) {
                        TransactionItem::create([
                            'transaction_id' => $trx1->id,
                            'product_id' => $pTri->id,
                            'product_sku' => $pTri->sku,
                            'product_name' => $pTri->name,
                            'product_category' => $pTri->category,
                            'quantity' => 1,
                            'price' => $pTri->price,
                            'created_at' => $createdTime1,
                            'updated_at' => $createdTime1,
                        ]);

                        if (!$pTri->is_digital) {
                            $pTri->final_stock -= 1;
                            $pTri->sold_stock += 1;
                            $pTri->save();
                            
                            StockMovement::create([
                                'product_id' => $pTri->id,
                                'product_name' => $pTri->name,
                                'product_sku' => $pTri->sku,
                                'product_category' => $pTri->category,
                                'branch_name' => $branch->name,
                                'quantity_change' => -1,
                                'final_stock' => $pTri->final_stock,
                                'type' => 'Penjualan',
                                'customer_phone' => $custPhone1,
                                'customer_id' => 'Pelanggan 1',
                                'destination_number' => null,
                                'payment_method' => $paymentMethod1,
                                'payment_change' => $paymentChange1,
                                'reference_no' => $refNo1,
                                'operator' => 'Andini (Kasir)',
                                'saldo_elektrik_remaining' => $branchBalances[$branch->name],
                                'created_at' => $createdTime1,
                                'updated_at' => $createdTime1,
                            ]);
                        }
                    }

                    // Transaction 2: Sales/MOBIL-{date}/002
                    // Customer: Pelanggan 2 (phone: 0813-2222-2222)
                    // Items: DIG-DANA-100K (Top Up Dana 100k, 102k)
                    // Destination: 0812-9876-5432
                    // Payment: QRIS (Pas)
                    $refNo2 = "Sales/" . strtoupper(substr($branch->name, 0, 5)) . '-' . $date->format('md') . "/002";
                    $createdTime2 = $date->copy()->setTime(23, 20, 0);
                    $custPhone2 = '0813-2222-2222';
                    $paymentMethod2 = 'QRIS';
                    $paymentChange2 = 'Pas';
                    
                    $pDana = $branchProducts->firstWhere('sku', 'DIG-DANA-100K');
                    if ($pDana) {
                        // Replenish if low
                        if ($branchBalances[$branch->name] < 1000000) {
                            $branchBalances[$branch->name] += 10000000;
                        }
                        $branchBalances[$branch->name] -= $pDana->hpp; // Deduct balance

                        $trx2 = Transaction::create([
                            'id' => $refNo2,
                            'branch_id' => $branch->id,
                            'total_amount' => $pDana->price,
                            'payment_method' => $paymentMethod2,
                            'cash_paid' => $pDana->price,
                            'change' => 0,
                            'payment_change' => $paymentChange2,
                            'status' => 'Sukses',
                            'customer_id' => 'Pelanggan 2',
                            'customer_phone' => $custPhone2,
                            'operator' => 'Andini (Kasir)',
                            'saldo_elektrik_remaining' => $branchBalances[$branch->name],
                            'created_at' => $createdTime2,
                            'updated_at' => $createdTime2,
                        ]);

                        TransactionItem::create([
                            'transaction_id' => $trx2->id,
                            'product_id' => $pDana->id,
                            'product_sku' => $pDana->sku,
                            'product_name' => $pDana->name,
                            'product_category' => $pDana->category,
                            'quantity' => 1,
                            'price' => $pDana->price,
                            'destination_number' => '0812-9876-5432',
                            'created_at' => $createdTime2,
                            'updated_at' => $createdTime2,
                        ]);

                        if (!$pDana->is_digital) {
                            $pDana->final_stock -= 1;
                            $pDana->sold_stock += 1;
                            $pDana->save();

                            StockMovement::create([
                                'product_id' => $pDana->id,
                                'product_name' => $pDana->name,
                                'product_sku' => $pDana->sku,
                                'product_category' => $pDana->category,
                                'branch_name' => $branch->name,
                                'quantity_change' => -1,
                                'final_stock' => $pDana->final_stock,
                                'type' => 'Penjualan',
                                'customer_phone' => $custPhone2,
                                'customer_id' => 'Pelanggan 2',
                                'destination_number' => '0812-9876-5432',
                                'payment_method' => $paymentMethod2,
                                'payment_change' => $paymentChange2,
                                'reference_no' => $refNo2,
                                'operator' => 'Andini (Kasir)',
                                'saldo_elektrik_remaining' => $branchBalances[$branch->name],
                                'created_at' => $createdTime2,
                                'updated_at' => $createdTime2,
                            ]);
                        }
                    }

                    // Transaction 3: Sales/MOBIL-{date}/003
                    // Customer: Pelanggan 3 (phone: 0813-3333-3333)
                    // Items: XL-COM-12 (Voucher XL Combo Flex 12GB, 45k) and IND-FRE-3 (Voucher Indosat Freedom 3GB, 15k)
                    // Payment: Tunai (Kurang: Rp 2.000)
                    $refNo3 = "Sales/" . strtoupper(substr($branch->name, 0, 5)) . '-' . $date->format('md') . "/003";
                    $createdTime3 = $date->copy()->setTime(23, 30, 0);
                    $custPhone3 = '0813-3333-3333';
                    $paymentMethod3 = 'Tunai';
                    $paymentChange3 = 'Kurang: Rp 2.000';
                    
                    $pXl = $branchProducts->firstWhere('sku', 'XL-COM-12');
                    $pIndosat = $branchProducts->firstWhere('sku', 'IND-FRE-3');
                    
                    $totalTrx3 = ($pXl ? $pXl->price : 0) + ($pIndosat ? $pIndosat->price : 0);
                    
                    $trx3 = Transaction::create([
                        'id' => $refNo3,
                        'branch_id' => $branch->id,
                        'total_amount' => $totalTrx3,
                        'payment_method' => $paymentMethod3,
                        'cash_paid' => $totalTrx3 - 2000,
                        'change' => 0,
                        'payment_change' => $paymentChange3,
                        'status' => 'Sukses',
                        'customer_id' => 'Pelanggan 3',
                        'customer_phone' => $custPhone3,
                        'operator' => 'Andini (Kasir)',
                        'saldo_elektrik_remaining' => $branchBalances[$branch->name],
                        'created_at' => $createdTime3,
                        'updated_at' => $createdTime3,
                    ]);

                    if ($pXl) {
                        TransactionItem::create([
                            'transaction_id' => $trx3->id,
                            'product_id' => $pXl->id,
                            'product_sku' => $pXl->sku,
                            'product_name' => $pXl->name,
                            'product_category' => $pXl->category,
                            'quantity' => 1,
                            'price' => $pXl->price,
                            'created_at' => $createdTime3,
                            'updated_at' => $createdTime3,
                        ]);

                        if (!$pXl->is_digital) {
                            $pXl->final_stock -= 1;
                            $pXl->sold_stock += 1;
                            $pXl->save();
                            
                            StockMovement::create([
                                'product_id' => $pXl->id,
                                'product_name' => $pXl->name,
                                'product_sku' => $pXl->sku,
                                'product_category' => $pXl->category,
                                'branch_name' => $branch->name,
                                'quantity_change' => -1,
                                'final_stock' => $pXl->final_stock,
                                'type' => 'Penjualan',
                                'customer_phone' => $custPhone3,
                                'customer_id' => 'Pelanggan 3',
                                'destination_number' => null,
                                'payment_method' => $paymentMethod3,
                                'payment_change' => $paymentChange3,
                                'reference_no' => $refNo3,
                                'operator' => 'Andini (Kasir)',
                                'saldo_elektrik_remaining' => $branchBalances[$branch->name],
                                'created_at' => $createdTime3,
                                'updated_at' => $createdTime3,
                            ]);
                        }
                    }
                    
                    if ($pIndosat) {
                        TransactionItem::create([
                            'transaction_id' => $trx3->id,
                            'product_id' => $pIndosat->id,
                            'product_sku' => $pIndosat->sku,
                            'product_name' => $pIndosat->name,
                            'product_category' => $pIndosat->category,
                            'quantity' => 1,
                            'price' => $pIndosat->price,
                            'created_at' => $createdTime3,
                            'updated_at' => $createdTime3,
                        ]);

                        if (!$pIndosat->is_digital) {
                            $pIndosat->final_stock -= 1;
                            $pIndosat->sold_stock += 1;
                            $pIndosat->save();
                            
                            StockMovement::create([
                                'product_id' => $pIndosat->id,
                                'product_name' => $pIndosat->name,
                                'product_sku' => $pIndosat->sku,
                                'product_category' => $pIndosat->category,
                                'branch_name' => $branch->name,
                                'quantity_change' => -1,
                                'final_stock' => $pIndosat->final_stock,
                                'type' => 'Penjualan',
                                'customer_phone' => $custPhone3,
                                'customer_id' => 'Pelanggan 3',
                                'destination_number' => null,
                                'payment_method' => $paymentMethod3,
                                'payment_change' => $paymentChange3,
                                'reference_no' => $refNo3,
                                'operator' => 'Andini (Kasir)',
                                'saldo_elektrik_remaining' => $branchBalances[$branch->name],
                                'created_at' => $createdTime3,
                                'updated_at' => $createdTime3,
                            ]);
                        }
                    }

                    // Transaction 4: Sales/MOBIL-{date}/004
                    // Customer: Pelanggan 4 (phone: 0813-4444-4444)
                    // Items: IND-FRE-3 (Voucher Indosat Freedom 3GB, 15k) and DIG-PULSA-30K (Pulsa XL 30k, 33k)
                    // Payment: Tunai (Kembali: Rp 2.000)
                    $refNo4 = "Sales/" . strtoupper(substr($branch->name, 0, 5)) . '-' . $date->format('md') . "/004";
                    $createdTime4 = $date->copy()->setTime(23, 40, 0);
                    $custPhone4 = '0813-4444-4444';
                    $paymentMethod4 = 'Tunai';
                    $paymentChange4 = 'Kembali: Rp 2.000';
                    
                    $pPulsa = $branchProducts->firstWhere('sku', 'DIG-PULSA-30K');
                    
                    $totalTrx4 = ($pIndosat ? $pIndosat->price : 0) + ($pPulsa ? $pPulsa->price : 0);
                    
                    if ($pPulsa) {
                        // Replenish if low
                        if ($branchBalances[$branch->name] < 1000000) {
                            $branchBalances[$branch->name] += 10000000;
                        }
                        $branchBalances[$branch->name] -= $pPulsa->hpp; // Deduct balance
                    }

                    $trx4 = Transaction::create([
                        'id' => $refNo4,
                        'branch_id' => $branch->id,
                        'total_amount' => $totalTrx4,
                        'payment_method' => $paymentMethod4,
                        'cash_paid' => $totalTrx4 + 2000,
                        'change' => 2000,
                        'payment_change' => $paymentChange4,
                        'status' => 'Sukses',
                        'customer_id' => 'Pelanggan 4',
                        'customer_phone' => $custPhone4,
                        'operator' => 'Andini (Kasir)',
                        'saldo_elektrik_remaining' => $branchBalances[$branch->name],
                        'created_at' => $createdTime4,
                        'updated_at' => $createdTime4,
                    ]);

                    if ($pIndosat) {
                        TransactionItem::create([
                            'transaction_id' => $trx4->id,
                            'product_id' => $pIndosat->id,
                            'product_sku' => $pIndosat->sku,
                            'product_name' => $pIndosat->name,
                            'product_category' => $pIndosat->category,
                            'quantity' => 1,
                            'price' => $pIndosat->price,
                            'created_at' => $createdTime4,
                            'updated_at' => $createdTime4,
                        ]);

                        if (!$pIndosat->is_digital) {
                            $pIndosat->final_stock -= 1;
                            $pIndosat->sold_stock += 1;
                            $pIndosat->save();
                            
                            StockMovement::create([
                                'product_id' => $pIndosat->id,
                                'product_name' => $pIndosat->name,
                                'product_sku' => $pIndosat->sku,
                                'product_category' => $pIndosat->category,
                                'branch_name' => $branch->name,
                                'quantity_change' => -1,
                                'final_stock' => $pIndosat->final_stock,
                                'type' => 'Penjualan',
                                'customer_phone' => $custPhone4,
                                'customer_id' => 'Pelanggan 4',
                                'destination_number' => null,
                                'payment_method' => $paymentMethod4,
                                'payment_change' => $paymentChange4,
                                'reference_no' => $refNo4,
                                'operator' => 'Andini (Kasir)',
                                'saldo_elektrik_remaining' => $branchBalances[$branch->name],
                                'created_at' => $createdTime4,
                                'updated_at' => $createdTime4,
                            ]);
                        }
                    }
                    
                    if ($pPulsa) {
                        TransactionItem::create([
                            'transaction_id' => $trx4->id,
                            'product_id' => $pPulsa->id,
                            'product_sku' => $pPulsa->sku,
                            'product_name' => $pPulsa->name,
                            'product_category' => $pPulsa->category,
                            'quantity' => 1,
                            'price' => $pPulsa->price,
                            'created_at' => $createdTime4,
                            'updated_at' => $createdTime4,
                        ]);

                        if (!$pPulsa->is_digital) {
                            $pPulsa->final_stock -= 1;
                            $pPulsa->sold_stock += 1;
                            $pPulsa->save();
                            
                            StockMovement::create([
                                'product_id' => $pPulsa->id,
                                'product_name' => $pPulsa->name,
                                'product_sku' => $pPulsa->sku,
                                'product_category' => $pPulsa->category,
                                'branch_name' => $branch->name,
                                'quantity_change' => -1,
                                'final_stock' => $pPulsa->final_stock,
                                'type' => 'Penjualan',
                                'customer_phone' => $custPhone4,
                                'customer_id' => 'Pelanggan 4',
                                'destination_number' => null,
                                'payment_method' => $paymentMethod4,
                                'payment_change' => $paymentChange4,
                                'reference_no' => $refNo4,
                                'operator' => 'Andini (Kasir)',
                                'saldo_elektrik_remaining' => $branchBalances[$branch->name],
                                'created_at' => $createdTime4,
                                'updated_at' => $createdTime4,
                            ]);
                        }
                    }
                }
                continue;
            }

                if ($branch->name === 'mobil1' && ($dateString === '2026-06-09' || $dateString === '2026-06-10')) {
                    if ($dateString === '2026-06-09') {
                        // Transactions for June 9, 2026

                        // Transaction 1: SF-UNL-7D (1) + IND-FRE-3 (2)
                        $refNo1 = "Sales/" . strtoupper(substr($branch->name, 0, 5)) . '-' . $date->format('md') . "/001";
                        $createdTime1 = $date->copy()->setTime(10, 15, 0);
                        $custPhone1 = '0812-3456-7890';
                        $paymentMethod1 = 'Tunai';
                        $paymentChange1 = 'Kembali: Rp 45.000';

                        $pSmartfren = $branchProducts->firstWhere('sku', 'SF-UNL-7D');
                        $pIndosat = $branchProducts->firstWhere('sku', 'IND-FRE-3');

                        $totalTrx1 = ($pSmartfren ? $pSmartfren->price : 0) + (($pIndosat ? $pIndosat->price : 0) * 2);

                        $trx1 = Transaction::create([
                            'id' => $refNo1,
                            'branch_id' => $branch->id,
                            'total_amount' => $totalTrx1,
                            'payment_method' => $paymentMethod1,
                            'cash_paid' => 100000,
                            'change' => 45000,
                            'payment_change' => $paymentChange1,
                            'status' => 'Sukses',
                            'customer_id' => 'Pelanggan 1',
                            'customer_phone' => $custPhone1,
                            'operator' => 'Andini (Kasir)',
                            'saldo_elektrik_remaining' => $branchBalances[$branch->name],
                            'created_at' => $createdTime1,
                            'updated_at' => $createdTime1,
                        ]);

                        if ($pSmartfren) {
                            TransactionItem::create([
                                'transaction_id' => $trx1->id,
                                'product_id' => $pSmartfren->id,
                                'product_sku' => $pSmartfren->sku,
                                'product_name' => $pSmartfren->name,
                                'product_category' => $pSmartfren->category,
                                'quantity' => 1,
                                'price' => $pSmartfren->price,
                                'created_at' => $createdTime1,
                                'updated_at' => $createdTime1,
                            ]);

                            $pSmartfren->final_stock -= 1;
                            $pSmartfren->sold_stock += 1;
                            $pSmartfren->save();

                            StockMovement::create([
                                'product_id' => $pSmartfren->id,
                                'product_name' => $pSmartfren->name,
                                'product_sku' => $pSmartfren->sku,
                                'product_category' => $pSmartfren->category,
                                'branch_name' => $branch->name,
                                'quantity_change' => -1,
                                'final_stock' => $pSmartfren->final_stock,
                                'type' => 'Penjualan',
                                'customer_phone' => $custPhone1,
                                'customer_id' => 'Pelanggan 1',
                                'destination_number' => null,
                                'payment_method' => $paymentMethod1,
                                'payment_change' => $paymentChange1,
                                'reference_no' => $refNo1,
                                'operator' => 'Andini (Kasir)',
                                'saldo_elektrik_remaining' => $branchBalances[$branch->name],
                                'created_at' => $createdTime1,
                                'updated_at' => $createdTime1,
                            ]);
                        }

                        if ($pIndosat) {
                            TransactionItem::create([
                                'transaction_id' => $trx1->id,
                                'product_id' => $pIndosat->id,
                                'product_sku' => $pIndosat->sku,
                                'product_name' => $pIndosat->name,
                                'product_category' => $pIndosat->category,
                                'quantity' => 2,
                                'price' => $pIndosat->price,
                                'created_at' => $createdTime1,
                                'updated_at' => $createdTime1,
                            ]);

                            $pIndosat->final_stock -= 2;
                            $pIndosat->sold_stock += 2;
                            $pIndosat->save();

                            StockMovement::create([
                                'product_id' => $pIndosat->id,
                                'product_name' => $pIndosat->name,
                                'product_sku' => $pIndosat->sku,
                                'product_category' => $pIndosat->category,
                                'branch_name' => $branch->name,
                                'quantity_change' => -2,
                                'final_stock' => $pIndosat->final_stock,
                                'type' => 'Penjualan',
                                'customer_phone' => $custPhone1,
                                'customer_id' => 'Pelanggan 1',
                                'destination_number' => null,
                                'payment_method' => $paymentMethod1,
                                'payment_change' => $paymentChange1,
                                'reference_no' => $refNo1,
                                'operator' => 'Andini (Kasir)',
                                'saldo_elektrik_remaining' => $branchBalances[$branch->name],
                                'created_at' => $createdTime1,
                                'updated_at' => $createdTime1,
                            ]);
                        }

                        // Transaction 2: w_dana_100 (1) - Digital
                        $refNo2 = "Sales/" . strtoupper(substr($branch->name, 0, 5)) . '-' . $date->format('md') . "/002";
                        $createdTime2 = $date->copy()->setTime(14, 30, 0);
                        $custPhone2 = '0878-1122-3344';
                        $paymentMethod2 = 'QRIS';
                        $paymentChange2 = 'Pas';

                        $pDana = $branchProducts->firstWhere('sku', 'w_dana_100');
                        if ($pDana) {
                            if ($branchBalances[$branch->name] < 1000000) {
                                $branchBalances[$branch->name] += 10000000;
                            }
                            $branchBalances[$branch->name] -= $pDana->hpp;

                            $trx2 = Transaction::create([
                                'id' => $refNo2,
                                'branch_id' => $branch->id,
                                'total_amount' => $pDana->price,
                                'payment_method' => $paymentMethod2,
                                'cash_paid' => $pDana->price,
                                'change' => 0,
                                'payment_change' => $paymentChange2,
                                'status' => 'Sukses',
                                'customer_id' => 'Pelanggan 2',
                                'customer_phone' => $custPhone2,
                                'operator' => 'Andini (Kasir)',
                                'saldo_elektrik_remaining' => $branchBalances[$branch->name],
                                'created_at' => $createdTime2,
                                'updated_at' => $createdTime2,
                            ]);

                            TransactionItem::create([
                                'transaction_id' => $trx2->id,
                                'product_id' => $pDana->id,
                                'product_sku' => $pDana->sku,
                                'product_name' => $pDana->name,
                                'product_category' => $pDana->category,
                                'quantity' => 1,
                                'price' => $pDana->price,
                                'destination_number' => '0812-3456-7890',
                                'created_at' => $createdTime2,
                                'updated_at' => $createdTime2,
                            ]);
                        }

                        // Transaction 3: ACC-ORI-TYPC-BK (1)
                        $refNo3 = "Sales/" . strtoupper(substr($branch->name, 0, 5)) . '-' . $date->format('md') . "/003";
                        $createdTime3 = $date->copy()->setTime(18, 45, 0);
                        $custPhone3 = '0896-9988-7766';
                        $paymentMethod3 = 'Tunai';
                        $paymentChange3 = 'Pas';

                        $pOrico = $branchProducts->firstWhere('sku', 'ACC-ORI-TYPC-BK');
                        if ($pOrico) {
                            $trx3 = Transaction::create([
                                'id' => $refNo3,
                                'branch_id' => $branch->id,
                                'total_amount' => $pOrico->price,
                                'payment_method' => $paymentMethod3,
                                'cash_paid' => $pOrico->price,
                                'change' => 0,
                                'payment_change' => $paymentChange3,
                                'status' => 'Sukses',
                                'customer_id' => 'Pelanggan 3',
                                'customer_phone' => $custPhone3,
                                'operator' => 'Andini (Kasir)',
                                'saldo_elektrik_remaining' => $branchBalances[$branch->name],
                                'created_at' => $createdTime3,
                                'updated_at' => $createdTime3,
                            ]);

                            TransactionItem::create([
                                'transaction_id' => $trx3->id,
                                'product_id' => $pOrico->id,
                                'product_sku' => $pOrico->sku,
                                'product_name' => $pOrico->name,
                                'product_category' => $pOrico->category,
                                'quantity' => 1,
                                'price' => $pOrico->price,
                                'created_at' => $createdTime3,
                                'updated_at' => $createdTime3,
                            ]);

                            $pOrico->final_stock -= 1;
                            $pOrico->sold_stock += 1;
                            $pOrico->save();

                            StockMovement::create([
                                'product_id' => $pOrico->id,
                                'product_name' => $pOrico->name,
                                'product_sku' => $pOrico->sku,
                                'product_category' => $pOrico->category,
                                'branch_name' => $branch->name,
                                'quantity_change' => -1,
                                'final_stock' => $pOrico->final_stock,
                                'type' => 'Penjualan',
                                'customer_phone' => $custPhone3,
                                'customer_id' => 'Pelanggan 3',
                                'destination_number' => null,
                                'payment_method' => $paymentMethod3,
                                'payment_change' => $paymentChange3,
                                'reference_no' => $refNo3,
                                'operator' => 'Andini (Kasir)',
                                'saldo_elektrik_remaining' => $branchBalances[$branch->name],
                                'created_at' => $createdTime3,
                                'updated_at' => $createdTime3,
                            ]);
                        }
                    } else {
                        // Transactions for June 10, 2026

                        // Transaction 1: TS-SIM-10-JKT (1) + XL-COM-12 (1)
                        $refNo1 = "Sales/" . strtoupper(substr($branch->name, 0, 5)) . '-' . $date->format('md') . "/001";
                        $createdTime1 = $date->copy()->setTime(9, 30, 0);
                        $custPhone1 = '0813-9876-5432';
                        $paymentMethod1 = 'Tunai';
                        $paymentChange1 = 'Kembali: Rp 20.000';

                        $pTelkomsel = $branchProducts->firstWhere('sku', 'TS-SIM-10-JKT');
                        $pXl = $branchProducts->firstWhere('sku', 'XL-COM-12');

                        $totalTrx1 = ($pTelkomsel ? $pTelkomsel->price : 0) + ($pXl ? $pXl->price : 0);

                        $trx1 = Transaction::create([
                            'id' => $refNo1,
                            'branch_id' => $branch->id,
                            'total_amount' => $totalTrx1,
                            'payment_method' => $paymentMethod1,
                            'cash_paid' => 100000,
                            'change' => 20000,
                            'payment_change' => $paymentChange1,
                            'status' => 'Sukses',
                            'customer_id' => 'Pelanggan 1',
                            'customer_phone' => $custPhone1,
                            'operator' => 'Andini (Kasir)',
                            'saldo_elektrik_remaining' => $branchBalances[$branch->name],
                            'created_at' => $createdTime1,
                            'updated_at' => $createdTime1,
                        ]);

                        if ($pTelkomsel) {
                            TransactionItem::create([
                                'transaction_id' => $trx1->id,
                                'product_id' => $pTelkomsel->id,
                                'product_sku' => $pTelkomsel->sku,
                                'product_name' => $pTelkomsel->name,
                                'product_category' => $pTelkomsel->category,
                                'quantity' => 1,
                                'price' => $pTelkomsel->price,
                                'created_at' => $createdTime1,
                                'updated_at' => $createdTime1,
                            ]);

                            $pTelkomsel->final_stock -= 1;
                            $pTelkomsel->sold_stock += 1;
                            $pTelkomsel->save();

                            StockMovement::create([
                                'product_id' => $pTelkomsel->id,
                                'product_name' => $pTelkomsel->name,
                                'product_sku' => $pTelkomsel->sku,
                                'product_category' => $pTelkomsel->category,
                                'branch_name' => $branch->name,
                                'quantity_change' => -1,
                                'final_stock' => $pTelkomsel->final_stock,
                                'type' => 'Penjualan',
                                'customer_phone' => $custPhone1,
                                'customer_id' => 'Pelanggan 1',
                                'destination_number' => null,
                                'payment_method' => $paymentMethod1,
                                'payment_change' => $paymentChange1,
                                'reference_no' => $refNo1,
                                'operator' => 'Andini (Kasir)',
                                'saldo_elektrik_remaining' => $branchBalances[$branch->name],
                                'created_at' => $createdTime1,
                                'updated_at' => $createdTime1,
                            ]);
                        }

                        if ($pXl) {
                            TransactionItem::create([
                                'transaction_id' => $trx1->id,
                                'product_id' => $pXl->id,
                                'product_sku' => $pXl->sku,
                                'product_name' => $pXl->name,
                                'product_category' => $pXl->category,
                                'quantity' => 1,
                                'price' => $pXl->price,
                                'created_at' => $createdTime1,
                                'updated_at' => $createdTime1,
                            ]);

                            $pXl->final_stock -= 1;
                            $pXl->sold_stock += 1;
                            $pXl->save();

                            StockMovement::create([
                                'product_id' => $pXl->id,
                                'product_name' => $pXl->name,
                                'product_sku' => $pXl->sku,
                                'product_category' => $pXl->category,
                                'branch_name' => $branch->name,
                                'quantity_change' => -1,
                                'final_stock' => $pXl->final_stock,
                                'type' => 'Penjualan',
                                'customer_phone' => $custPhone1,
                                'customer_id' => 'Pelanggan 1',
                                'destination_number' => null,
                                'payment_method' => $paymentMethod1,
                                'payment_change' => $paymentChange1,
                                'reference_no' => $refNo1,
                                'operator' => 'Andini (Kasir)',
                                'saldo_elektrik_remaining' => $branchBalances[$branch->name],
                                'created_at' => $createdTime1,
                                'updated_at' => $createdTime1,
                            ]);
                        }

                        // Transaction 2: p_tsel_50 (1) - Digital
                        $refNo2 = "Sales/" . strtoupper(substr($branch->name, 0, 5)) . '-' . $date->format('md') . "/002";
                        $createdTime2 = $date->copy()->setTime(13, 15, 0);
                        $custPhone2 = '0852-5555-6666';
                        $paymentMethod2 = 'QRIS';
                        $paymentChange2 = 'Pas';

                        $pTselPulsa = $branchProducts->firstWhere('sku', 'p_tsel_50');
                        if ($pTselPulsa) {
                            if ($branchBalances[$branch->name] < 1000000) {
                                $branchBalances[$branch->name] += 10000000;
                            }
                            $branchBalances[$branch->name] -= $pTselPulsa->hpp;

                            $trx2 = Transaction::create([
                                'id' => $refNo2,
                                'branch_id' => $branch->id,
                                'total_amount' => $pTselPulsa->price,
                                'payment_method' => $paymentMethod2,
                                'cash_paid' => $pTselPulsa->price,
                                'change' => 0,
                                'payment_change' => $paymentChange2,
                                'status' => 'Sukses',
                                'customer_id' => 'Pelanggan 2',
                                'customer_phone' => $custPhone2,
                                'operator' => 'Andini (Kasir)',
                                'saldo_elektrik_remaining' => $branchBalances[$branch->name],
                                'created_at' => $createdTime2,
                                'updated_at' => $createdTime2,
                            ]);

                            TransactionItem::create([
                                'transaction_id' => $trx2->id,
                                'product_id' => $pTselPulsa->id,
                                'product_sku' => $pTselPulsa->sku,
                                'product_name' => $pTselPulsa->name,
                                'product_category' => $pTselPulsa->category,
                                'quantity' => 1,
                                'price' => $pTselPulsa->price,
                                'destination_number' => '0813-1111-2222',
                                'created_at' => $createdTime2,
                                'updated_at' => $createdTime2,
                            ]);
                        }

                        // Transaction 3: ACC-IP14PM-SIL (1)
                        $refNo3 = "Sales/" . strtoupper(substr($branch->name, 0, 5)) . '-' . $date->format('md') . "/003";
                        $createdTime3 = $date->copy()->setTime(16, 40, 0);
                        $custPhone3 = '0857-4444-3333';
                        $paymentMethod3 = 'Tunai';
                        $paymentChange3 = 'Kembali: Rp 5.000';

                        $pSiliconClear = $branchProducts->firstWhere('sku', 'ACC-IP14PM-SIL');
                        if ($pSiliconClear) {
                            $trx3 = Transaction::create([
                                'id' => $refNo3,
                                'branch_id' => $branch->id,
                                'total_amount' => $pSiliconClear->price,
                                'payment_method' => $paymentMethod3,
                                'cash_paid' => 80000,
                                'change' => 5000,
                                'payment_change' => $paymentChange3,
                                'status' => 'Sukses',
                                'customer_id' => 'Pelanggan 3',
                                'customer_phone' => $custPhone3,
                                'operator' => 'Andini (Kasir)',
                                'saldo_elektrik_remaining' => $branchBalances[$branch->name],
                                'created_at' => $createdTime3,
                                'updated_at' => $createdTime3,
                            ]);

                            TransactionItem::create([
                                'transaction_id' => $trx3->id,
                                'product_id' => $pSiliconClear->id,
                                'product_sku' => $pSiliconClear->sku,
                                'product_name' => $pSiliconClear->name,
                                'product_category' => $pSiliconClear->category,
                                'quantity' => 1,
                                'price' => $pSiliconClear->price,
                                'created_at' => $createdTime3,
                                'updated_at' => $createdTime3,
                            ]);

                            $pSiliconClear->final_stock -= 1;
                            $pSiliconClear->sold_stock += 1;
                            $pSiliconClear->save();

                            StockMovement::create([
                                'product_id' => $pSiliconClear->id,
                                'product_name' => $pSiliconClear->name,
                                'product_sku' => $pSiliconClear->sku,
                                'product_category' => $pSiliconClear->category,
                                'branch_name' => $branch->name,
                                'quantity_change' => -1,
                                'final_stock' => $pSiliconClear->final_stock,
                                'type' => 'Penjualan',
                                'customer_phone' => $custPhone3,
                                'customer_id' => 'Pelanggan 3',
                                'destination_number' => null,
                                'payment_method' => $paymentMethod3,
                                'payment_change' => $paymentChange3,
                                'reference_no' => $refNo3,
                                'operator' => 'Andini (Kasir)',
                                'saldo_elektrik_remaining' => $branchBalances[$branch->name],
                                'created_at' => $createdTime3,
                                'updated_at' => $createdTime3,
                            ]);
                        }
                    }
                    continue;
                }

                // Generate a random number of transactions for this branch on this day (e.g. 2 to 4 transactions)
                // Use a deterministic seed based on date and branch ID to make seeding reproducible but unique per day
                $seedVal = crc32($dateString . $branch->name);
                mt_srand($seedVal);

                $numTransactions = mt_rand(2, 4);

                for ($trxIndex = 1; $trxIndex <= $numTransactions; $trxIndex++) {
                    // Decide if this is a Restok (15% chance) or Penjualan (85% chance)
                    $isRestok = (mt_rand(1, 100) <= 15);

                    $refCode = strtoupper($branch->name) . '-' . $date->format('md');
                    $timeHour = mt_rand(8, 20);
                    $timeMin = mt_rand(0, 59);
                    $timeSec = mt_rand(0, 59);
                    $createdTime = $date->copy()->setTime($timeHour, $timeMin, $timeSec);

                    if ($isRestok) {
                        // Choose a random physical product for Restok
                        $physicalProducts = $branchProducts->where('is_digital', false);
                        if ($physicalProducts->isEmpty()) {
                            continue;
                        }
                        
                        $product = $physicalProducts->random();
                        $qty = mt_rand(10, 50);

                        $product->final_stock += $qty;
                        $product->incoming_stock += $qty;
                        $product->save();

                        StockMovement::create([
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'product_sku' => $product->sku,
                            'product_category' => $product->category,
                            'branch_name' => $branch->name,
                            'quantity_change' => $qty,
                            'final_stock' => $product->final_stock,
                            'type' => 'Restok',
                            'reference_no' => "Restock/{$refCode}/" . sprintf('%02d', $trxIndex),
                            'operator' => 'Budi (Admin)',
                            'saldo_elektrik_remaining' => $branchBalances[$branch->name],
                            'created_at' => $createdTime,
                            'updated_at' => $createdTime,
                        ]);
                    } else {
                        // Penjualan transaction
                        // Decide number of items in this transaction (1 to 3 items)
                        $numItems = mt_rand(1, 3);
                        $refNo = "Sales/{$refCode}/" . sprintf('%03d', $trxIndex);
                        
                        // Generate unique customer phone for this transaction
                        $phonePrefix = ['0812', '0813', '0821', '0852', '0857', '0878', '0896'];
                        $custPhone = $phonePrefix[mt_rand(0, count($phonePrefix) - 1)] . '-' . mt_rand(1000, 9999) . '-' . mt_rand(1000, 9999);
                        
                        $paymentMethod = (mt_rand(1, 100) <= 60) ? 'Tunai' : 'QRIS';

                        // Select unique products for this transaction
                        $selectedProducts = $branchProducts->shuffle()->take($numItems);

                        // Calculate total amount for change calculation
                        $totalAmount = 0;
                        $digitalAmount = 0;
                        $itemsData = [];

                        foreach ($selectedProducts as $product) {
                            // Ensure there is enough stock, otherwise skip or do a correction first
                            if (!$product->is_digital && $product->final_stock <= 2) {
                                // Add 20 stock via restock/correction before selling
                                $product->final_stock += 20;
                                $product->incoming_stock += 20;
                                $product->save();

                                StockMovement::create([
                                    'product_id' => $product->id,
                                    'product_name' => $product->name,
                                    'product_sku' => $product->sku,
                                    'product_category' => $product->category,
                                    'branch_name' => $branch->name,
                                    'quantity_change' => 20,
                                    'final_stock' => $product->final_stock,
                                    'type' => 'Restok',
                                    'reference_no' => "Restock-Auto/{$refCode}/" . sprintf('%02d', $trxIndex),
                                    'operator' => 'System (Auto)',
                                    'saldo_elektrik_remaining' => $branchBalances[$branch->name],
                                    'created_at' => $createdTime->copy()->subMinutes(5),
                                    'updated_at' => $createdTime->copy()->subMinutes(5),
                                ]);
                            }

                            $qty = mt_rand(1, 2);
                            $totalAmount += $product->price * $qty;
                            if ($product->is_digital) {
                                $digitalAmount += $product->hpp * $qty;
                            }
                            $itemsData[] = ['product' => $product, 'qty' => $qty];
                        }

                        // Replenish if low
                        if ($branchBalances[$branch->name] < 1000000) {
                            $branchBalances[$branch->name] += 10000000;
                        }
                        // Deduct digital sales from balance
                        $branchBalances[$branch->name] -= $digitalAmount;

                        // Determine change status
                        $paymentChange = 'Pas';
                        $cashPaid = $totalAmount;
                        $changeVal = 0;

                        if ($paymentMethod === 'Tunai') {
                            if ($totalAmount <= 10000) {
                                $paid = 10000;
                            } elseif ($totalAmount <= 20000) {
                                $paid = 20000;
                            } elseif ($totalAmount <= 50000) {
                                $paid = 50000;
                            } elseif ($totalAmount <= 100000) {
                                $paid = 100000;
                            } else {
                                $paid = ceil($totalAmount / 50000) * 50000;
                            }
                            $diff = $paid - $totalAmount;
                            $cashPaid = $paid;
                            $changeVal = $diff;
                            
                            // Occasionally simulate underpayment
                            if (mt_rand(1, 100) <= 5) {
                                $paymentChange = 'Kurang: Rp 2.000';
                                $cashPaid = $totalAmount - 2000;
                                $changeVal = 0;
                            } elseif ($diff > 0) {
                                $paymentChange = 'Kembali: Rp ' . number_format($diff, 0, ',', '.');
                            }
                        }

                        // Create transaction
                        $trx = Transaction::create([
                            'id' => $refNo,
                            'branch_id' => $branch->id,
                            'total_amount' => $totalAmount,
                            'payment_method' => $paymentMethod,
                            'cash_paid' => $cashPaid,
                            'change' => $changeVal,
                            'payment_change' => $paymentChange,
                            'status' => 'Sukses',
                            'customer_id' => 'Pelanggan ' . $trxIndex,
                            'customer_phone' => $custPhone,
                            'operator' => 'Andini (Kasir)',
                            'saldo_elektrik_remaining' => $branchBalances[$branch->name],
                            'created_at' => $createdTime,
                            'updated_at' => $createdTime,
                        ]);

                        // Write items and movements
                        foreach ($itemsData as $item) {
                            $prod = $item['product'];
                            $qty = $item['qty'];
                            
                            TransactionItem::create([
                                'transaction_id' => $trx->id,
                                'product_id' => $prod->id,
                                'product_sku' => $prod->sku,
                                'product_name' => $prod->name,
                                'product_category' => $prod->category,
                                'quantity' => $qty,
                                'price' => $prod->price,
                                'destination_number' => $prod->is_digital ? '0812-' . mt_rand(1000, 9999) . '-' . mt_rand(1000, 9999) : null,
                                'created_at' => $createdTime,
                                'updated_at' => $createdTime,
                            ]);

                            if (!$prod->is_digital) {
                                $prod->final_stock -= $qty;
                                $prod->sold_stock += $qty;
                                $prod->save();

                                StockMovement::create([
                                    'product_id' => $prod->id,
                                    'product_name' => $prod->name,
                                    'product_sku' => $prod->sku,
                                    'product_category' => $prod->category,
                                    'branch_name' => $branch->name,
                                    'quantity_change' => -$qty,
                                    'final_stock' => $prod->final_stock,
                                    'type' => 'Penjualan',
                                    'customer_phone' => $custPhone,
                                    'customer_id' => 'Pelanggan ' . $trxIndex,
                                    'destination_number' => null,
                                    'payment_method' => $paymentMethod,
                                    'payment_change' => $paymentChange,
                                    'reference_no' => $refNo,
                                    'operator' => 'Andini (Kasir)',
                                    'saldo_elektrik_remaining' => $branchBalances[$branch->name],
                                    'created_at' => $createdTime,
                                    'updated_at' => $createdTime,
                                ]);
                            }
                        }
                    }
                }
            }
        }

        // 4. Update status field for physical products at the end
        $allProducts = Product::where('is_digital', false)->get();
        foreach ($allProducts as $p) {
            if ($p->final_stock <= 0) {
                $p->status = 'Habis';
            } elseif ($p->final_stock <= 5) {
                $p->status = 'Kritis';
            } elseif ($p->final_stock <= 10) {
                $p->status = 'Tipis';
            } else {
                $p->status = 'Aman';
            }
            $p->save();
        }

        // 5. Update branch revenue and stock health metrics based on final database state
        foreach ($branches as $branch) {
            $revenue = Transaction::where('branch_id', $branch->id)
                ->where('status', 'Sukses')
                ->whereDate('created_at', Carbon::now()->toDateString())
                ->sum('total_amount');
            
            $branch->revenue_mtd = $revenue ? (double)$revenue : 0.0;

            $branchProducts = Product::where('branch_id', $branch->id)->get();
            $branch->stock_available = $branchProducts->where('is_digital', false)->sum('final_stock');
            
            $totalInitialIncoming = $branchProducts->where('is_digital', false)->sum(fn($p) => ($p->initial_stock ?? 0) + ($p->incoming_stock ?? 0));
            $totalFinal = $branchProducts->where('is_digital', false)->sum('final_stock');
            $healthPct = $totalInitialIncoming > 0 ? ($totalFinal / $totalInitialIncoming) * 100 : 100;
            $branch->stock_health = round($healthPct);
            
            // Save seeded final electric balance
            $branch->saldo_elektrik = $branchBalances[$branch->name];
            
            // Set last_active_at: Online branches get Carbon::now(), Offline branches get Carbon::now()->subHours(2)
            $rawStatus = $branch->getRawOriginal('status') ?? $branch->status;
            if ($rawStatus === 'Online') {
                $branch->last_active_at = Carbon::now();
            } else {
                $branch->last_active_at = Carbon::now()->subHours(2);
            }
            
            $branch->save();
        }

        // Reset random seed to default
        mt_srand();

        if (app()->environment('testing')) {
            Carbon::setTestNow();
        }
    }
}
