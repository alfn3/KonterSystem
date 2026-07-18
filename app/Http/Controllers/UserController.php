<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('email', 'like', '%' . $searchTerm . '%')
                  ->orWhere('whatsapp', 'like', '%' . $searchTerm . '%')
                  ->orWhere('agent_id', 'like', '%' . $searchTerm . '%');
            });
        }

        $users = $query->orderBy('name', 'asc')->get();
        $branches = \App\Models\Branch::orderBy('name', 'asc')->get();

        return view('user.index', compact('users', 'branches'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $loginAccess = $request->input('login_access');
        if (!$loginAccess) {
            if (!$request->filled('email') && !$request->filled('whatsapp')) {
                $request->validate([
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                    'whatsapp' => ['required', 'string', 'max:255', 'unique:users'],
                ]);
            }
            $loginAccess = $request->filled('email') ? 'web' : 'mobile';
        }

        if ($loginAccess === 'web') {
            $request->merge(['whatsapp' => null, 'agent_id' => null]);
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);
        } else {
            $request->merge(['email' => null, 'password' => null, 'password_confirmation' => null]);
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'whatsapp' => ['required', 'string', 'max:255', 'unique:users'],
                'agent_id' => ['required', 'string', 'max:255', 'unique:users'],
            ]);
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password ? Hash::make($request->password) : null,
            'whatsapp' => $request->whatsapp,
            'agent_id' => $request->agent_id,
            'is_active' => true,
        ]);

        return redirect()->route('user.index')->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        if ($request->has('toggle_status')) {
            if ($user->id === Auth::id()) {
                return redirect()->back()->withErrors(['error' => 'Anda tidak dapat menonaktifkan akun Anda sendiri.']);
            }
            $user->update([
                'is_active' => !$user->is_active
            ]);
            $statusStr = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
            return redirect()->back()->with('success', "User berhasil {$statusStr}.");
        }

        $loginAccess = $request->input('login_access');
        if (!$loginAccess) {
            if (!$request->filled('email') && !$request->filled('whatsapp')) {
                $request->validate([
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
                    'whatsapp' => ['required', 'string', 'max:255', 'unique:users,whatsapp,' . $user->id],
                ]);
            }
            $loginAccess = $request->filled('email') ? 'web' : 'mobile';
        }

        if ($loginAccess === 'web') {
            $request->merge(['whatsapp' => null, 'agent_id' => null]);
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
                'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            ]);
        } else {
            $request->merge(['email' => null, 'password' => null, 'password_confirmation' => null]);
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'whatsapp' => ['required', 'string', 'max:255', 'unique:users,whatsapp,' . $user->id],
                'agent_id' => ['required', 'string', 'max:255', 'unique:users,agent_id,' . $user->id],
            ]);
        }

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'whatsapp' => $request->whatsapp,
            'agent_id' => $request->agent_id,
        ];

        if ($loginAccess === 'web') {
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }
        } else {
            $userData['password'] = null;
        }

        $user->update($userData);

        return redirect()->route('user.index')->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return redirect()->back()->withErrors(['error' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
        }

        $user->delete();

        return redirect()->route('user.index')->with('success', 'User berhasil dihapus.');
    }
}
