<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrisPaymentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test QRIS confirmation page returns success.
     */
    public function test_qris_confirmation_page_returns_success(): void
    {
        $response = $this->get(route('qris.index'));

        $response->assertStatus(200);
        $response->assertSee('Konfirmasi QRIS');
    }

    /**
     * Test QRIS confirmation page only lists QRIS transactions.
     */
    public function test_qris_confirmation_page_lists_qris_transactions_only(): void
    {
        $branch = Branch::create([
            'name' => 'Original Konter',
            'agent_id' => 'orig_agent',
            'status' => 'Online',
            'address' => 'Jl. Asli No. 1',
            'revenue_mtd' => 0,
            'stock_available' => 0,
            'stock_health' => 100,
            'profit_margin' => 10,
            'cash_status' => 'Cocok',
            'cash_matched' => true,
        ]);

        $qrisTx = Transaction::create([
            'id' => 'TX-QRIS-CONFIRM-1',
            'branch_id' => $branch->id,
            'total_amount' => 50000,
            'payment_method' => 'QRIS',
            'cash_paid' => 50000,
            'change' => 0,
            'status' => 'Diproses',
            'customer_phone' => '081234567890',
            'operator' => 'Andini',
        ]);

        $cashTx = Transaction::create([
            'id' => 'TX-CASH-CONFIRM-2',
            'branch_id' => $branch->id,
            'total_amount' => 30000,
            'payment_method' => 'Tunai',
            'cash_paid' => 50000,
            'change' => 20000,
            'status' => 'Sukses',
            'customer_phone' => '08987654321',
            'operator' => 'Andini',
        ]);

        $response = $this->get(route('qris.index'));

        $response->assertStatus(200);
        $response->assertSee('TX-QRIS-CONFIRM-1');
        $response->assertDontSee('TX-CASH-CONFIRM-2');
    }

    /**
     * Test manual confirmation updates status and increments branch revenue.
     */
    public function test_manual_confirmation_of_qris_transaction(): void
    {
        $branch = Branch::create([
            'name' => 'Original Konter',
            'agent_id' => 'orig_agent',
            'status' => 'Online',
            'address' => 'Jl. Asli No. 1',
            'revenue_mtd' => 0,
            'stock_available' => 0,
            'stock_health' => 100,
            'profit_margin' => 10,
            'cash_status' => 'Cocok',
            'cash_matched' => true,
        ]);

        // Seed a successful transaction for today so that initial revenue is 100,000
        Transaction::create([
            'id' => 'TX-SUCCESS-SEEDED-1',
            'branch_id' => $branch->id,
            'total_amount' => 100000,
            'payment_method' => 'Tunai',
            'cash_paid' => 100000,
            'change' => 0,
            'status' => 'Sukses',
            'customer_phone' => '081234567890',
            'operator' => 'Andini',
        ]);

        $qrisTx = Transaction::create([
            'id' => 'TX-QRIS-CONFIRM-3',
            'branch_id' => $branch->id,
            'total_amount' => 50000,
            'payment_method' => 'QRIS',
            'cash_paid' => 50000,
            'change' => 0,
            'status' => 'Diproses',
            'customer_phone' => '081234567890',
            'operator' => 'Andini',
        ]);

        $response = $this->put(route('qris.confirm', $qrisTx->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $qrisTx->refresh();
        $this->assertEquals('Sukses', $qrisTx->status);

        $branch->refresh();
        // Revenue should be incremented by transaction total amount (100,000 + 50,000 = 150,000)
        $this->assertEquals(150000, (double)$branch->revenue_mtd);
    }

    /**
     * Test QRIS list page automatically syncs from API when API key is configured.
     */
    public function test_qris_page_syncs_from_api_correctly(): void
    {
        $branch = Branch::create([
            'name' => 'mobil 1',
            'agent_id' => 'agent_1',
            'status' => 'Online',
            'address' => 'Jl. Asli No. 1',
            'revenue_mtd' => 0,
            'stock_available' => 0,
            'stock_health' => 100,
            'profit_margin' => 10,
            'cash_status' => 'Cocok',
            'cash_matched' => true,
        ]);

        // Enable sync in tests config
        config([
            'services.temanqris.api_key' => 'test-api-key',
            'services.temanqris.sync_in_tests' => true,
        ]);

        // Fake HTTP client response
        \Illuminate\Support\Facades\Http::fake([
            'https://temanqris.com/api/qris/payment-links' => \Illuminate\Support\Facades\Http::response([
                'payment_links' => [
                    [
                        'order_id' => 'TX-API-SYNC-1',
                        'status' => 'paid',
                        'amount' => 25000,
                        'created_at' => now()->toIso8601String(),
                        'qris' => '00020101021226580009COM.DUMMY...'
                    ],
                    [
                        'order_id' => 'TX-API-SYNC-2',
                        'status' => 'pending',
                        'amount' => 15000,
                        'created_at' => now()->toIso8601String(),
                        'qris' => '00020101021226580009COM.DUMMY...'
                    ]
                ]
            ], 200)
        ]);

        // Count initial transactions in DB
        $initialCount = Transaction::where('payment_method', 'QRIS')->count();

        // Call the index page which triggers sync
        $response = $this->get(route('qris.index'));

        $response->assertStatus(200);

        // Verify transactions were created
        $this->assertEquals($initialCount + 2, Transaction::where('payment_method', 'QRIS')->count());

        // Check TX-API-SYNC-1 (status paid -> Sukses, branch revenue should be updated)
        $tx1 = Transaction::find('TX-API-SYNC-1');
        $this->assertNotNull($tx1);
        $this->assertEquals('Sukses', $tx1->status);
        $this->assertEquals(25000, $tx1->total_amount);
        $this->assertEquals('00020101021226580009COM.DUMMY...', $tx1->qris);

        // Check TX-API-SYNC-2 (status pending -> Diproses)
        $tx2 = Transaction::find('TX-API-SYNC-2');
        $this->assertNotNull($tx2);
        $this->assertEquals('Diproses', $tx2->status);

        // Branch revenue should have increased by the successful transaction amount (25,000)
        $branch->refresh();
        $this->assertEquals(25000, (double)$branch->revenue_mtd);

        // Verify items were created
        $this->assertDatabaseHas('transaction_items', [
            'transaction_id' => 'TX-API-SYNC-1',
            'product_sku' => 'QRIS',
            'price' => 25000,
        ]);
    }
}

