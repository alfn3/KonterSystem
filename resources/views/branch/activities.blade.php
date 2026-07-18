@extends('layouts.app')

@section('title', 'Manajemen Cabang > ' . $branch->name . ' > Aktivitas')
@section('page_title')
    <a href="{{ route('branch.index') }}" class="hover:underline text-slate-500">Manajemen Cabang</a>
    <span class="text-slate-400 mx-1 font-medium">&gt;</span>
    <a href="{{ route('branch.show', $branch->id) }}" class="hover:underline text-slate-500">{{ $branch->name }}</a>
    <span class="text-slate-400 mx-1 font-medium">&gt;</span>
    <span class="text-slate-900">Aktivitas</span>
@endsection
@section('subtitle', 'Log pergerakan stok, penjualan, pengeluaran, dan audit lengkap.')

@section('content')
<!-- Header Actions & Switcher (Premium Banner) -->
<div class="greeting-banner mb-6 flex-col sm:flex-row items-center justify-between gap-4" style="padding: 16px 20px;">
    <div class="flex items-center gap-3 w-full sm:w-auto" style="position:relative;z-index:1;">
        <a href="{{ route('branch.show', [$branch->id, 'date' => $selectedDate]) }}" class="flex items-center gap-1.5 bg-white/20 border border-white/30 text-white px-3.5 py-2 rounded-lg text-xs font-bold uppercase tracking-wider hover:bg-white/30 transition-all cursor-pointer h-[34px] backdrop-blur-sm shadow-sm">
            <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
        </a>
        <div class="h-5 w-[1px] bg-white/30"></div>
        <div class="flex items-center gap-2">
            <span class="text-xs font-black text-white uppercase tracking-widest">Aktivitas Cabang</span>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ ($branch->status === 'Online' || $branch->status === 'Open') ? 'bg-green-400 text-white border border-green-300' : 'bg-slate-400 text-white border border-slate-300' }} shadow-sm">
                {{ $branch->status }}
            </span>
        </div>
    </div>
    <div class="flex flex-wrap items-center gap-3" style="position:relative;z-index:1;">
        <!-- Date Picker (Pickdate) -->
        <form action="{{ route('branch.activities', $branch->id) }}" method="GET" class="flex items-center gap-1.5 m-0 p-0" id="date-filter-form">
            <input type="hidden" name="search" value="{{ $search }}">
            <input type="hidden" name="type" value="{{ $type }}">
            <div class="relative shadow-sm rounded-lg">
                <input type="date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()" class="bg-white/95 border border-white/20 rounded-lg px-3 py-1.5 text-xs font-bold text-slate-800 outline-none focus:ring-1 focus:ring-white cursor-pointer">
            </div>
        </form>

        <div class="relative shadow-sm rounded-lg">
            <select onchange="window.location.href='/operasional/cabang/' + this.value + '/aktivitas?date={{ $selectedDate }}&search={{ urlencode($search) }}&type={{ urlencode($type) }}'" class="appearance-none bg-white/95 border border-white/20 rounded-lg px-4 py-1.5 pr-10 text-xs font-bold text-slate-800 outline-none focus:ring-1 focus:ring-white cursor-pointer w-44 h-[34px]">
                @foreach($branchesList as $b)
                    <option value="{{ $b->id }}" {{ $b->id == $branch->id ? 'selected' : '' }}>{{ $b->name }}</option>
                @endforeach
            </select>
            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-indigo-600 pointer-events-none text-base">expand_more</span>
        </div>

        <button onclick="window.print()" class="flex items-center gap-2 bg-indigo-50 text-indigo-700 px-4 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider hover:bg-white transition-all shadow-sm cursor-pointer h-[34px]">
            <span class="material-symbols-outlined text-base">print</span> Cetak Log
        </button>

        <button onclick="window.location.reload()" class="flex items-center justify-center bg-white/20 border border-white/30 hover:bg-white/30 text-white p-2 rounded-lg transition-all shadow-sm cursor-pointer h-[34px] backdrop-blur-sm" title="Refresh Data">
            <span class="material-symbols-outlined text-base">refresh</span>
        </button>
    </div>
</div>

<!-- Search & Filter Bar -->
<div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm mb-6">
    <form action="{{ route('branch.activities', $branch->id) }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end m-0 p-0">
        <input type="hidden" name="date" value="{{ $selectedDate }}">
        
        <div class="md:col-span-6 flex flex-col gap-1.5">
            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Cari Aktivitas</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-base">search</span>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama produk, SKU, REFF/SN..." class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-10 pr-4 py-2 text-xs font-semibold text-slate-700 outline-none focus:ring-1 focus:ring-slate-900 h-[38px]">
            </div>
        </div>

        <div class="md:col-span-4 flex flex-col gap-1.5">
            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tipe Aktivitas</label>
            <div class="relative">
                <select name="type" onchange="this.form.submit()" class="w-full appearance-none bg-slate-50 border border-slate-200 rounded-lg pl-4 pr-10 py-2 text-xs font-semibold text-slate-700 outline-none focus:ring-1 focus:ring-slate-900 cursor-pointer h-[38px]">
                    <option value="Semua Tipe" {{ $type === 'Semua Tipe' ? 'selected' : '' }}>Semua Tipe</option>
                    <option value="Penjualan" {{ $type === 'Penjualan' ? 'selected' : '' }}>Penjualan</option>
                    <option value="Restok" {{ $type === 'Restok' ? 'selected' : '' }}>Restok / Update</option>
                    <option value="Koreksi" {{ $type === 'Koreksi' ? 'selected' : '' }}>Koreksi</option>
                    <option value="Keluar" {{ $type === 'Keluar' ? 'selected' : '' }}>Keluar / Pengeluaran</option>
                    <option value="Error" {{ $type === 'Error' ? 'selected' : '' }}>Error / Gagal</option>
                </select>
                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-base">expand_more</span>
            </div>
        </div>

        <div class="md:col-span-2 flex gap-2">
            <button type="submit" class="w-full bg-slate-900 text-white rounded-lg text-xs font-bold uppercase tracking-wider hover:bg-slate-800 transition-all shadow-sm cursor-pointer h-[38px]">
                Filter
            </button>
            <a href="{{ route('branch.activities', $branch->id) }}?date={{ $selectedDate }}" class="flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg px-3 h-[38px]" title="Reset Filter">
                <span class="material-symbols-outlined text-base">restart_alt</span>
            </a>
        </div>
    </form>
</div>

<!-- Log Aktivitas Tabel Lengkap -->
<section class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
    <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-slate-600 text-sm">history_edu</span>
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Seluruh Aktivitas Cabang</h3>
        </div>
        <span class="bg-slate-200 text-slate-800 px-2 py-0.5 rounded text-[10px] font-bold">{{ count($activityLogs) }} Records</span>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-200">
                    <th class="px-6 py-3.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Waktu</th>
                    <th class="px-6 py-3.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Aktivitas</th>
                    <th class="px-6 py-3.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">No Tujuan</th>
                    <th class="px-6 py-3.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pelanggan</th>
                    <th class="px-6 py-3.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Saldo Elektrik</th>
                    <th class="px-6 py-3.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Status</th>
                    <th class="px-6 py-3.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">REFF/SN/KETERANGAN</th>
                    <th class="px-6 py-3.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Nominal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($activityLogs as $log)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-3.5">
                            <p class="text-xs font-bold text-slate-900">{{ $log['time'] }}</p>
                            <p class="text-[9px] font-medium text-slate-400 mt-0.5">{{ $log['date'] }}</p>
                        </td>
                        <td class="px-6 py-3.5">
                            <p class="text-xs font-bold text-slate-900">{{ $log['activity'] }}</p>
                            <p class="text-[10px] text-slate-500 font-medium mt-0.5">{{ $log['detail'] }}</p>
                        </td>
                        <td class="px-6 py-3.5">
                            @if(isset($log['destination_number']) && $log['destination_number'] && $log['destination_number'] !== '-')
                                <p class="text-xs font-mono font-bold text-slate-900">{{ $log['destination_number'] }}</p>
                            @else
                                <p class="text-xs text-slate-400 font-medium">-</p>
                            @endif
                        </td>
                        <td class="px-6 py-3.5">
                            @if(isset($log['customer_phone_label']) && $log['customer_phone_label'] && $log['customer_phone_label'] !== '-')
                                <p class="text-xs font-bold text-slate-900">{{ $log['customer_phone_label'] }}</p>
                                @if(isset($log['customer_phone']) && $log['customer_phone'] && $log['customer_phone'] !== '-' && $log['customer_phone'] !== $log['customer_phone_label'])
                                    <p class="text-[10px] font-mono font-medium text-slate-500 mt-0.5">{{ $log['customer_phone'] }}</p>
                                @endif
                                @if(!empty($log['payment_info']))
                                    <p class="text-[9px] text-slate-500 font-medium mt-0.5">{{ $log['payment_info'] }}</p>
                                @endif
                            @elseif(isset($log['customer_phone']) && $log['customer_phone'] && $log['customer_phone'] !== '-')
                                <p class="text-xs font-mono font-bold text-slate-900">{{ $log['customer_phone'] }}</p>
                                @if(!empty($log['payment_info']))
                                    <p class="text-[9px] text-slate-500 font-medium mt-0.5">{{ $log['payment_info'] }}</p>
                                @endif
                            @else
                                <p class="text-xs text-slate-400 font-medium">-</p>
                            @endif
                        </td>
                        <td class="px-6 py-3.5">
                            @if(isset($log['saldo_elektrik_remaining']) && $log['saldo_elektrik_remaining'] !== null)
                                <p class="text-xs font-semibold text-slate-700">Rp {{ number_format($log['saldo_elektrik_remaining'], 0, ',', '.') }}</p>
                            @else
                                <p class="text-xs text-slate-400 font-medium">-</p>
                            @endif
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider {{ $log['status_class'] }} border">
                                {{ $log['status'] }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5">
                            <p class="text-xs font-mono font-medium text-slate-600">{{ $log['ref'] }}</p>
                        </td>
                        <td class="px-6 py-3.5 text-right">
                            <p class="text-xs font-bold {{ $log['type'] === 'Keluar' || $log['type'] === 'Error' ? 'text-commander-error' : 'text-slate-900' }}">{{ $log['nominal'] }}</p>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-xs text-slate-400 font-semibold uppercase tracking-wider">
                            Tidak ada data aktivitas yang sesuai filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
