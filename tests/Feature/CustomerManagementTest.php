<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    /**
     * Test customer index displays the list of seeded customers.
     */
    public function test_customer_index_displays_list(): void
    {
        $response = $this->get(route('customer.index'));
        $response->assertStatus(200);
        $response->assertSee('Manajemen Pelanggan');
        $response->assertSee('Ahmad Yani');
        $response->assertSee('Bambang Wijaya');
    }

    /**
     * Test creating a new customer.
     */
    public function test_customer_can_be_created(): void
    {
        $branch = Branch::first();
        $this->assertNotNull($branch);

        $customerData = [
            'name' => 'Charlie Brown',
            'phone' => '0812-5555-4444',
            'branch_id' => $branch->id,
        ];

        $response = $this->post(route('customer.store'), $customerData);
        $response->assertRedirect();

        $this->assertDatabaseHas('customers', [
            'name' => 'Charlie Brown',
            'phone' => '0812-5555-4444',
            'branch_id' => $branch->id,
        ]);
    }

    /**
     * Test validation that phone number must be unique.
     */
    public function test_customer_phone_must_be_unique(): void
    {
        $existing = Customer::first();
        $this->assertNotNull($existing);

        $customerData = [
            'name' => 'Duplicate Phone Guy',
            'phone' => $existing->phone,
            'branch_id' => null,
        ];

        $response = $this->post(route('customer.store'), $customerData);
        $response->assertSessionHasErrors(['phone']);
    }

    /**
     * Test updating a customer.
     */
    public function test_customer_can_be_updated(): void
    {
        $customer = Customer::first();
        $this->assertNotNull($customer);

        $updateData = [
            'name' => 'Ahmad Yani Updated',
            'phone' => '0812-3456-9999',
            'branch_id' => null,
        ];

        $response = $this->put(route('customer.update', $customer->id), $updateData);
        $response->assertRedirect();

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Ahmad Yani Updated',
            'phone' => '0812-3456-9999',
            'branch_id' => null,
        ]);
    }

    /**
     * Test deleting a customer.
     */
    public function test_customer_can_be_deleted(): void
    {
        $customer = Customer::first();
        $this->assertNotNull($customer);

        $response = $this->delete(route('customer.destroy', $customer->id));
        $response->assertRedirect();

        $this->assertNull(Customer::find($customer->id));
    }

    /**
     * Test filtering customers by branch.
     */
    public function test_customer_list_can_be_filtered_by_branch(): void
    {
        // Get branch and customer in that branch
        $customer = Customer::whereNotNull('branch_id')->first();
        $this->assertNotNull($customer);

        $branchId = $customer->branch_id;

        // Get another customer with a different branch or null branch
        $otherCustomer = Customer::where(function($q) use ($branchId) {
            $q->where('branch_id', '!=', $branchId)
              ->orWhereNull('branch_id');
        })->first();
        $this->assertNotNull($otherCustomer);

        $response = $this->get(route('customer.index', ['branch_id' => $branchId]));
        $response->assertStatus(200);
        $response->assertSee($customer->name);
        $response->assertDontSee($otherCustomer->name);
    }

    /**
     * Test searching customers by name or phone.
     */
    public function test_customer_list_can_be_searched(): void
    {
        $customer = Customer::first();
        $this->assertNotNull($customer);

        // Search by name
        $responseName = $this->get(route('customer.index', ['search' => $customer->name]));
        $responseName->assertStatus(200);
        $responseName->assertSee($customer->name);

        // Search by phone
        $responsePhone = $this->get(route('customer.index', ['search' => $customer->phone]));
        $responsePhone->assertStatus(200);
        $responsePhone->assertSee($customer->name);
    }

    /**
     * Test dynamic transaction metrics calculation (count and total spent).
     */
    public function test_customer_transaction_metrics_calculation(): void
    {
        $customer = Customer::create([
            'name' => 'Metrics Test Customer',
            'phone' => '0899-9999-9999',
            'branch_id' => Branch::first()->id,
        ]);

        // Create transactions matching the customer phone
        Transaction::create([
            'id' => 'TX-TEST-001',
            'branch_id' => $customer->branch_id ?? Branch::first()->id,
            'total_amount' => 50000,
            'payment_method' => 'Tunai',
            'cash_paid' => 50000,
            'change' => 0,
            'status' => 'Sukses',
            'customer_phone' => $customer->phone,
            'operator' => 'Test Operator',
        ]);

        Transaction::create([
            'id' => 'TX-TEST-002',
            'branch_id' => $customer->branch_id ?? Branch::first()->id,
            'total_amount' => 35000,
            'payment_method' => 'Tunai',
            'cash_paid' => 40000,
            'change' => 5000,
            'status' => 'Sukses',
            'customer_phone' => $customer->phone,
            'operator' => 'Test Operator',
        ]);

        // Failed transaction should count towards count but spent should be 0
        Transaction::create([
            'id' => 'TX-TEST-003',
            'branch_id' => $customer->branch_id ?? Branch::first()->id,
            'total_amount' => 100000,
            'payment_method' => 'Tunai',
            'cash_paid' => 100000,
            'change' => 0,
            'status' => 'Gagal',
            'customer_phone' => $customer->phone,
            'operator' => 'Test Operator',
        ]);

        // Load index searching specifically for this customer
        $response = $this->get(route('customer.index', ['search' => '0899-9999-9999']));
        $response->assertStatus(200);

        // Check if metrics are displayed
        // 3 total transactions, and 85,000 total spent (Rp 85.000)
        $response->assertSee('3'); // Transaction count
        $response->assertSee('Rp 85.000'); // Total spent format
    }
}
