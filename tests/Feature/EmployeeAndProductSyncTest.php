<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeAndProductSyncTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    /**
     * Test employee list loading and search.
     */
    public function test_employee_index_displays_list(): void
    {
        $response = $this->get(route('employee.index'));
        $response->assertStatus(200);
        $response->assertSee('Manajemen Karyawan');
        $response->assertSee('Andini');
        $response->assertSee('Budi Santoso');
    }

    /**
     * Test creating a new employee.
     */
    public function test_employee_can_be_created(): void
    {
        $employeeData = [
            'name' => 'John Doe',
            'role' => 'Kasir',
            'email' => 'john.doe@example.com',
            'phone' => '0812-9999-8888',
            'status' => 'Aktif',
            'home_address' => 'Medan, Sumatra Utara',
            'start_date' => '2025-06-01',
        ];

        $response = $this->post(route('employee.store'), $employeeData);
        $response->assertRedirect();
        
        $this->assertDatabaseHas('employees', [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'home_address' => 'Medan, Sumatra Utara',
            'start_date' => '2025-06-01 00:00:00',
        ]);
    }

    /**
     * Test updating an employee.
     */
    public function test_employee_can_be_updated(): void
    {
        $employee = Employee::first();
        $this->assertNotNull($employee);

        $updateData = [
            'name' => 'John Updated',
            'role' => $employee->role,
            'email' => 'updated@example.com',
            'phone' => $employee->phone,
            'status' => 'Nonaktif',
            'home_address' => 'Balikpapan, Kalimantan Timur',
            'start_date' => '2025-06-10',
        ];

        $response = $this->put(route('employee.update', $employee->id), $updateData);
        $response->assertRedirect();

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'name' => 'John Updated',
            'status' => 'Nonaktif',
            'home_address' => 'Balikpapan, Kalimantan Timur',
            'start_date' => '2025-06-10 00:00:00',
        ]);
    }

    /**
     * Test deleting an employee.
     */
    public function test_employee_can_be_deleted(): void
    {
        $employee = Employee::first();
        $this->assertNotNull($employee);

        $response = $this->delete(route('employee.destroy', $employee->id));
        $response->assertRedirect();

        $this->assertNull(Employee::find($employee->id));
    }

    /**
     * Test product creation in Gudang replicates to all branches with stock 0.
     */
    public function test_product_created_in_gudang_replicates_to_branches(): void
    {
        $totalBranches = Branch::count();
        $this->assertGreaterThan(0, $totalBranches);

        $productData = [
            'brand' => 'Telkomsel',
            'name' => 'Perdana Super Test 20GB',
            'sku' => 'PDN-TSEL-SUPERTEST',
            'category' => 'Perdana',
            'initial_stock' => 10,
            'incoming_stock' => 5,
            'final_stock' => 15,
            'sold_stock' => 0,
            'hpp' => 20000,
            'price' => 30000,
        ];

        $response = $this->post(route('inventory.store'), $productData);
        $response->assertRedirect();

        // Verify it was created in Gudang
        $this->assertDatabaseHas('products', [
            'sku' => 'PDN-TSEL-SUPERTEST',
            'branch_id' => null,
            'final_stock' => 15,
        ]);

        // Verify it exists in all branches with stock 0
        $branches = Branch::all();
        foreach ($branches as $branch) {
            $this->assertDatabaseHas('products', [
                'sku' => 'PDN-TSEL-SUPERTEST',
                'branch_id' => $branch->id,
                'final_stock' => 0,
            ]);
        }
    }

    /**
     * Test creating a branch clones all existing Gudang products to the new branch with stock 0.
     */
    public function test_new_branch_clones_gudang_products(): void
    {
        $gudangProductsCount = Product::whereNull('branch_id')->count();
        $this->assertGreaterThan(0, $gudangProductsCount);

        $branchData = [
            'name' => 'mobil_test_sync',
            'status' => 'Online',
            'revenue_mtd' => 0,
            'stock_available' => 0,
            'stock_health' => 100,
            'address' => 'Jl. Test No. 99',
            'profit_margin' => 20,
        ];

        $response = $this->post(route('branch.store'), $branchData);
        $response->assertRedirect();

        $newBranch = Branch::where('name', 'mobil_test_sync')->first();
        $this->assertNotNull($newBranch);

        // Verify all Gudang products exist in the new branch with stock 0
        $gudangProducts = Product::whereNull('branch_id')->get();
        foreach ($gudangProducts as $gp) {
            $this->assertDatabaseHas('products', [
                'sku' => $gp->sku,
                'branch_id' => $newBranch->id,
                'final_stock' => 0,
            ]);
        }
    }

    /**
     * Test product deletion restrictions when any branch still has stock.
     */
    public function test_product_deletion_restricted_when_stock_exists(): void
    {
        // Find a product SKU that has stock > 0 in Gudang/branches
        $product = Product::where('final_stock', '>', 0)->first();
        $this->assertNotNull($product);

        $response = $this->delete(route('inventory.destroy', $product->id));
        // Should redirect back with errors
        $response->assertSessionHasErrors(['delete_error']);

        // Verify the product still exists in DB
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
        ]);

        // Update all records of this SKU to 0 stock
        Product::where('sku', $product->sku)->update([
            'initial_stock' => 0,
            'incoming_stock' => 0,
            'final_stock' => 0,
            'sold_stock' => 0,
        ]);

        // Attempt deletion again
        $response2 = $this->delete(route('inventory.destroy', $product->id));
        $response2->assertRedirect();
        $response2->assertSessionHas('success');

        // Verify all records of this SKU are deleted across Gudang and branches
        $this->assertDatabaseMissing('products', [
            'sku' => $product->sku,
        ]);
    }

    /**
     * Test that the branch list displays today's clocked-in agent correctly.
     */
    public function test_branch_list_displays_clocked_in_agent(): void
    {
        $branch = Branch::first();
        $this->assertNotNull($branch);

        // Access the branch index, should see "Belum Absen" initially
        $response = $this->get(route('branch.index'));
        $response->assertStatus(200);
        $response->assertSee('Belum Absen');

        // Clock in an agent today
        \App\Models\Attendance::create([
            'branch_id' => $branch->id,
            'name' => 'Andini (Kasir)',
            'created_at' => now(),
        ]);

        // Access the branch index again, should see the clocked-in agent's name
        $response2 = $this->get(route('branch.index'));
        $response2->assertStatus(200);
        $response2->assertSee('Absen: Andini (Kasir)');
    }
}
