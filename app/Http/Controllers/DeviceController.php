<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        $query = Device::query();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('agent_id', 'like', "%{$search}%")
                  ->orWhere('device_id', 'like', "%{$search}%")
                  ->orWhere('device_name', 'like', "%{$search}%")
                  ->orWhereIn('agent_id', function($sub) use ($search) {
                      $sub->select('agent_id')
                          ->from('users')
                          ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $devices = $query->orderBy('agent_id', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total' => Device::count(),
            'active' => Device::where('is_active', true)->count(),
            'blocked' => Device::where('is_active', false)->count(),
        ];

        return view('device.index', compact('devices', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'agent_id' => 'required|string|max:100',
            'device_id' => 'required|string|max:255',
            'device_name' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        // Check unique agent_id + device_id combination
        $exists = Device::where('agent_id', $validated['agent_id'])
            ->where('device_id', $validated['device_id'])
            ->exists();

        if ($exists) {
            return redirect()->back()->withErrors(['device_id' => 'Device ID sudah terdaftar untuk Agent ID ini.'])->withInput();
        }

        Device::create([
            'agent_id' => $validated['agent_id'],
            'device_id' => $validated['device_id'],
            'device_name' => $validated['device_name'] ?: 'Device Baru',
            'is_active' => $validated['is_active'],
        ]);

        return redirect()->back()->with('success', 'Device baru berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $device = Device::findOrFail($id);

        // Allow toggling only is_active if it is the only input
        if ($request->has('toggle_status')) {
            $device->update([
                'is_active' => !$device->is_active
            ]);
            $statusStr = $device->is_active ? 'diaktifkan' : 'dinonaktifkan';
            return redirect()->back()->with('success', "Device berhasil {$statusStr}.");
        }

        $validated = $request->validate([
            'agent_id' => 'required|string|max:100',
            'device_id' => 'required|string|max:255',
            'device_name' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        // Check uniqueness excluding self
        $exists = Device::where('agent_id', $validated['agent_id'])
            ->where('device_id', $validated['device_id'])
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return redirect()->back()->withErrors(['device_id' => 'Device ID sudah terdaftar untuk Agent ID ini.'])->withInput();
        }

        $device->update([
            'agent_id' => $validated['agent_id'],
            'device_id' => $validated['device_id'],
            'device_name' => $validated['device_name'] ?: 'Device Baru',
            'is_active' => $validated['is_active'],
        ]);

        return redirect()->back()->with('success', 'Detail device berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $device = Device::findOrFail($id);
        $device->delete();

        return redirect()->back()->with('success', 'Device berhasil dihapus.');
    }
}
