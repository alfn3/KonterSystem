<?php

namespace Tests\Feature;

use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchStatusTest extends TestCase
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
     * Test that the branch list table renames the MTD Revenue column, formats it fully in Rupiah,
     * and deletes the Stock Available column.
     */
    public function test_branch_list_revenue_formatting_and_no_stock_available_column(): void
    {
        $branch = Branch::first();
        $this->assertNotNull($branch);

        // Clear transactions for this branch
        \App\Models\Transaction::where('branch_id', $branch->id)->delete();

        // Seed a transaction today for 12,500,000 to test the formatting
        \App\Models\Transaction::create([
            'id' => 'TX-TEST-REV-FORMAT',
            'branch_id' => $branch->id,
            'total_amount' => 12500000.00,
            'payment_method' => 'Tunai',
            'cash_paid' => 12500000.00,
            'change' => 0,
            'payment_change' => 'Pas',
            'status' => 'Sukses',
            'customer_id' => 'Pelanggan Test',
            'operator' => 'Andini (Kasir)',
            'created_at' => now(),
        ]);

        $response = $this->get(route('branch.index'));
        $response->assertStatus(200);

        // Should see "Penjualan" instead of "Revenue MTD"
        $response->assertSee('Penjualan');
        $response->assertDontSee('Revenue MTD');

        // Should NOT see "Stok Tersedia" in table header
        $response->assertDontSee('Stok Tersedia');

        // Should see formatted Rupiah "Rp 12.500.000"
        $response->assertSee('Rp 12.500.000');
    }

    /**
     * Test that the branch status dynamically updates to Online/Offline
     * based on last API activity from the mobile-counter.
     */
    public function test_branch_status_dynamic_online_offline_on_api_call(): void
    {
        $branch = Branch::where('name', 'mobil1')->first();
        $this->assertNotNull($branch);

        // Ensure last_active_at starts as null
        $branch->update(['last_active_at' => null]);

        // 1. Initial access (should fallback to static db status from seeder, which is Online)
        $response = $this->get(route('branch.index'));
        $response->assertStatus(200);
        $response->assertSee('Online');

        // 2. Call an API endpoint (e.g., get saldo elektrik) to simulate mobile-counter activity
        $apiResponse = $this->get('/api/saldo-elektrik');
        $apiResponse->assertStatus(200);

        // Verify last_active_at was touched
        $branch = $branch->fresh();
        $this->assertNotNull($branch->last_active_at);

        // Verify status is Online on index page
        $response = $this->get(route('branch.index'));
        $response->assertStatus(200);
        $response->assertSee('Online');
        $response->assertSee('bg-green-500'); // Online status class

        // 3. Simulate inactivity (e.g. last active 2 minutes ago)
        $branch->update([
            'last_active_at' => now()->subMinutes(2),
        ]);

        // Verify status becomes Offline
        $response = $this->get(route('branch.index'));
        $response->assertStatus(200);
        $response->assertSee('Offline');
        $response->assertSee('bg-slate-400'); // Offline status class
    }

    /**
     * Test that today's customer count is displayed correctly on the branch index page and in the status API.
     */
    public function test_branch_list_displays_today_customer_count(): void
    {
        $branch = Branch::where('name', 'mobil2')->first();
        $this->assertNotNull($branch);

        $response = $this->get(route('branch.index'));
        $response->assertStatus(200);

        // Since the seeder seeds 4 transactions for mobil2 today, today's customer count should be 4
        $response->assertSee('4 Orang');

        // Test API status response
        $apiResponse = $this->get('/api/cabang/status');
        $apiResponse->assertStatus(200);
        
        $data = $apiResponse->json();
        $mobil2Data = collect($data)->firstWhere('name', 'mobil2');
        $this->assertNotNull($mobil2Data);
        $this->assertEquals(4, $mobil2Data['today_customer_count']);
    }

    /**
     * Test that the branch name and agent ID can be edited.
     */
    public function test_branch_name_and_agent_id_can_be_edited(): void
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

        $updateData = [
            'name' => 'Updated Konter',
            'agent_id' => 'upd_agent',
            'address' => 'Jl. Baru No. 2',
        ];

        $response = $this->put(route('branch.update', $branch->id), $updateData);
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Data cabang berhasil diperbarui!');

        $branch->refresh();
        $this->assertEquals('Updated Konter', $branch->name);
        $this->assertEquals('upd_agent', $branch->agent_id);
        $this->assertEquals('Jl. Baru No. 2', $branch->address);
    }
}
