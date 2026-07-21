@extends('layouts.app')

@section('title', 'Detail Analitik Inventoris')
@section('subtitle', 'Analisis mendalam nilai aset, produk terpopuler (fast moving), produk stagnan (slow moving), dan risiko kehabisan stok.')

@section('content')

    <!-- Header Actions & Branch Selector (Styled as Premium Banner) -->
    <div class="greeting-banner mb-0 rounded-t-xl rounded-b-none relative z-10 flex-col sm:flex-row items-center justify-between gap-4" style="padding: 16px 20px;">
        <!-- Left: Branch & Date selection -->
        <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto" style="position:relative;z-index:1;">
            <div class="flex items-center gap-3">
                <span class="text-[11px] font-extrabold text-white/80 uppercase tracking-wider">Cabang / Toko:</span>
                <div class="flex items-center gap-1.5 px-2.5 py-1.5 bg-white/95 border border-white/20 rounded-lg shadow-sm">
                    <span class="material-symbols-outlined text-sm text-indigo-600">store</span>
                    <select id="branch-selector" onchange="toggleBranchView()" class="bg-transparent border-none p-0 text-xs font-bold text-slate-800 focus:ring-0 cursor-pointer outline-none">
                        <option value="Gudang" {{ $selectedBranch === 'Gudang' ? 'selected' : '' }}>Gudang</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->name }}" {{ $selectedBranch === $b->name ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="flex items-center gap-1.5 px-2.5 py-1.5 bg-white/95 border border-white/20 rounded-lg shadow-sm">
                <span class="material-symbols-outlined text-sm text-indigo-600">calendar_today</span>
                <input id="date-selector" onchange="toggleDateView()" class="bg-transparent border-none p-0 text-xs font-bold text-slate-800 focus:ring-0 cursor-pointer w-28 outline-none" type="date" value="{{ $selectedDate }}">
            </div>
        </div>

        <!-- Right: Back Button -->
        <div class="flex items-center gap-2" style="position:relative;z-index:1;">
            <a href="{{ route('inventory.index', ['branch' => $selectedBranch, 'date' => $selectedDate]) }}" class="px-4 py-2 bg-white/20 border border-white/30 rounded-lg flex items-center gap-2 text-xs font-bold text-white hover:bg-white/30 transition-all cursor-pointer decoration-none backdrop-blur-sm shadow-sm">
                <span class="material-symbols-outlined text-sm">arrow_back</span> Kembali ke Manajemen Stok
            </a>
        </div>
    </div>
    <!-- Custom Page Skeleton Loader (Hidden by Default) -->
    <div id="custom-page-skeleton" class="hidden w-full animate-pulse bg-white p-4 sm:p-6 rounded-b-xl border border-slate-200 border-t-0 shadow-sm mb-8" style="margin-top: 0;">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="h-32 bg-slate-50 border border-slate-100 rounded-xl"></div>
            <div class="h-32 bg-slate-50 border border-slate-100 rounded-xl"></div>
            <div class="h-32 bg-slate-50 border border-slate-100 rounded-xl"></div>
            <div class="h-32 bg-slate-50 border border-slate-100 rounded-xl"></div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            <div class="h-64 bg-slate-50 border border-slate-100 rounded-xl"></div>
            <div class="h-64 bg-slate-50 border border-slate-100 rounded-xl"></div>
        </div>
    </div>

    <!-- Main Content Wrapper -->
    <div id="main-content" class="bg-white p-4 sm:p-6 rounded-b-xl border border-slate-200 border-t-0 shadow-sm mb-8 space-y-6" style="margin-top: 0;">


    <!-- KPI Analytics Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Total Asset Value -->
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex flex-col justify-between hover:border-indigo-300 hover:shadow-md transition-all group gap-2">
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <p style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin:0;">Total Nilai Aset</p>
                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" style="background:#eef2ff;">
                    <span class="material-symbols-outlined" style="color:#6366f1;font-size:20px;font-variation-settings:'FILL' 1;">account_balance_wallet</span>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-start;justify-content:center;flex:1;">
                <p style="font-size:26px;font-weight:800;color:#0f172a;letter-spacing:-0.5px;line-height:1.1;">Rp {{ number_format($totalAsset, 0, ',', '.') }}</p>
                <p style="font-size:11px;color:#94a3b8;margin-top:4px;margin-bottom:0;">Berdasarkan Harga Jual</p>
            </div>
        </div>

        <!-- Total HPP Cost -->
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex flex-col justify-between hover:border-sky-300 hover:shadow-md transition-all group gap-2">
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <p style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin:0;">Nilai Modal (HPP)</p>
                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" style="background:#f0f9ff;">
                    <span class="material-symbols-outlined" style="color:#0ea5e9;font-size:20px;font-variation-settings:'FILL' 1;">receipt_long</span>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-start;justify-content:center;flex:1;">
                <p style="font-size:26px;font-weight:800;color:#0f172a;letter-spacing:-0.5px;line-height:1.1;">Rp {{ number_format($totalHppAsset, 0, ',', '.') }}</p>
                <p style="font-size:11px;color:#94a3b8;margin-top:4px;margin-bottom:0;">Berdasarkan Harga Pokok</p>
            </div>
        </div>

        <!-- Potential Profit Margin -->
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex flex-col justify-between hover:border-emerald-300 hover:shadow-md transition-all group gap-2">
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <p style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin:0;">Potensi Keuntungan</p>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="font-size:11px;font-weight:700;color:#16a34a;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:99px;padding:2px 8px;display:flex;align-items:center;gap:2px;">
                        {{ $totalAsset > 0 ? number_format(($potentialProfit / $totalAsset) * 100, 1) . '%' : '0%' }}
                    </span>
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" style="background:#f0fdf4;">
                        <span class="material-symbols-outlined" style="color:#16a34a;font-size:20px;font-variation-settings:'FILL' 1;">monetization_on</span>
                    </div>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-start;justify-content:center;flex:1;">
                <p style="font-size:26px;font-weight:800;color:#0f172a;letter-spacing:-0.5px;line-height:1.1;">Rp {{ number_format($potentialProfit, 0, ',', '.') }}</p>
                <p style="font-size:11px;color:#94a3b8;margin-top:4px;margin-bottom:0;">Selisih Aset - Modal</p>
            </div>
        </div>

        <!-- Total Items -->
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex flex-col justify-between hover:border-amber-300 hover:shadow-md transition-all group gap-2">
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <p style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin:0;">Total Kuantitas</p>
                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" style="background:#fff7ed;">
                    <span class="material-symbols-outlined" style="color:#ea580c;font-size:20px;font-variation-settings:'FILL' 1;">inventory_2</span>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-start;justify-content:center;flex:1;">
                <p style="font-size:26px;font-weight:800;color:#0f172a;letter-spacing:-0.5px;line-height:1.1;">{{ number_format($totalItems, 0, ',', '.') }} <span style="font-size:14px;color:#64748b;font-weight:600;">Pcs</span></p>
                <p style="font-size:11px;color:#94a3b8;margin-top:4px;margin-bottom:0;">Seluruh SKU Aktif</p>
            </div>
        </div>
    </div>

    <!-- Category Assets Distribution Breakdown -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
        <div class="bg-slate-50 border-b border-slate-200 px-5 py-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-slate-400 text-sm">pie_chart</span>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Distribusi Aset Per Kategori</span>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @php
                    $colors = [
                        'Perdana' => ['bg' => 'bg-indigo-500', 'text' => 'text-indigo-700', 'light' => 'bg-indigo-50'],
                        'Voucher' => ['bg' => 'bg-sky-500', 'text' => 'text-sky-700', 'light' => 'bg-sky-50'],
                        'Aksesoris' => ['bg' => 'bg-violet-500', 'text' => 'text-violet-700', 'light' => 'bg-violet-50'],
                    ];
                @endphp
                @forelse($categoryAssets as $cat)
                    @php
                        $catName = $cat['category'];
                        $catAssetVal = $cat['asset_value'];
                        $catQty = $cat['total_qty'];
                        $pct = $totalAsset > 0 ? ($catAssetVal / $totalAsset) * 100 : 0;
                        $style = $colors[$catName] ?? ['bg' => 'bg-slate-600', 'text' => 'text-slate-600', 'light' => 'bg-slate-50'];
                    @endphp
                    <div class="border border-slate-150 rounded-xl p-4 space-y-3 hover:bg-slate-50/50 transition-all">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-700">{{ $catName }}</span>
                            <span class="text-xs font-extrabold text-slate-900">{{ number_format($pct, 1) }}%</span>
                        </div>
                        <!-- Progress bar -->
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="{{ $style['bg'] }} h-2 rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                        <div class="flex items-center justify-between text-[11px] font-semibold text-slate-500 pt-1">
                            <span>{{ number_format($catQty, 0, ',', '.') }} Pcs</span>
                            <span class="text-slate-750">Rp {{ number_format($catAssetVal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center text-xs font-medium text-slate-400 py-4">Tidak ada data aset kategori</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Triple Lists Grid: Fast, Slow, & Low Stock -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Top 10 Fast Moving -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
            <div class="bg-slate-50 border-b border-slate-200 px-5 py-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-400 text-sm">trending_up</span>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Fast Moving (Top 10)</span>
                </div>
                <span class="text-[9px] font-extrabold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded uppercase border border-emerald-100">Terjual</span>
            </div>
            <div class="p-5 flex-1 space-y-4">
                @forelse($fastMoving as $index => $item)
                    @php
                        $maxSold = $fastMoving->first() ? $fastMoving->first()->sold_stock : 1;
                        $soldPct = $maxSold > 0 ? ($item->sold_stock / $maxSold) * 100 : 0;
                    @endphp
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="text-[10px] font-black text-slate-400 w-4">{{ $index + 1 }}.</span>
                                <span class="font-bold text-slate-700 truncate max-w-[170px]" title="{{ $item->name }}">{{ $item->name }}</span>
                            </div>
                            <span class="font-black text-slate-900 whitespace-nowrap">{{ $item->sold_stock }} Pcs</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-1.5 ml-6" style="width: calc(100% - 24px);">
                            <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $soldPct }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-4 text-center">Tidak ada data penjualan</p>
                @endforelse
            </div>
        </div>

        <!-- Top 10 Slow Moving -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
            <div class="bg-slate-50 border-b border-slate-200 px-5 py-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-400 text-sm">trending_down</span>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Slow Moving (Top 10)</span>
                </div>
                <span class="text-[9px] font-extrabold text-slate-600 bg-slate-100 px-2 py-0.5 rounded uppercase border border-slate-200/60">Stagnan</span>
            </div>
            <div class="p-5 flex-1 space-y-4">
                @forelse($slowMoving as $index => $item)
                    @php
                        // Compare relative to max of slow moving list
                        $maxSlow = $slowMoving->last() ? $slowMoving->last()->sold_stock : 1;
                        $slowPct = $maxSlow > 0 ? ($item->sold_stock / $maxSlow) * 100 : 0;
                    @endphp
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="text-[10px] font-black text-slate-400 w-4">{{ $index + 1 }}.</span>
                                <span class="font-bold text-slate-700 truncate max-w-[170px]" title="{{ $item->name }}">{{ $item->name }}</span>
                            </div>
                            <span class="font-black text-slate-900 whitespace-nowrap">{{ $item->sold_stock }} Pcs</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-1.5 ml-6" style="width: calc(100% - 24px);">
                            <div class="bg-slate-400 h-1.5 rounded-full" style="width: {{ $slowPct }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-4 text-center">Tidak ada data stagnan</p>
                @endforelse
            </div>
        </div>

        <!-- Low Stock / Kritis -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
            <div class="bg-slate-50 border-b border-slate-200 px-5 py-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-400 text-sm">notification_important</span>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Produk Kritis & Tipis</span>
                </div>
                <span class="text-[9px] font-extrabold text-red-600 bg-red-50 px-2 py-0.5 rounded uppercase border border-red-100">Stok &le; 10</span>
            </div>
            <div class="p-5 flex-1 overflow-y-auto max-h-[420px] divide-y divide-slate-100 pr-1">
                @forelse($lowStock as $item)
                    @php
                        $isCritical = $item->final_stock <= 5;
                        $statusText = $item->final_stock === 0 ? 'Habis' : ($isCritical ? 'Kritis' : 'Tipis');
                        $badgeClass = $item->final_stock === 0 ? 'bg-red-100 text-red-700' : ($isCritical ? 'bg-red-50 text-red-650' : 'bg-amber-50 text-amber-600');
                    @endphp
                    <div class="flex items-center justify-between py-3">
                        <div class="min-w-0 pr-3">
                            <p class="text-xs font-bold text-slate-800 truncate" title="{{ $item->name }}">{{ $item->name }}</p>
                            <p class="text-[9px] font-semibold text-slate-400 mt-0.5">SKU: {{ $item->sku }}</p>
                        </div>
                        <div class="text-right flex flex-col items-end gap-1 flex-shrink-0">
                            <span class="text-xs font-black text-slate-900">{{ $item->final_stock }} Pcs</span>
                            <span class="text-[8px] font-extrabold uppercase px-1.5 py-0.5 rounded-full {{ $badgeClass }}">{{ $statusText }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-6 text-center">Semua stok produk berada di batas aman</p>
                @endforelse
            </div>
        </div>
    </div>


    </div> <!-- End Main Content Wrapper -->
@endsection

@push('scripts')
<script>
    function toggleBranchView() {
        const selector = document.getElementById('branch-selector');
        const selectedValue = selector.value;
        const dateEl = document.getElementById('date-selector');
        const dateValue = dateEl ? dateEl.value : "{{ $selectedDate }}";
        if (typeof window.triggerGlobalLoading === 'function') window.triggerGlobalLoading();
        window.location.href = "{{ route('inventory.analytics') }}?branch=" + encodeURIComponent(selectedValue) + "&date=" + encodeURIComponent(dateValue);
    }

    function toggleDateView() {
        const selector = document.getElementById('branch-selector');
        const branchValue = selector ? selector.value : "{{ $selectedBranch }}";
        const dateEl = document.getElementById('date-selector');
        const dateValue = dateEl ? dateEl.value : "";
        if (typeof window.triggerGlobalLoading === 'function') window.triggerGlobalLoading();
        window.location.href = "{{ route('inventory.analytics') }}?branch=" + encodeURIComponent(branchValue) + "&date=" + encodeURIComponent(dateValue);
    }
    window.customTriggerLoading = function() {
        document.getElementById('main-content').style.display = 'none';
        document.getElementById('custom-page-skeleton').classList.remove('hidden');
    };
    window.customRestoreLoading = function() {
        document.getElementById('main-content').style.display = 'block';
        document.getElementById('custom-page-skeleton').classList.add('hidden');
    };
</script>
@endpush
