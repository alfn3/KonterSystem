<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Branch;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $query = Employee::query();

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $employees = $query->orderBy('name', 'asc')->get();
        $branches = Branch::orderBy('name', 'asc')->get();

        return view('employee.index', compact('employees', 'branches', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'role' => 'required|string|max:50',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:20',
            'status' => 'required|string|in:Aktif,Nonaktif',
            'home_address' => 'nullable|string',
            'start_date' => 'nullable|date',
        ]);

        Employee::create($validated);

        return redirect()->back()->with('success', 'Karyawan baru berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'role' => 'required|string|max:50',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:20',
            'status' => 'required|string|in:Aktif,Nonaktif',
            'home_address' => 'nullable|string',
            'start_date' => 'nullable|date',
        ]);

        $employee->update($validated);

        return redirect()->back()->with('success', 'Data karyawan berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return redirect()->back()->with('success', 'Karyawan berhasil dihapus!');
    }
}
