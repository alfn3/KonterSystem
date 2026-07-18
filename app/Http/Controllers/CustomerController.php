<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $selectedBranchId = $request->query('branch_id');

        $query = Customer::with('branch');

        if (!empty($selectedBranchId)) {
            $query->where('branch_id', $selectedBranchId);
        }

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('name', 'asc')->get();

        // Dynamically compute transaction metrics for each customer based on phone number matching
        $customers->each(function($customer) {
            $stats = \App\Models\Transaction::where('customer_phone', $customer->phone)
                ->selectRaw('count(*) as count, sum(case when status = "Sukses" then total_amount else 0 end) as spent')
                ->first();
            $customer->total_transactions = $stats->count ?? 0;
            $customer->total_spent = (float)($stats->spent ?? 0);
        });

        $branches = Branch::orderBy('name', 'asc')->get();

        return view('customer.index', compact('customers', 'branches', 'search', 'selectedBranchId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:30|unique:customers,phone',
            'branch_id' => 'nullable|exists:branches,id',
            'service_type' => 'nullable|string|max:50',
        ]);

        Customer::create($validated);

        return redirect()->back()->with('success', 'Pelanggan baru berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => [
                'required',
                'string',
                'max:30',
                Rule::unique('customers', 'phone')->ignore($id),
            ],
            'branch_id' => 'nullable|exists:branches,id',
            'service_type' => 'nullable|string|max:50',
        ]);

        $customer->update($validated);

        return redirect()->back()->with('success', 'Data pelanggan berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return redirect()->back()->with('success', 'Pelanggan berhasil dihapus!');
    }
}
