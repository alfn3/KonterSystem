@extends('layouts.app')

@section('title', 'Manajemen Cabang > ' . $branch->name)
@section('page_title')
    <a href="{{ route('branch.index') }}" class="hover:underline text-slate-500">Manajemen Cabang</a>
    <span class="text-slate-400 mx-1 font-medium">&gt;</span>
    <span class="text-slate-900">{{ $branch->name }}</span>
    <span class="text-xs font-semibold text-slate-500 ml-3 bg-slate-100 px-2 py-1 rounded border border-slate-200">Agent ID: {{ $branch->agent_id ?: '-' }}</span>

@endsection
@section('subtitle', 'Detail performa, inventoris, dan log aktivitas real-time.')

@section('content')
@php
    $bgGradient = 'linear-gradient(135deg, #6366f1 0%, #4338ca 100%)'; // konsisten biru (indigo)
    $boxShadow = '0 4px 20px rgba(99,102,241,0.25)';
    $iconColor = '#4f46e5';
    // Card bawah disamarkan (lebih subtle / clean seperti desain global)
    $cardBg = '#ffffff'; 
    $borderColor = '#e2e8f0'; 
    $hoverBorderColor = '#cbd5e1'; 
    $headerBg = '#f8fafc'; 
@endphp
<!-- Header Actions & Switcher -->
<div class="greeting-banner mt-6 flex-col sm:flex-row items-center justify-between gap-4 rounded-t-xl relative z-10" style="background: {{ $bgGradient }}; box-shadow: {{ $boxShadow }}; padding: 16px 20px;">
    <div class="flex flex-wrap items-center justify-between gap-4 w-full" style="position:relative;z-index:1;">
        
        <!-- Left: Title, Address, and Actions -->
        <div class="flex items-center gap-3">
            <h2 class="text-xl font-black text-white">{{ $branch->name }}</h2>
            <div class="flex items-center gap-1 text-[11px] text-white/90 bg-white/10 border border-white/20 px-2.5 py-1.5 rounded-lg backdrop-blur-sm">
                <span class="material-symbols-outlined text-[14px]">location_on</span>
                <span class="font-bold">{{ $branch->address }}</span>
            </div>

            <!-- Edit Cabang Button -->
            <button onclick="openEditBranchModal({{ json_encode($branch) }})" class="flex items-center gap-1 bg-white border border-white/20 text-slate-800 px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider hover:bg-slate-50 transition-all shadow-sm cursor-pointer h-[32px] ml-2" title="Edit Cabang">
                <span class="material-symbols-outlined text-[14px]" style="color:{{$iconColor}}">edit</span> Edit
            </button>
        </div>
        
        <!-- Right: All Controls & Actions -->
        <div class="flex flex-wrap items-center gap-2">
            
            <!-- Date Picker (Pickdate) -->
            <form action="{{ route('branch.show', $branch->id) }}" method="GET" class="flex items-center m-0 p-0" id="date-filter-form" onsubmit="event.preventDefault(); fetchBranchData(this.action + '?date=' + this.date.value);">
                <input type="date" name="date" value="{{ $selectedDate }}" onchange="fetchBranchData('{{ route('branch.show', $branch->id) }}?date=' + this.value);" class="bg-white/95 border border-white/20 rounded-lg px-2.5 py-1.5 text-[11px] font-bold text-slate-800 outline-none focus:ring-1 focus:ring-white cursor-pointer h-[32px] shadow-sm">
            </form>

            <!-- Branch Switcher -->
            <div class="relative shadow-sm rounded-lg">
                <select onchange="fetchBranchData('/operasional/cabang/' + this.value + '?date={{ $selectedDate }}');" class="appearance-none bg-white/95 border border-white/20 rounded-lg px-3 py-1.5 pr-8 text-[11px] font-bold text-slate-800 outline-none focus:ring-1 focus:ring-white cursor-pointer w-32 h-[32px]">
                    @foreach($branchesList as $b)
                        <option value="{{ $b->id }}" {{ $b->id == $branch->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
                <span class="material-symbols-outlined absolute right-2 top-1/2 -translate-y-1/2 text-[14px]" style="color:{{$iconColor}}; pointer-events:none;">expand_more</span>
            </div>

            <!-- Refresh Button -->
            <button onclick="fetchBranchData(window.location.href);" class="flex items-center justify-center bg-white/20 hover:bg-white/30 border border-white/30 text-white w-[32px] h-[32px] rounded-lg transition-all shadow-sm cursor-pointer backdrop-blur-sm" title="Refresh Data">
                <span id="refresh-icon" class="material-symbols-outlined text-[16px]">refresh</span>
            </button>

            <!-- Divider -->
            <div class="h-5 w-px bg-white/30 mx-1"></div>

            <!-- Status Badge -->
            <div class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg {{ ($branch->status === 'Online' || $branch->status === 'Open') ? 'bg-white text-green-700 border border-white/20' : 'bg-white text-slate-600 border border-white/20' }} shadow-sm h-[32px]">
                <span class="w-1.5 h-1.5 rounded-full {{ ($branch->status === 'Online' || $branch->status === 'Open') ? 'bg-green-500' : 'bg-slate-400' }}"></span>
                <span class="text-[10px] font-bold uppercase tracking-wider">{{ $branch->status }}</span>
                @if($branch->status === 'Offline' && $branch->last_active_at)
                    <span class="text-[9px] text-slate-400 font-semibold normal-case ml-1">
                        ({{ $branch->last_active_at->diffForHumans() }})
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Main Content Wrapper -->
<div id="dashboard-content" class="bg-white p-4 sm:p-6 rounded-b-xl border border-slate-200 border-t-0 shadow-sm mb-8" style="margin-top: 0;">
    <!-- Summary Section -->
    <section class="space-y-4 mb-8">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <!-- Total Penjualan -->
    <div class="rounded-xl p-4 shadow-sm group relative h-[125px] flex items-center" style="background-color: {{ $cardBg }}; border: 1px solid {{ $borderColor }};">
        <div class="absolute top-4 left-4 h-8 flex items-center">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Penjualan</span>
        </div>
        <div class="absolute top-4 right-4 w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
            <span class="material-symbols-outlined text-base">point_of_sale</span>
        </div>
        <div>
            <h3 class="{{ $totalPenjualan === 'belum ada penjualan' ? 'text-xl font-black text-slate-400' : 'text-xl font-black text-slate-800' }} leading-none">
                {{ $totalPenjualan === 'belum ada penjualan' ? 'Rp 0' : $totalPenjualan }}
            </h3>
        </div>
    </div>
    
    <!-- Total Pelanggan -->
    <div class="rounded-xl p-4 shadow-sm group relative h-[125px] flex items-center" style="background-color: {{ $cardBg }}; border: 1px solid {{ $borderColor }};">
        <div class="absolute top-4 left-4 h-8 flex items-center">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Pelanggan</span>
        </div>
        <div class="absolute top-4 right-4 w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600">
            <span class="material-symbols-outlined text-base">groups</span>
        </div>
        <div class="flex items-baseline gap-1">
            <h3 class="{{ $totalPelanggan === 'belum ada pelanggan' ? 'text-xl font-black text-slate-400' : 'text-xl font-black text-slate-800' }} leading-none">
                {{ $totalPelanggan === 'belum ada pelanggan' ? '0' : $totalPelanggan }}
            </h3>
            <span class="text-xs font-bold text-slate-400">Orang</span>
        </div>
    </div>

    <!-- Saldo Elektrik -->
    <div class="rounded-xl p-4 shadow-sm hover:shadow-md transition-all group relative h-[125px] flex items-center" style="background-color: {{ $cardBg }}; border: 1px solid {{ $saldoElektrikVal < 1000000 ? 'rgba(239,68,68,0.5)' : $borderColor }};">
        <div class="absolute top-4 left-4 h-8 flex items-center">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Saldo Elektrik</span>
        </div>
        <div class="absolute top-4 right-4 w-8 h-8 rounded-lg {{ $saldoElektrikVal < 1000000 ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center">
            <span class="material-symbols-outlined text-base">account_balance_wallet</span>
        </div>
        <div class="flex items-center gap-2">
            <h3 class="text-xl font-black {{ $saldoElektrikVal < 1000000 ? 'text-red-600' : 'text-slate-800' }} leading-none">{{ $saldoElektrik }}</h3>
            <span class="material-symbols-outlined text-[10px] {{ $saldoElektrikVal < 1000000 ? 'text-red-500' : 'text-emerald-500' }}" style="font-variation-settings: 'FILL' 1">circle</span>
        </div>
        <div class="absolute bottom-4 right-4">
            <span class="bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded text-[8px] font-bold uppercase tracking-wider">
                Terpakai: <span class="text-slate-700">{{ $saldoElektrikTerpakai }}</span>
            </span>
        </div>
    </div>

    <!-- Selisih -->
    <div class="rounded-xl p-4 shadow-sm hover:shadow-md transition-all group relative h-[125px] flex items-center" style="background-color: {{ $cardBg }}; border: 1px solid {{ $selisihVal < 0 ? 'rgba(239,68,68,0.5)' : $borderColor }};">
        <div class="absolute top-4 left-4 h-8 flex items-center">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Selisih Kas</span>
        </div>
        <div class="absolute top-4 right-4 w-8 h-8 rounded-lg {{ $selisihVal < 0 ? 'bg-red-50 text-red-600' : 'bg-slate-50 text-slate-500' }} flex items-center justify-center">
            <span class="material-symbols-outlined text-base">receipt_long</span>
        </div>
        <div>
            <h3 class="text-xl font-black {{ $selisihVal < 0 ? 'text-red-600' : 'text-slate-800' }} leading-none">{{ $selisih }}</h3>
        </div>
    </div>
    </div>
</section>

<!-- Main Grid: Main Content & Sidebar -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    <!-- Main Content (Left, 2 columns on XL) -->
    <div class="xl:col-span-2 space-y-6">
<!-- Rincian Stok Produk -->
<section class="rounded-xl overflow-hidden shadow-sm" style="background-color: {{ $cardBg }}; border: 1px solid {{ $borderColor }};">
    <div class="px-6 py-4 flex items-center gap-2" style="background-color: {{ $headerBg }}; border-bottom: 1px solid {{ $borderColor }};">
        <span class="material-symbols-outlined text-slate-600 text-sm">inventory_2</span>
        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Rincian Stok Produk</h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse" id="product-table">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-200">
                    <th class="px-6 py-3.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Produk</th>
                    <th class="px-6 py-3.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Stok Awal</th>
                    <th class="px-6 py-3.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Top Up (+)</th>
                    <th class="px-6 py-3.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Terjual (-)</th>
                    <th class="px-6 py-3.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Sisa Stok</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse(collect($products)->take(5) as $product)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-slate-900">{{ $product->brand }} {{ $product->name }}</span>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[10px] font-mono text-slate-400">{{ $product->sku }}</span>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase bg-slate-100 px-1.5 py-0.5 rounded">{{ $product->category }}</span>
                                    @if($product->final_stock <= 5 && $product->final_stock > 0)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[8px] font-bold bg-amber-100 text-amber-700 uppercase tracking-wider text-center">Stok Tipis</span>
                                    @elseif($product->final_stock == 0)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[8px] font-bold bg-red-100 text-red-700 uppercase tracking-wider text-center">Habis</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center text-xs font-medium text-slate-600">
                            {{ $product->initial_stock }}
                        </td>
                        <td class="px-6 py-4 text-center text-xs font-bold text-green-600">
                            {{ $product->incoming_stock > 0 ? '+' . $product->incoming_stock : '-' }}
                        </td>
                        <td class="px-6 py-4 text-center text-xs font-bold text-commander-error">
                            {{ $product->sold_stock > 0 ? $product->sold_stock : '-' }}
                        </td>
                        <td class="px-6 py-4 text-center text-xs font-bold text-slate-900">
                            {{ $product->final_stock }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-xs text-slate-400 font-semibold uppercase">
                            Tidak ada produk di cabang ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 flex justify-end" style="border-top: 1px solid {{ $borderColor }}; background-color: {{ $headerBg }};">
        <a href="{{ route('inventory.index', ['branch' => $branch->name, 'date' => $selectedDate]) }}" class="text-xs font-bold uppercase tracking-wider text-slate-900 hover:text-slate-700 flex items-center gap-1.5">
            Lihat Semua Stok <span class="material-symbols-outlined text-sm font-bold">arrow_forward</span>
        </a>
    </div>
</section>


<section class="rounded-xl overflow-hidden shadow-sm" style="background-color: {{ $cardBg }}; border: 1px solid {{ $borderColor }};">
            <div>
                <div class="px-6 py-4 flex items-center justify-between" style="background-color: {{ $headerBg }}; border-bottom: 1px solid {{ $borderColor }};">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-slate-600 text-sm">history_edu</span>
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Log Aktivitas Terbaru</h3>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-200">
                                <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Waktu</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Aktivitas</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">No Tujuan</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pelanggan</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Saldo Elektrik</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Status</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">REFF/SN/KETERANGAN</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @php
                                $logs = collect($limitedActivityLogs)->take(5);
                                $emptyCount = 5 - $logs->count();
                            @endphp
                            @foreach($logs as $log)
                                <tr class="hover:bg-slate-50 transition-colors h-[64px]">
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
                            @endforeach

                            @for($i = 0; $i < $emptyCount; $i++)
                                <tr class="h-[64px]">
                                    @if($logs->isEmpty() && $i === 2)
                                        <td colspan="8" class="px-6 py-4 text-center text-xs text-slate-400 font-semibold uppercase">
                                            Tidak ada aktivitas hari ini.
                                        </td>
                                    @else
                                        <td colspan="8" class="px-6 py-4"></td>
                                    @endif
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="p-4 flex justify-between items-center" style="border-top: 1px solid {{ $borderColor }}; background-color: {{ $headerBg }};">
                <span class="text-xs text-slate-500">Real-time update dari cabang {{ $branch->name }}</span>
                <a href="{{ route('branch.activities', [$branch->id, 'date' => $selectedDate]) }}" class="text-xs font-bold uppercase tracking-wider text-slate-900 hover:text-slate-700 flex items-center gap-1.5">
                    Lihat Detail Aktivitas <span class="material-symbols-outlined text-sm font-bold">arrow_forward</span>
                </a>
            </div>
        </section>
    </div>

    <!-- Sidebar (Right, 1 column on XL) -->
    <div class="space-y-6">
<section class="rounded-xl overflow-hidden shadow-sm" style="background-color: {{ $cardBg }}; border: 1px solid {{ $borderColor }};">
            <div class="px-6 py-4 flex items-center justify-between" style="background-color: {{ $headerBg }}; border-bottom: 1px solid {{ $borderColor }};">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-600 text-sm">schedule</span>
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Agent Activity</h3>
                </div>
            </div>
            <div class="p-6">
                <div class="relative space-y-8 before:absolute before:inset-0 before:ml-4 before:-translate-x-px before:h-full before:w-0.5 before:bg-slate-100">
                    @foreach($timeline as $item)
                        <div class="relative flex items-center gap-6 {{ $item['opacity'] ?? '' }}">
                            <div class="absolute left-0 w-8 h-8 rounded-full {{ $item['bg'] }} border-4 border-white shadow-sm flex items-center justify-center">
                                <span class="material-symbols-outlined text-white text-xs font-bold">{{ $item['icon'] }}</span>
                            </div>
                            <div class="ml-10">
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">{{ $item['time'] }}</p>
                                <h4 class="text-xs font-bold text-slate-900">{{ $item['title'] }}</h4>
                                <p class="text-[11px] text-slate-500 font-medium mt-0.5">{{ $item['desc'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        
        
<!-- Rincian Saldo Laci -->
    <div class="rounded-xl overflow-hidden shadow-sm flex flex-col justify-between" style="background-color: {{ $cardBg }}; border: 1px solid {{ $borderColor }};">
        <div>
            <div class="px-6 py-4 flex items-center gap-2" style="background-color: {{ $headerBg }}; border-bottom: 1px solid {{ $borderColor }};">
                <span class="material-symbols-outlined text-slate-600 text-sm">payments</span>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Rincian Saldo Laci</h3>
            </div>
            @if($denominasi['has_data'])
                <div class="p-6 space-y-3">
                    <!-- Kelompok Bendel -->
                    <div class="flex justify-between items-center text-xs font-bold text-slate-700">
                        <span>Bendel 100k / 50k</span>
                        <span>{{ $denominasi['bendel_large'] }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs font-bold text-slate-700">
                        <span>Bendel 20k / 10k / 5k</span>
                        <span>{{ $denominasi['bendel_medium'] }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs font-bold text-slate-700">
                        <span>Bendel 2k / 1k</span>
                        <span>{{ $denominasi['bendel_small'] }}</span>
                    </div>
                    
                    <div class="border-t border-slate-100 my-2 pt-2"></div>
                    
                    <!-- Pecahan Rinci -->
                    <div class="flex justify-between items-center text-xs font-medium text-slate-600">
                        <span>Pecahan 100k</span>
                        <span class="text-slate-950 font-bold">{{ $denominasi['100k'] }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs font-medium text-slate-600">
                        <span>Pecahan 50k</span>
                        <span class="text-slate-900 font-bold">{{ $denominasi['50k'] }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs font-medium text-slate-600">
                        <span>Pecahan 20k</span>
                        <span class="text-slate-900 font-bold">{{ $denominasi['20k'] }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs font-medium text-slate-600">
                        <span>Pecahan 10k</span>
                        <span class="text-slate-900 font-bold">{{ $denominasi['10k'] }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs font-medium text-slate-600">
                        <span>Pecahan 5k</span>
                        <span class="text-slate-900 font-bold">{{ $denominasi['5k'] }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs font-medium text-slate-600">
                        <span>Pecahan 2k</span>
                        <span class="text-slate-900 font-bold">{{ $denominasi['2k'] }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs font-medium text-slate-600">
                        <span>Pecahan 1k</span>
                        <span class="text-slate-900 font-bold">{{ $denominasi['1k'] }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs font-medium text-slate-600 border-t border-slate-100 pt-2">
                        <span>Koin</span>
                        <span class="text-slate-900 font-bold">{{ $denominasi['koin'] }}</span>
                    </div>
                </div>
            @else
                <div class="p-6 py-12 flex flex-col items-center justify-center text-center">
                    <span class="material-symbols-outlined text-slate-300 text-4xl mb-2">money_off</span>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">belum ada data</p>
                </div>
            @endif
        </div>
        @if($denominasi['has_data'])
            <div class="p-5 flex justify-between items-center" style="border-top: 1px solid {{ $borderColor }}; background-color: {{ $headerBg }};">
                <span class="text-sm font-bold text-slate-900">Total Cash</span>
                <span class="text-sm font-extrabold text-slate-900">{{ $denominasi['total'] }}</span>
            </div>
        @endif
    </div>

    
<!-- Detail Pengeluaran -->
    <div class="rounded-xl overflow-hidden shadow-sm flex flex-col justify-between" style="background-color: {{ $cardBg }}; border: 1px solid {{ $borderColor }};">
        <div>
            <div class="px-6 py-4 flex items-center justify-between" style="background-color: {{ $headerBg }}; border-bottom: 1px solid {{ $borderColor }};">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-600 text-sm">receipt_long</span>
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Detail Pengeluaran</h3>
                </div>
            </div>
            <div class="p-6 space-y-4">
                @foreach($pengeluaranList as $expense)
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <span class="text-[10px] font-bold text-slate-400 font-mono mt-0.5">{{ $expense['time'] }}</span>
                            <div class="flex items-center justify-center h-6 w-6 rounded bg-slate-50 border border-slate-200">
                                <span class="material-symbols-outlined text-slate-500 text-sm">{{ $expense['icon'] }}</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs text-slate-900 font-bold">{{ $expense['name'] }}</span>
                                <span class="text-[10px] text-slate-400 font-medium mt-0.5">{{ $expense['note'] }}</span>
                            </div>
                        </div>
                        <span class="text-xs text-slate-900 font-bold whitespace-nowrap">Rp {{ number_format($expense['amount'], 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="p-5 flex justify-between items-center" style="border-top: 1px solid {{ $borderColor }}; background-color: {{ $headerBg }};">
            <span class="text-sm font-bold text-commander-error">Total Pengeluaran</span>
            <span class="text-sm font-extrabold text-commander-error">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</span>
        </div>
    </div>

<div class="bg-slate-900 p-6 rounded-xl text-white relative overflow-hidden shadow-sm">
            <div class="relative z-10">
                <h4 class="text-sm font-bold uppercase tracking-wider mb-2">Audit Quick Check</h4>
                <p class="text-xs text-slate-400 mb-6 leading-relaxed">Bandingkan saldo kas laci tercatat dengan records settlement digital secara instan.</p>
                <a href="{{ route('audit.index') }}" class="block text-center w-full py-2.5 bg-white text-slate-900 rounded-lg text-xs font-bold uppercase tracking-wider hover:bg-slate-100 transition-colors shadow">Mulai Audit Sekarang</a>
            </div>
            <div class="absolute -right-4 -bottom-4 opacity-10">
                <span class="material-symbols-outlined text-[120px]">verified_user</span>
            </div>
        </div>
    </div>
</div>
</div> <!-- End Main Content Wrapper -->

<!-- Skeleton Loading (Hidden by Default) -->
<div id="dashboard-skeleton" class="hidden bg-white p-4 sm:p-6 rounded-b-xl border border-slate-200 border-t-0 shadow-sm mb-8" style="margin-top: 0;">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 animate-pulse">
        @for($i = 0; $i < 4; $i++)
        <div class="rounded-xl p-4 border border-slate-100 bg-slate-50/50 h-[125px] flex flex-col justify-center">
            <div class="h-3 bg-slate-200/60 rounded w-1/3 mb-4"></div>
            <div class="h-6 bg-slate-200/60 rounded w-2/3"></div>
        </div>
        @endfor
    </div>
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 animate-pulse">
        <div class="xl:col-span-2 space-y-6">
            <div class="h-[250px] bg-slate-50/50 rounded-xl border border-slate-100"></div>
            <div class="h-[350px] bg-slate-50/50 rounded-xl border border-slate-100"></div>
        </div>
        <div class="space-y-6">
            <div class="h-[200px] bg-slate-50/50 rounded-xl border border-slate-100"></div>
            <div class="h-[250px] bg-slate-50/50 rounded-xl border border-slate-100"></div>
            <div class="h-[150px] bg-slate-50/50 rounded-xl border border-slate-100"></div>
        </div>
    </div>
</div>

@endsection

<!-- Edit Branch Modal -->
<div id="edit-branch-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[999] flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-slate-500">edit_road</span>
                Edit Informasi Cabang
            </h3>
            <button onclick="closeEditBranchModal()" class="material-symbols-outlined text-slate-400 hover:text-slate-600 cursor-pointer border-none bg-transparent outline-none">close</button>
        </div>
        <div class="p-6 space-y-4">
            <form id="edit-branch-form" action="" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Nama Konter / Cabang</label>
                    <input type="text" name="name" id="edit_branch_name" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Agent ID</label>
                    <input type="text" name="agent_id" id="edit_branch_agent_id" placeholder="e.g. operator1 (opsional)" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Alamat Cabang</label>
                    <input type="text" name="address" id="edit_branch_address" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                </div>
            </form>

            <div class="pt-4 border-t border-slate-100 flex justify-between items-center mt-2">
                <!-- Hapus Cabang Form -->
                <form action="{{ route('branch.destroy', $branch->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus cabang {{ $branch->name }}?')" class="m-0 p-0 inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="flex items-center gap-1.5 bg-red-50 border border-red-200 text-red-600 px-4 py-2 rounded-lg text-[11px] font-bold uppercase tracking-wider hover:bg-red-100 transition-all shadow-sm cursor-pointer">
                        <span class="material-symbols-outlined text-sm">delete</span> Hapus Cabang
                    </button>
                </form>

                <div class="flex gap-3">
                    <button type="button" onclick="closeEditBranchModal()" class="px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-lg text-xs font-bold uppercase cursor-pointer transition-all">Batal</button>
                    <button type="submit" form="edit-branch-form" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-bold uppercase cursor-pointer shadow-sm transition-all">Perbarui</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showSkeletonAndNavigate() {
        document.getElementById('dashboard-content').classList.add('hidden');
        document.getElementById('dashboard-skeleton').classList.remove('hidden');
        
        const refreshIcon = document.getElementById('refresh-icon');
        if (refreshIcon) refreshIcon.classList.add('animate-[spin_1s_linear_infinite]');
    }

    function fetchBranchData(url) {
        showSkeletonAndNavigate();
        fetch(url)
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Update Main Content
                const newContent = doc.getElementById('dashboard-content');
                if (newContent) {
                    document.getElementById('dashboard-content').innerHTML = newContent.innerHTML;
                }
                
                // Update Banner/Header specifically so date/branch dropdowns stay in sync
                const newBanner = doc.querySelector('.greeting-banner');
                if (newBanner) {
                    document.querySelector('.greeting-banner').innerHTML = newBanner.innerHTML;
                }
                
                // Update Page Title
                document.title = doc.title;
                const newPageHeader = doc.querySelector('.page-header');
                if (newPageHeader) {
                    const currentPageHeader = document.querySelector('.page-header');
                    if (currentPageHeader) {
                        currentPageHeader.innerHTML = newPageHeader.innerHTML;
                    }
                }
                
                window.history.pushState({path: url}, '', url);
                window.customRestoreLoading();
            })
            .catch(err => {
                console.error('AJAX fetch failed:', err);
                window.location.href = url; // Fallback to full reload
            });
    }

    function openEditBranchModal(branch) {
        document.getElementById('edit_branch_name').value = branch.name;
        document.getElementById('edit_branch_agent_id').value = branch.agent_id || '';
        document.getElementById('edit_branch_address').value = branch.address;
        
        const form = document.getElementById('edit-branch-form');
        form.action = `/operasional/cabang/${branch.id}`;
        
        document.getElementById('edit-branch-modal').classList.remove('hidden');
    }

    function closeEditBranchModal() {
        document.getElementById('edit-branch-modal').classList.add('hidden');
    }

    window.customTriggerLoading = function() {
        showSkeletonAndNavigate();
    };

    window.customRestoreLoading = function() {
        document.getElementById('dashboard-content').classList.remove('hidden');
        document.getElementById('dashboard-skeleton').classList.add('hidden');
        const refreshIcon = document.getElementById('refresh-icon');
        if (refreshIcon) refreshIcon.classList.remove('animate-[spin_1s_linear_infinite]');
    };
</script>
@endpush
