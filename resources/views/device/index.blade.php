@extends('layouts.app')

@section('title', 'Manajemen Device')
@section('subtitle', 'Kelola device mobile yang aktif dan terdaftar untuk setiap Agent ID (Cabang).')

@section('content')

    <!-- Stats Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
        <!-- Card 1: Total Devices -->
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-center gap-4">
            <div class="h-10 w-10 bg-slate-100 rounded-lg flex items-center justify-center text-slate-700">
                <span class="material-symbols-outlined text-xl">devices</span>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Device</p>
                <h3 class="text-xl font-black text-slate-900 mt-0.5">{{ $stats['total'] }}</h3>
            </div>
        </div>

        <!-- Card 2: Active Devices -->
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-center gap-4">
            <div class="h-10 w-10 bg-emerald-50 rounded-lg flex items-center justify-center text-emerald-600">
                <span class="material-symbols-outlined text-xl">phonelink_setup</span>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Device Aktif</p>
                <h3 class="text-xl font-black text-slate-900 mt-0.5">{{ $stats['active'] }}</h3>
            </div>
        </div>

        <!-- Card 3: Nonactive Devices -->
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-center gap-4">
            <div class="h-10 w-10 bg-slate-100 rounded-lg flex items-center justify-center text-slate-500">
                <span class="material-symbols-outlined text-xl">phonelink_erase</span>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Device Nonaktif</p>
                <h3 class="text-xl font-black text-slate-900 mt-0.5">{{ $stats['blocked'] }}</h3>
            </div>
        </div>
    </div>

    <!-- Controls / Search Bar (Premium Banner) -->
    <div class="greeting-banner mb-0 rounded-t-xl rounded-b-none relative z-10" style="padding: 16px 20px;">
        <div class="flex items-center gap-4 w-full">
            <!-- Left Group: Search -->
            <form action="{{ route('device.index') }}" method="GET" class="flex items-center gap-3 flex-1 m-0 p-0" style="position:relative;z-index:1;">
                <div class="relative w-full max-w-[350px] shadow-sm rounded-lg">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-indigo-600 text-sm">search</span>
                    <input type="text" name="search" value="{{ request('search') }}" class="w-full text-xs border border-white/20 rounded-lg pl-9 pr-4 py-2 focus:ring-1 focus:ring-white outline-none transition-all bg-white/95 text-slate-800 font-bold placeholder-slate-400" placeholder="Cari Agent ID, Nama Cabang, Device ID, atau Nama Device...">
                </div>
                <button type="submit" class="px-4 py-2 bg-white/20 border border-white/30 text-white rounded-lg text-xs font-bold hover:bg-white/30 transition-all cursor-pointer backdrop-blur-sm shadow-sm">Cari</button>
                @if(request('search'))
                    <a href="{{ route('device.index') }}" class="px-3 py-2 bg-red-500/80 hover:bg-red-500 text-white border border-red-400/50 text-xs font-bold rounded-lg flex items-center justify-center transition-all cursor-pointer backdrop-blur-sm shadow-sm">Reset</a>
                @endif
            </form>
        </div>
    </div>
    <!-- Main Content Wrapper -->
    <div class="bg-white p-4 sm:p-6 rounded-b-xl border border-slate-200 border-t-0 shadow-sm mb-8" style="margin-top: 0;">


    <!-- Data Table Card -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider w-16">Inisial</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Agent ID</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Cabang</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Device ID (Hardware)</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Device</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Terakhir Aktif</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Aksi (Status)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($devices as $device)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="h-8 w-8 rounded-full bg-slate-900 text-white text-xs font-bold flex items-center justify-center">
                                    {{ strtoupper(substr($device->agent_id, 0, 1)) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900">
                                {{ $device->agent_id }}
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-indigo-650">
                                {{ ucfirst($device->branch_name) }}
                            </td>
                            <td class="px-6 py-4 text-xs font-mono text-slate-600">
                                {{ $device->device_id }}
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-700 font-medium">
                                {{ $device->device_name }}
                            </td>
                            <td class="px-6 py-4 text-slate-650 text-xs font-medium">
                                {{ $device->last_active_at ? $device->last_active_at->translatedFormat('d M Y H:i') : 'Belum aktif' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('device.update', $device->id) }}" method="POST" class="inline-flex items-center gap-2 m-0 p-0 justify-end w-full">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="toggle_status" value="1">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" onchange="this.form.submit()" class="sr-only peer" {{ $device->is_active ? 'checked' : '' }}>
                                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                                        <span class="ml-2 text-xs font-bold @if($device->is_active) text-emerald-600 @else text-slate-400 @endif">
                                            {{ $device->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </label>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-xs font-medium text-slate-400">
                                Tidak ada data device.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>


    </div> <!-- End Main Content Wrapper -->
@endsection
