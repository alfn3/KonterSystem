<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected function setUp(): void
    {
        \Carbon\Carbon::setTestNow('2026-06-18 12:00:00');
        parent::setUp();
    }

    protected function tearDown(): void
    {
        \Carbon\Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * Test the main dashboard page.
     */
    public function test_dashboard_returns_success(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('MobileCell');
        $response->assertSee('Store Health Monitor');
    }

    /**
     * Test the dashboard metrics and branch card layout.
     */
    public function test_dashboard_metrics_and_branch_layout(): void
    {
        // 1. Seed some successful transactions with items
        $branch = \App\Models\Branch::first();
        
        $transaction = \App\Models\Transaction::create([
            'id' => 'TX-TEST-DASHBOARD',
            'branch_id' => $branch->id,
            'total_amount' => 150000,
            'payment_method' => 'QRIS',
            'cash_paid' => 150000,
            'change' => 0,
            'status' => 'Sukses',
            'operator' => 'Operator Test',
        ]);
        
        \App\Models\TransactionItem::create([
            'transaction_id' => $transaction->id,
            'product_sku' => 'SKU-DASH-1',
            'product_name' => 'Product Dash 1',
            'product_category' => 'Perdana',
            'quantity' => 5,
            'price' => 30000,
        ]);
        
        // 2. Add another transaction with status other than 'Sukses' to ensure it's not counted
        $failedTransaction = \App\Models\Transaction::create([
            'id' => 'TX-TEST-DASHBOARD-FAILED',
            'branch_id' => $branch->id,
            'total_amount' => 60000,
            'payment_method' => 'Tunai',
            'cash_paid' => 60000,
            'change' => 0,
            'status' => 'Gagal',
            'operator' => 'Operator Test',
        ]);
        
        \App\Models\TransactionItem::create([
            'transaction_id' => $failedTransaction->id,
            'product_sku' => 'SKU-DASH-1',
            'product_name' => 'Product Dash 1',
            'product_category' => 'Perdana',
            'quantity' => 2,
            'price' => 30000,
        ]);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        
        // The total transactions value should include 5 items (from the successful transaction item quantity)
        $totalItemsSold = \App\Models\TransactionItem::whereHas('transaction', function($query) {
            $query->where('status', 'Sukses');
        })->sum('quantity');
        $response->assertSee(number_format($totalItemsSold));

        // Ensure the online/offline status badge is NOT present on yesterday's branch cards
        $response->assertDontSee('status-dot');
        $response->assertDontSee('animate-pulse-online');
    }


    /**
     * Test the inventory page.
     */
    public function test_inventory_returns_success(): void
    {
        $response = $this->get(route('inventory.index'));
        $response->assertStatus(200);
        $response->assertSee('MobileCell');
        $response->assertSee('Perdana Smartfren Unlimited 7 Hari');
    }

    /**
     * Test the branch management page.
     */
    public function test_branch_returns_success(): void
    {
        $response = $this->get(route('branch.index'));
        $response->assertStatus(200);
        $response->assertSee('MobileCell');
        $response->assertSee('mobil1');
    }

    /**
     * Test the audit page.
     */
    public function test_audit_returns_success(): void
    {
        $response = $this->get(route('audit.index'));
        $response->assertStatus(200);
        $response->assertSee('MobileCell');
        $response->assertSee('mobil2');
    }

    /**
     * Test the monthly report page.
     */
    public function test_report_monthly_returns_success(): void
    {
        $response = $this->get(route('report.monthly'));
        $response->assertStatus(200);
        $response->assertSee('MobileCell');
        $response->assertSee('Selisih per Kasir');
    }

    /**
     * Test storing a product with HPP.
     */
    public function test_store_product_saves_hpp(): void
    {
        $productData = [
            'brand' => 'Telkomsel',
            'name' => 'Perdana Telkomsel 14GB',
            'sku' => 'PDN-TSEL-14GB',
            'category' => 'Perdana',
            'initial_stock' => 10,
            'incoming_stock' => 5,
            'final_stock' => 15,
            'sold_stock' => 0,
            'hpp' => 30000,
            'price' => 38000,
        ];

        $response = $this->post(route('inventory.store'), $productData);
        $response->assertRedirect();
        
        $this->assertDatabaseHas('products', [
            'sku' => 'PDN-TSEL-14GB',
            'hpp' => 30000,
            'price' => 38000,
        ]);
    }

    /**
     * Test creating a product without stock values defaults them to 0.
     */
    public function test_store_product_without_stock_defaults_to_zero(): void
    {
        $productData = [
            'brand' => 'Indosat',
            'name' => 'Perdana Indosat 10GB',
            'sku' => 'PDN-ISAT-10GB',
            'category' => 'Perdana',
            'hpp' => 20000,
            'price' => 28000,
        ];

        $response = $this->post(route('inventory.store'), $productData);
        $response->assertRedirect();
        
        $this->assertDatabaseHas('products', [
            'sku' => 'PDN-ISAT-10GB',
            'initial_stock' => 0,
            'incoming_stock' => 0,
            'final_stock' => 0,
            'sold_stock' => 0,
        ]);
    }

    /**
     * Test creating a product without SKU automatically generates a unique SKU.
     */
    public function test_store_product_without_sku_auto_generates_sku(): void
    {
        $productData = [
            'brand' => 'Telkomsel',
            'name' => 'Perdana Telkomsel 14GB Baru',
            'category' => 'Perdana',
            'hpp' => 30000,
            'price' => 38000,
        ];

        $response = $this->post(route('inventory.store'), $productData);
        $response->assertRedirect();
        
        // Assert SKU starts with PDN-TSEL- and has name suffix
        $product = \App\Models\Product::where('brand', 'Telkomsel')->where('name', 'Perdana Telkomsel 14GB Baru')->first();
        $this->assertNotNull($product);
        $this->assertStringStartsWith('PDN-TSEL-', $product->sku);
    }

    /**
     * Test that products are sorted by category weight (Perdana > Voucher > Aksesoris) and then by brand.
     */
    public function test_products_are_sorted_by_custom_category_weight_and_brand(): void
    {
        $response = $this->get(route('inventory.index'));
        $response->assertStatus(200);
        
        $products = $response->viewData('products');
        
        $categoryOrder = [
            'Perdana' => 1,
            'Voucher' => 2,
            'Aksesoris' => 3,
        ];
        
        $lastWeight = 0;
        $lastCategory = '';
        $lastBrand = '';
        
        foreach ($products as $p) {
            $currentWeight = $categoryOrder[$p['category']] ?? 999;
            
            $this->assertGreaterThanOrEqual($lastWeight, $currentWeight);
            
            if ($lastWeight === $currentWeight) {
                $this->assertGreaterThanOrEqual(strtolower($lastCategory), strtolower($p['category']));
                
                if ($lastCategory === $p['category']) {
                    $this->assertGreaterThanOrEqual(strtolower($lastBrand), strtolower($p['brand']));
                }
            }
            
            $lastWeight = $currentWeight;
            $lastCategory = $p['category'];
            $lastBrand = $p['brand'];
        }
    }

    /**
     * Test the stock movement history page returns success.
     */
    public function test_history_returns_success(): void
    {
        $response = $this->get(route('inventory.history'));
        $response->assertStatus(200);
        $response->assertSee('Riwayat Pergerakan Stok');
        $response->assertSee('Voucher XL Combo Flex 12GB');
    }

    /**
     * Test date filter on stock movement history.
     */
    public function test_history_date_filter_filters_results(): void
    {
        $todayStr = \Carbon\Carbon::now()->toDateString();
        $responseToday = $this->get(route('inventory.history', ['date' => $todayStr]));
        $responseToday->assertStatus(200);
        $responseToday->assertSee('Voucher XL Combo Flex 12GB');

        $responsePast = $this->get(route('inventory.history', ['date' => '2020-01-01']));
        $responsePast->assertStatus(200);
        $responsePast->assertDontSee('Voucher XL Combo Flex 12GB');
    }

    /**
     * Test that updating a product final stock logs a movement log.
     */
    public function test_product_update_records_movement(): void
    {
        $product = \App\Models\Product::whereNull('branch_id')->first();
        $this->assertNotNull($product);

        $response = $this->put(route('inventory.update', $product->id), [
            'brand' => $product->brand,
            'name' => $product->name,
            'sku' => $product->sku,
            'category' => $product->category,
            'initial_stock' => $product->initial_stock,
            'incoming_stock' => $product->incoming_stock,
            'final_stock' => $product->final_stock + 10, // Change final stock
            'sold_stock' => $product->sold_stock,
            'price' => $product->price,
            'hpp' => $product->hpp,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        
        $this->assertDatabaseHas('stock_movements', [
            'product_sku' => $product->sku,
            'quantity_change' => 10,
            'type' => 'Koreksi',
        ]);
    }

    /**
     * Test restocking a branch from Gudang.
     */
    public function test_branch_restock_works(): void
    {
        $gudangProduct = \App\Models\Product::whereNull('branch_id')->where('sku', 'TS-SIM-10-JKT')->first();
        $branchProduct = \App\Models\Product::whereNotNull('branch_id')->where('sku', 'TS-SIM-10-JKT')->first();

        $this->assertNotNull($gudangProduct);
        $this->assertNotNull($branchProduct);

        $initialGudangQty = $gudangProduct->final_stock;
        $initialBranchQty = $branchProduct->final_stock;

        $response = $this->post(route('inventory.restock'), [
            'branch_name' => $branchProduct->branch->name,
            'supplier' => 'Gudang Pusat',
            'reference_no' => 'TEST-RESTOCK-001',
            'items' => [
                [
                    'sku' => 'TS-SIM-10-JKT',
                    'quantity' => 15,
                ]
            ]
        ]);

        $response->assertRedirect();

        // Check stock levels changed
        $this->assertEquals($initialGudangQty - 15, $gudangProduct->fresh()->final_stock);
        $this->assertEquals($initialBranchQty + 15, $branchProduct->fresh()->final_stock);

        // Check movements logged
        $this->assertDatabaseHas('stock_movements', [
            'product_sku' => 'TS-SIM-10-JKT',
            'branch_name' => 'Gudang > ' . $branchProduct->branch->name,
            'quantity_change' => -15,
            'reference_no' => 'TEST-RESTOCK-001',
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_sku' => 'TS-SIM-10-JKT',
            'branch_name' => 'Gudang > ' . $branchProduct->branch->name,
            'quantity_change' => 15,
            'reference_no' => 'TEST-RESTOCK-001',
        ]);
    }

    /**
     * Test restocking the Gudang from supplier.
     */
    public function test_gudang_restock_works(): void
    {
        $gudangProduct = \App\Models\Product::whereNull('branch_id')->where('sku', 'TS-SIM-10-JKT')->first();
        $this->assertNotNull($gudangProduct);

        $initialGudangQty = $gudangProduct->final_stock;

        $response = $this->post(route('inventory.restock'), [
            'branch_name' => 'Gudang',
            'supplier' => 'PT Jaya Abadi Telekom',
            'reference_no' => 'TEST-GUDANG-002',
            'items' => [
                [
                    'sku' => 'TS-SIM-10-JKT',
                    'quantity' => 100,
                ]
            ]
        ]);

        $response->assertRedirect();

        $this->assertEquals($initialGudangQty + 100, $gudangProduct->fresh()->final_stock);

        $this->assertDatabaseHas('stock_movements', [
            'product_sku' => 'TS-SIM-10-JKT',
            'branch_name' => 'Gudang',
            'quantity_change' => 100,
            'type' => 'Restok',
            'reference_no' => 'TEST-GUDANG-002',
        ]);
    }

    /**
     * Test the inventory analytics page.
     */
    public function test_inventory_analytics_returns_success(): void
    {
        $response = $this->get(route('inventory.analytics'));
        $response->assertStatus(200);
        $response->assertSee('Detail Analitik Inventoris');
        $response->assertSee('Total Nilai Aset');
        $response->assertSee('Fast Moving');
    }

    /**
     * Test date filter on inventory index.
     */
    public function test_inventory_index_date_filter(): void
    {
        $response = $this->get(route('inventory.index', ['date' => '2026-05-31']));
        $response->assertStatus(200);

        $responsePast = $this->get(route('inventory.index', ['date' => '2020-01-01']));
        $responsePast->assertStatus(200);
        $responsePast->assertSee('Tidak ada data produk untuk cabang ini.');
    }

    /**
     * Test date filter on inventory analytics.
     */
    public function test_inventory_analytics_date_filter(): void
    {
        $response = $this->get(route('inventory.analytics', ['date' => '2026-05-31']));
        $response->assertStatus(200);

        $responsePast = $this->get(route('inventory.analytics', ['date' => '2020-01-01']));
        $responsePast->assertStatus(200);
        $responsePast->assertSee('Tidak ada data aset kategori');
    }

    /**
     * Test that updating initial stock adjusts final stock, and vice versa.
     */
    public function test_product_update_syncs_initial_and_final_stocks(): void
    {
        $product = \App\Models\Product::whereNull('branch_id')->first();
        $this->assertNotNull($product);

        $oldInitial = $product->initial_stock;
        $oldFinal = $product->final_stock;

        // 1. Update initial_stock only. final_stock should adjust by the same difference.
        $response = $this->put(route('inventory.update', $product->id), [
            'brand' => $product->brand,
            'name' => $product->name,
            'sku' => $product->sku,
            'category' => $product->category,
            'initial_stock' => $oldInitial + 10,
            'incoming_stock' => $product->incoming_stock,
            'final_stock' => $oldFinal,
            'sold_stock' => $product->sold_stock,
            'price' => $product->price,
            'hpp' => $product->hpp,
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertEquals($oldFinal + 10, $product->fresh()->final_stock);

        // 2. Update final_stock only. initial_stock should adjust by the same difference.
        $productFresh = $product->fresh();
        $oldInitial2 = $productFresh->initial_stock;
        $oldFinal2 = $productFresh->final_stock;

        $response2 = $this->put(route('inventory.update', $product->id), [
            'brand' => $product->brand,
            'name' => $product->name,
            'sku' => $product->sku,
            'category' => $product->category,
            'initial_stock' => $oldInitial2,
            'incoming_stock' => $product->incoming_stock,
            'final_stock' => $oldFinal2 - 5,
            'sold_stock' => $product->sold_stock,
            'price' => $product->price,
            'hpp' => $product->hpp,
        ]);
        $response2->assertSessionHasNoErrors();
        $this->assertEquals($oldInitial2 - 5, $product->fresh()->initial_stock);
    }

    /**
     * Test the branch detail show page.
     */
    public function test_branch_show_page_returns_success(): void
    {
        $branch = \App\Models\Branch::first();
        $this->assertNotNull($branch);

        $response = $this->get(route('branch.show', $branch->id));
        $response->assertStatus(200);
        $response->assertSee('Detail Per Konter');
        $response->assertSee($branch->name);
        $response->assertSee('Rincian Stok Produk');
        $response->assertSee('Rincian Saldo Laci');
        $response->assertSee('Detail Pengeluaran');
        $response->assertSee('Agent Activity');
        $response->assertSee('Log Aktivitas Terbaru');
    }

    /**
     * Test filtering branch detail by date.
     */
    public function test_branch_show_page_can_filter_by_date(): void
    {
        $branch = \App\Models\Branch::first();
        $this->assertNotNull($branch);

        $response = $this->get(route('branch.show', ['id' => $branch->id, 'date' => '2023-10-15']));
        $response->assertStatus(200);
        $response->assertSee('Detail Per Konter');
        $response->assertSee('15 ');
        $response->assertSee('2023');
    }

    /**
     * Test updating the branch address.
     */
    public function test_branch_can_update_address(): void
    {
        $branch = \App\Models\Branch::first();
        $this->assertNotNull($branch);

        $newAddress = 'Jl. Baru No. 123, Bandung';
        $response = $this->put(route('branch.update', $branch->id), [
            'address' => $newAddress,
        ]);

        $response->assertRedirect(route('branch.show', $branch->id));
        $this->assertEquals($newAddress, $branch->fresh()->address);
    }

    /**
     * Test deleting a branch.
     */
    public function test_branch_can_be_deleted(): void
    {
        $branch = \App\Models\Branch::first();
        $this->assertNotNull($branch);

        $response = $this->delete(route('branch.destroy', $branch->id));
        $response->assertRedirect(route('branch.index'));
        $this->assertNull(\App\Models\Branch::find($branch->id));
    }

    /**
     * Test the branch activity details page returns success.
     */
    public function test_branch_activities_page_returns_success(): void
    {
        $branch = \App\Models\Branch::first();
        $this->assertNotNull($branch);

        $response = $this->get(route('branch.activities', $branch->id));
        $response->assertStatus(200);
        @$response->assertSee('Aktivitas Cabang');
        @$response->assertSee($branch->name);
        @$response->assertSee('Seluruh Aktivitas Cabang');
    }

    /**
     * Test sales logs contain customer phone numbers.
     */
    public function test_sales_log_contains_customer_phone(): void
    {
        $branch = \App\Models\Branch::where('name', 'mobil2')->first();
        $this->assertNotNull($branch);

        // Get activities for mobil2 where the seeder has seeded sales logs
        $response = $this->get(route('branch.activities', [
            'id' => $branch->id,
            // Use the date of seeder created logs (which is Carbon::now() with some hours offset, so today's date)
            'date' => \Carbon\Carbon::now()->toDateString()
        ]));
        $response->assertStatus(200);
        $response->assertSee('Pelanggan 1');
    }

    /**
     * Test sales logs contain digital items sales.
     */
    public function test_sales_log_contains_digital_sales(): void
    {
        $branch = \App\Models\Branch::where('name', 'mobil2')->first();
        $this->assertNotNull($branch);

        $response = $this->get(route('branch.activities', [
            'id' => $branch->id,
            'date' => \Carbon\Carbon::now()->toDateString()
        ]));
        $response->assertStatus(200);
        $response->assertSee('Penjualan');
        $response->assertSee('top up (Dana 100k)');
    }

    /**
     * Test sales logs contain target destination numbers and payment method / change details.
     */
    public function test_sales_log_contains_payment_details(): void
    {
        $branch = \App\Models\Branch::where('name', 'mobil2')->first();
        $this->assertNotNull($branch);

        $response = $this->get(route('branch.activities', [
            'id' => $branch->id,
            'date' => \Carbon\Carbon::now()->toDateString()
        ]));
        $response->assertStatus(200);
        $response->assertSee('No Tujuan');
        $response->assertSee('Pelanggan 1');
        $response->assertSee('0812-9876-5432'); // target/destination number for Dana topup
        $response->assertSee('QRIS (Pas)'); // Payment info for Dana topup
        $response->assertSee('Tunai (Kembali: Rp 5.000)'); // Payment info for Pelanggan 1
    }

    /**
     * Test sales logs contain multi-product transaction payment details (e.g. total based change/underpayment).
     */
    public function test_sales_log_contains_multi_product_payment_details(): void
    {
        $branch = \App\Models\Branch::where('name', 'mobil2')->first();
        $this->assertNotNull($branch);

        $response = $this->get(route('branch.activities', [
            'id' => $branch->id,
            'date' => \Carbon\Carbon::now()->toDateString()
        ]));
        $response->assertStatus(200);

        // Transaction 006: total 95k, paid 100k, change Kembali: Rp 5.000
        $response->assertSee('Tunai (Kembali: Rp 5.000)');
        // Transaction 007: total 60k, paid 58k, underpayment Kurang: Rp 2.000
        $response->assertSee('Tunai (Kurang: Rp 2.000)');
    }

    /**
     * Test that sales logs have custom item and category formatting.
     */
    public function test_sales_log_item_formatting(): void
    {
        $branch = \App\Models\Branch::where('name', 'mobil2')->first();
        $this->assertNotNull($branch);

        $response = $this->get(route('branch.activities', [
            'id' => $branch->id,
            'date' => \Carbon\Carbon::now()->toDateString()
        ]));
        $response->assertStatus(200);

        // Should see formatted activity titles and details
        $response->assertSee('Penjualan');
        $response->assertSee('vocher (Indosat Freedom 3GB)');
        $response->assertSee('pulsa (XL 30k)');
        $response->assertSee('Tunai (Kembali: Rp 2.000)');
    }

    /**
     * Test daily stock calculation rules.
     */
    public function test_daily_stock_calculation_rules(): void
    {
        $product = \App\Models\Product::whereNull('branch_id')->first();
        $this->assertNotNull($product);

        // Update created_at of product and any related movements to 2 days ago so it exists yesterday
        $product->created_at = \Carbon\Carbon::now()->subDays(2);
        $product->save();

        $selectedDate = \Carbon\Carbon::now()->toDateString();
        $yesterdayDate = \Carbon\Carbon::now()->subDay()->toDateString();

        // Load inventory index for yesterday
        $responseYesterday = $this->get(route('inventory.index', ['date' => $yesterdayDate]));
        $responseYesterday->assertStatus(200);
        $yesterdayProducts = $responseYesterday->viewData('products');
        $yesterdayProduct = collect($yesterdayProducts)->firstWhere('sku', $product->sku);
        $this->assertNotNull($yesterdayProduct);
        $yesterdayFinalStock = $yesterdayProduct['final'];

        // Load inventory index for today
        $responseToday = $this->get(route('inventory.index', ['date' => $selectedDate]));
        $responseToday->assertStatus(200);
        $todayProducts = $responseToday->viewData('products');
        $todayProduct = collect($todayProducts)->firstWhere('sku', $product->sku);
        $this->assertNotNull($todayProduct);
        $todayInitialStock = $todayProduct['initial'];

        // Rule 1: stokawal hari ini adalah stok akhir kemarin
        $this->assertEquals($yesterdayFinalStock, $todayInitialStock);

        // Rule 2: stok akhir hari ini defaultnya sama dengan stok awal jika tidak ada pergerakan
        $futureDate = '2030-01-01';
        $responseFuture = $this->get(route('inventory.index', ['date' => $futureDate]));
        $responseFuture->assertStatus(200);
        $futureProducts = $responseFuture->viewData('products');
        $futureProduct = collect($futureProducts)->firstWhere('sku', $product->sku);
        $this->assertNotNull($futureProduct);
        $this->assertEquals($futureProduct['initial'], $futureProduct['final']);
    }

    /**
     * Test that inventory index page does not display digital categories.
     */
    public function test_inventory_only_contains_physical_categories(): void
    {
        $response = $this->get(route('inventory.index'));
        $response->assertStatus(200);
        
        $products = $response->viewData('products');
        
        foreach ($products as $p) {
            $this->assertContains($p['category'], ['Perdana', 'Voucher', 'Aksesoris']);
        }
    }

    /**
     * Test that branch show page only displays physical stock in the stock table.
     */
    public function test_branch_show_only_displays_physical_stock(): void
    {
        $branch = \App\Models\Branch::where('name', 'mobil2')->first();
        $this->assertNotNull($branch);

        $response = $this->get(route('branch.show', $branch->id));
        $response->assertStatus(200);

        $products = $response->viewData('products');

        foreach ($products as $p) {
            $this->assertContains($p->category, ['Perdana', 'Voucher', 'Aksesoris']);
        }
    }

    /**
     * Test that branch detail page displays "belum ada penjualan" when there are 0 sales transactions.
     */
    public function test_branch_show_displays_no_sales_message_when_zero_transactions(): void
    {
        $branch = \App\Models\Branch::first();
        $this->assertNotNull($branch);

        // Fetch branch details for a past date with no transaction seeding (e.g. 2023-10-15)
        $response = $this->get(route('branch.show', ['id' => $branch->id, 'date' => '2023-10-15']));
        $response->assertStatus(200);
        $response->assertSee('belum ada penjualan');
    }

    /**
     * Test electric balance seeding, tracking, and UI display.
     */
    public function test_electric_balance_tracking_and_display(): void
    {
        $branch = \App\Models\Branch::where('name', 'mobil2')->first();
        $this->assertNotNull($branch);
        $this->assertGreaterThan(0, $branch->saldo_elektrik);

        // Fetch branch show page and assert it displays electric balance
        $response = $this->get(route('branch.show', $branch->id));
        $response->assertStatus(200);
        $response->assertSee('Saldo Elektrik');
        $response->assertSee('Rp ' . number_format($branch->saldo_elektrik, 0, ',', '.'));

        // Fetch activities and assert it has Saldo Elektrik column
        $responseActs = $this->get(route('branch.activities', $branch->id));
        $responseActs->assertStatus(200);
        $responseActs->assertSee('Saldo Elektrik');
    }

    /**
     * Test specific example sales data for mobil1 on June 9 & 10, 2026.
     */
    public function test_mobil1_june_sales_seeding(): void
    {
        $branch = \App\Models\Branch::where('name', 'mobil1')->first();
        $this->assertNotNull($branch);

        // 1. June 9, 2026
        $responseJune9 = $this->get(route('branch.activities', [
            'id' => $branch->id,
            'date' => '2026-06-09',
        ]));
        $responseJune9->assertStatus(200);
        $responseJune9->assertSee('Pelanggan 1');
        $responseJune9->assertSee('0812-3456-7890');
        $responseJune9->assertSee('perdana (Smartfren Unlimited 7 Hari)');
        $responseJune9->assertSee('vocher (Indosat Freedom 3GB)');
        
        $responseJune9->assertSee('Pelanggan 2');
        $responseJune9->assertSee('0878-1122-3344');
        $responseJune9->assertSee('top-up dana 100.000');

        // 2. June 10, 2026
        $responseJune10 = $this->get(route('branch.activities', [
            'id' => $branch->id,
            'date' => '2026-06-10',
        ]));
        $responseJune10->assertStatus(200);
        $responseJune10->assertSee('Pelanggan 1');
        $responseJune10->assertSee('0813-9876-5432');
        $responseJune10->assertSee('perdana (Telkomsel Simpati 10GB Jabodetabek)');
        $responseJune10->assertSee('vocher (XL Combo Flex 12GB)');

        $responseJune10->assertSee('Pelanggan 2');
        $responseJune10->assertSee('0852-5555-6666');
        $responseJune10->assertSee('pulsa (Telkomsel 50.000)');
    }
}


