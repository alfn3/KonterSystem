@extends('layouts.app')

@section('title', 'Riwayat Pergerakan Stok')
@section('subtitle', 'Monitor aktivitas stok real-time di seluruh outlet Anda.')

@section('content')


    <!-- Filter Controls (Premium Banner) -->
    <div class="greeting-banner mb-6" style="padding: 20px;">
        <form action="{{ route('inventory.history') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end m-0 p-0 w-full" style="position:relative;z-index:1;">
            <div class="md:col-span-4">
                <label class="block text-[10px] font-extrabold text-white/80 uppercase tracking-widest mb-1.5">Cari Transaksi</label>
                <div class="relative shadow-sm rounded-lg">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-indigo-600 text-sm">search</span>
                    <input type="text" name="search" value="{{ request('search') }}" class="w-full pl-9 pr-4 py-2 bg-white/95 border border-white/20 rounded-lg text-xs font-bold text-slate-800 outline-none focus:ring-1 focus:ring-white placeholder-slate-400" placeholder="Cari SN / Reff / Produk...">
                </div>
            </div>
            
            <div class="md:col-span-4">
                <label class="block text-[10px] font-extrabold text-white/80 uppercase tracking-widest mb-1.5">Tipe Pergerakan</label>
                <div class="relative shadow-sm rounded-lg">
                    <select name="type" onchange="this.form.submit()" class="appearance-none w-full px-3 py-2 pr-8 bg-white/95 border border-white/20 rounded-lg text-xs font-bold text-slate-800 outline-none focus:ring-1 focus:ring-white cursor-pointer h-[34px]">
                        <option value="Semua Tipe" {{ request('type') == 'Semua Tipe' ? 'selected' : '' }}>Semua Tipe</option>
                        <option value="Restok" {{ request('type') == 'Restok' ? 'selected' : '' }}>Restok</option>
                        <option value="Penjualan" {{ request('type') == 'Penjualan' ? 'selected' : '' }}>Penjualan</option>
                        <option value="Koreksi" {{ request('type') == 'Koreksi' ? 'selected' : '' }}>Koreksi</option>
                        <option value="Mutasi" {{ request('type') == 'Mutasi' ? 'selected' : '' }}>Mutasi</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-2 top-1/2 -translate-y-1/2 text-indigo-600 pointer-events-none text-base">expand_more</span>
                </div>
            </div>

            <div class="md:col-span-3">
                <label class="block text-[10px] font-extrabold text-white/80 uppercase tracking-widest mb-1.5">Tanggal</label>
                <div class="relative shadow-sm rounded-lg">
                    <input type="date" name="date" value="{{ request('date') }}" onchange="this.form.submit()" class="w-full px-3 py-1.5 bg-white/95 border border-white/20 rounded-lg text-xs font-bold text-slate-800 outline-none focus:ring-1 focus:ring-white cursor-pointer h-[34px]">
                </div>
            </div>

            <div class="md:col-span-1">
                <button type="button" onclick="window.location.reload()" class="w-full flex items-center justify-center bg-white/20 hover:bg-white/30 border border-white/30 text-white rounded-lg p-2 transition-all shadow-sm cursor-pointer h-[34px] backdrop-blur-sm" title="Refresh Data">
                    <span class="material-symbols-outlined text-base">refresh</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-[11px]">Waktu</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-[11px]">Outlet</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-[11px]">Tipe Pergerakan</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-[11px]">Nama Produk</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-[11px] text-right">Perubahan</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-[11px] text-right">Stok Akhir</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-[11px]">SN / Reff</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-[11px]">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($movements as $m)
                        @php
                            $catClass = 'bg-blue-50 text-blue-700 border-blue-100';
                            if ($m->product_category === 'Voucher') {
                                $catClass = 'bg-purple-50 text-purple-700 border-purple-100';
                            } elseif ($m->product_category === 'Aksesoris') {
                                $catClass = 'bg-amber-50 text-amber-700 border-amber-100';
                            }

                            // Movement Type Badge styling
                            $typeClass = 'bg-slate-50 text-slate-700 border-slate-250';
                            if ($m->type === 'Restok') {
                                $typeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                            } elseif ($m->type === 'Mutasi') {
                                $typeClass = 'bg-indigo-50 text-indigo-750 border-indigo-200';
                            } elseif ($m->type === 'Koreksi') {
                                $typeClass = 'bg-amber-50 text-amber-700 border-amber-200';
                            } elseif ($m->type === 'Penjualan') {
                                $typeClass = 'bg-rose-50 text-rose-700 border-rose-200';
                            }

                            // Operator Initials
                            $words = explode(' ', $m->operator);
                            $initials = '';
                            foreach ($words as $w) {
                                $initials .= strtoupper(substr($w, 0, 1));
                            }
                            $initials = substr($initials, 0, 2);
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="text-xs font-bold text-slate-800">{{ $m->created_at->translatedFormat('d M, H:i') }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs font-bold text-slate-800">{{ $m->branch_name }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-[10px] px-2.5 py-1 rounded font-extrabold border uppercase tracking-wider {{ $typeClass }}">{{ $m->type }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <div class="mb-1">
                                        <span class="text-[8px] px-1.5 py-0.5 rounded font-extrabold border uppercase tracking-wider {{ $catClass }}">{{ $m->product_category }}</span>
                                    </div>
                                    <span class="text-xs font-bold text-slate-900">{{ $m->product_name }}</span>
                                    <span class="text-[10px] text-slate-400 font-medium">SKU: {{ $m->product_sku }}</span>
                                    @if($m->type === 'Penjualan')
                                        @if($m->customer_id)
                                            <span class="text-[10px] text-slate-500 font-semibold mt-0.5">ID: {{ $m->customer_id }}</span>
                                        @endif
                                        @if($m->customer_phone)
                                            <span class="text-[10px] text-slate-500 font-medium font-mono">No: {{ $m->customer_phone }}</span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <p class="text-xs font-bold {{ $m->quantity_change < 0 ? 'text-commander-error' : 'text-commander-success' }}">
                                    {{ $m->quantity_change > 0 ? '+' : '' }}{{ $m->quantity_change }}
                                </p>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <p class="text-xs font-semibold text-slate-850">{{ $m->final_stock }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-[11px] text-slate-500 font-medium">{{ $m->reference_no }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-[9px] font-black text-slate-600">{{ $initials }}</div>
                                    <p class="text-xs font-medium text-slate-700">{{ $m->operator }}</p>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-xs font-medium text-slate-400">
                                Tidak ada data riwayat pergerakan stok.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($movements->hasPages())
            <div class="bg-slate-50 px-6 py-4 flex items-center justify-between border-t border-slate-200">
                <p class="text-xs text-slate-500 font-medium">
                    Menampilkan {{ $movements->firstItem() }} - {{ $movements->lastItem() }} dari {{ $movements->total() }} riwayat
                </p>
                <div class="flex gap-2">
                    {{-- Custom minimal pagination --}}
                    @if($movements->onFirstPage())
                        <button class="w-8 h-8 flex items-center justify-center rounded border border-slate-200 bg-white text-slate-350 cursor-not-allowed" disabled>
                            <span class="material-symbols-outlined text-[18px]">chevron_left</span>
                        </button>
                    @else
                        <a href="{{ $movements->previousPageUrl() }}" class="w-8 h-8 flex items-center justify-center rounded border border-slate-200 bg-white text-slate-600 hover:bg-slate-50">
                            <span class="material-symbols-outlined text-[18px]">chevron_left</span>
                        </a>
                    @endif

                    @foreach ($movements->getUrlRange(max(1, $movements->currentPage() - 1), min($movements->lastPage(), $movements->currentPage() + 1)) as $page => $url)
                        @if ($page == $movements->currentPage())
                            <button class="w-8 h-8 flex items-center justify-center rounded bg-slate-900 text-white font-bold text-xs">{{ $page }}</button>
                        @else
                            <a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center rounded border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 text-xs font-medium">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if($movements->hasMorePages())
                        <a href="{{ $movements->nextPageUrl() }}" class="w-8 h-8 flex items-center justify-center rounded border border-slate-200 bg-white text-slate-600 hover:bg-slate-50">
                            <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                        </a>
                    @else
                        <button class="w-8 h-8 flex items-center justify-center rounded border border-slate-200 bg-white text-slate-350 cursor-not-allowed" disabled>
                            <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                        </button>
                    @endif
                </div>
            </div>
        @endif
    </div>

@endsection
