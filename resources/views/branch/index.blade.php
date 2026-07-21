@extends('layouts.app')

@section('title', 'Manajemen Cabang')
@section('subtitle', 'Kelola informasi outlet, pantau kesehatan stok, serta evaluasi profit margin per cabang.')

@section('content')

    <!-- Summary Section (Dashboard Style) -->
    <section class="mb-6 mt-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
            @php
                $metricIcons = [
                    'Cabang Aktif' => ['icon' => 'store', 'bg' => '#eef2ff', 'color' => '#6366f1'],
                    'Total Cabang' => ['icon' => 'storefront', 'bg' => '#f0fdf4', 'color' => '#16a34a'],
                    'Stok Kritis'  => ['icon' => 'warning', 'bg' => '#fff7ed', 'color' => '#ea580c'],
                    'Audit Telat'  => ['icon' => 'schedule', 'bg' => '#fdf4ff', 'color' => '#9333ea'],
                ];
            @endphp
            @foreach($stats as $stat)
                @php $mi = $metricIcons[$stat['title']] ?? ['icon' => $stat['icon'] ?? 'bar_chart', 'bg' => '#f8fafc', 'color' => '#64748b']; @endphp
                <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex flex-col justify-between hover:border-indigo-300 hover:shadow-md transition-all group gap-3">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $mi['bg'] }};">
                            <span class="material-symbols-outlined" style="color:{{ $mi['color'] }};font-size:20px;font-variation-settings:'FILL' 1;">{{ $mi['icon'] }}</span>
                        </div>
                    </div>
                    <div>
                        <p style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">{{ $stat['title'] }}</p>
                        <p style="font-size:26px;font-weight:800;color:#0f172a;letter-spacing:-0.5px;line-height:1.1;">
                            {{ $stat['value'] }}
                            @if($stat['total'])
                                <span style="font-size:14px;color:#64748b;font-weight:600;">{{ $stat['total'] }}</span>
                            @endif
                        </p>
                        <p style="font-size:11px;color:{{ str_contains($stat['text_class'] ?? '', 'red') ? '#dc2626' : (str_contains($stat['text_class'] ?? '', 'green') ? '#16a34a' : '#64748b') }};margin-top:4px;display:flex;align-items:center;gap:3px;">
                            {{ $stat['desc'] }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- Filters & Actions (Styled as Premium Banner) -->
    <div class="greeting-banner mb-0 rounded-t-xl rounded-b-none relative z-10 flex-col sm:flex-row items-center justify-between gap-4" style="padding: 16px 20px;">
        <div class="flex flex-wrap items-center gap-4 w-full sm:w-auto" style="position:relative;z-index:1;">
            <form action="{{ route('branch.index') }}" method="GET" class="flex flex-wrap items-center gap-3 flex-1 m-0 p-0" id="filter-form" onsubmit="event.preventDefault(); fetchIndexData(this.action + '?' + new URLSearchParams(new FormData(this)).toString());">
                <div class="flex items-center gap-1.5 px-2.5 py-1.5 bg-white/95 border border-white/20 rounded-lg shadow-sm shrink-0">
                    <span class="material-symbols-outlined text-indigo-600 text-sm">map</span>
                    <select name="wilayah" onchange="fetchIndexData('{{ route('branch.index') }}?' + new URLSearchParams(new FormData(this.form)).toString());" class="bg-transparent text-xs font-bold text-slate-800 outline-none cursor-pointer">
                        <option value="Semua Wilayah" {{ request('wilayah') === 'Semua Wilayah' ? 'selected' : '' }}>Semua Wilayah</option>
                        <option value="Jawa Barat" {{ request('wilayah') === 'Jawa Barat' ? 'selected' : '' }}>Jawa Barat</option>
                        <option value="DKI Jakarta" {{ request('wilayah') === 'DKI Jakarta' ? 'selected' : '' }}>DKI Jakarta</option>
                        <option value="Jawa Tengah" {{ request('wilayah') === 'Jawa Tengah' ? 'selected' : '' }}>Jawa Tengah</option>
                    </select>
                </div>
                <div class="flex items-center gap-1.5 px-2.5 py-1.5 bg-white/95 border border-white/20 rounded-lg shadow-sm shrink-0">
                    <span class="material-symbols-outlined text-indigo-600 text-sm">wifi</span>
                    <select name="status" onchange="fetchIndexData('{{ route('branch.index') }}?' + new URLSearchParams(new FormData(this.form)).toString());" class="bg-transparent text-xs font-bold text-slate-800 outline-none cursor-pointer">
                        <option value="Semua Status" {{ request('status') === 'Semua Status' ? 'selected' : '' }}>Semua Status</option>
                        <option value="Online" {{ request('status') === 'Online' ? 'selected' : '' }}>Online</option>
                        <option value="Offline" {{ request('status') === 'Offline' ? 'selected' : '' }}>Offline</option>
                    </select>
                </div>
                <div class="flex items-center gap-1.5 px-2.5 py-1.5 bg-white/95 border border-white/20 rounded-lg shadow-sm shrink-0">
                    <span class="material-symbols-outlined text-indigo-600 text-sm">sort</span>
                    <select name="sort" onchange="fetchIndexData('{{ route('branch.index') }}?' + new URLSearchParams(new FormData(this.form)).toString());" class="bg-transparent text-xs font-bold text-slate-800 outline-none cursor-pointer">
                        <option value="Terbaru" {{ request('sort') === 'Terbaru' ? 'selected' : '' }}>Terbaru</option>
                        <option value="Revenue Tertinggi" {{ request('sort') === 'Revenue Tertinggi' ? 'selected' : '' }}>Revenue Tertinggi</option>
                        <option value="Stok Terendah" {{ request('sort') === 'Stok Terendah' ? 'selected' : '' }}>Stok Terendah</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="flex items-center gap-2" style="position:relative;z-index:1;">
            <button onclick="openAddBranchModal()" class="flex items-center gap-1.5 bg-white/20 border border-white/30 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-white/30 transition-all shadow-sm cursor-pointer shrink-0 backdrop-blur-sm">
                <span class="material-symbols-outlined text-sm">add</span> Tambah Cabang
            </button>
        </div>
    </div>
    <!-- Custom Page Skeleton Loader (Hidden by Default) -->
    <div id="custom-page-skeleton" class="hidden w-full animate-pulse bg-white p-4 sm:p-6 rounded-b-xl border border-slate-200 border-t-0 shadow-sm mb-8" style="margin-top: 0;">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div class="h-48 bg-slate-50 border border-slate-100 rounded-xl"></div>
            <div class="h-48 bg-slate-50 border border-slate-100 rounded-xl"></div>
            <div class="h-48 bg-slate-50 border border-slate-100 rounded-xl"></div>
        </div>
    </div>

    <!-- Main Content Wrapper -->
    <div id="main-content" class="bg-white p-4 sm:p-6 rounded-b-xl border border-slate-200 border-t-0 shadow-sm mb-8 space-y-6" style="margin-top: 0;">



    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4" id="branch-cards-grid">
        @forelse($branches as $branch)
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm hover:shadow-md hover:border-indigo-300 transition-all duration-300 flex flex-col overflow-hidden group" id="branch-card-{{ $branch['id'] }}">
                
                <!-- Card Header -->
                <div class="p-5 flex items-start justify-between gap-4 border-b border-slate-100">
                    <div class="flex flex-col min-w-0">
                        <div class="flex items-center gap-2">
                            <h4 class="text-base font-extrabold text-slate-900 truncate leading-snug">{{ $branch['name'] }}</h4>
                        </div>
                        <span class="text-[11px] text-slate-400 font-semibold mt-1 flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">location_on</span>
                            <span class="truncate">{{ $branch['address'] }}</span>
                        </span>
                        <span class="text-[10px] text-slate-500 font-bold mt-1 flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">vpn_key</span>
                            <span>Agent ID: <span class="text-indigo-600">{{ $branch['agent_id'] ?: '-' }}</span></span>
                        </span>
                    </div>
                    
                    <!-- Status Badge -->
                    <div class="flex flex-col items-end gap-1 shrink-0">
                        <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-50 border border-slate-200/60 shadow-sm">
                            <span class="w-2.5 h-2.5 rounded-full {{ $branch['status_class'] }} status-dot animate-pulse-online" data-id="{{ $branch['id'] }}"></span>
                            <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider status-text" data-id="{{ $branch['id'] }}">{{ $branch['status'] }}</span>
                        </div>
                        @if($branch['status'] === 'Offline' && $branch['last_active'])
                            <span class="text-[9px] font-semibold text-slate-400">Aktif: {{ $branch['last_active'] }}</span>
                        @endif
                    </div>
                </div>

                <!-- Card Body -->
                <div class="p-5 flex-1 space-y-4">
                    <!-- Metrics Section -->
                    <div class="space-y-3">
                        <!-- Penjualan -->
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Penjualan</span>
                            <span class="text-xs font-black text-slate-900" data-field="revenue" data-id="{{ $branch['id'] }}">{{ $branch['revenue_mtd'] }}</span>
                        </div>
                        
                        <!-- Saldo Elektrik -->
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Saldo Elektrik</span>
                            <div class="flex items-center gap-1.5 text-right">
                                @if($branch['saldo_elektrik_val'] < 1000000)
                                    <span class="text-[8px] font-black text-red-600 bg-red-50 px-1 rounded border border-red-200 uppercase tracking-wider leading-none" data-field="saldo_elektrik_kritis" data-id="{{ $branch['id'] }}">Kritis</span>
                                @endif
                                <span class="text-xs font-black {{ $branch['saldo_elektrik_val'] < 1000000 ? 'text-red-600' : 'text-slate-900' }}" data-field="saldo_elektrik" data-id="{{ $branch['id'] }}">
                                    {{ $branch['saldo_elektrik'] }}
                                </span>
                            </div>
                        </div>

                        <!-- Pelanggan -->
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pelanggan</span>
                            <span class="text-xs font-black text-slate-900" data-field="today_customer_count" data-id="{{ $branch['id'] }}">{{ $branch['today_customer_count'] }} Orang</span>
                        </div>

                        <!-- Kesehatan Stok -->
                        <div class="space-y-1.5 pt-1">
                            <div class="flex items-center justify-between text-[10px]">
                                <span class="font-bold text-slate-400 uppercase tracking-wider">Kesehatan Stok</span>
                                <span class="font-extrabold text-slate-900">{{ $branch['stock_health_label'] }}</span>
                            </div>
                            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden border border-slate-200/50">
                                <div class="h-full {{ $branch['stock_health_class'] }} transition-all duration-500" style="width: {{ $branch['stock_health'] }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Karyawan / Absen -->
                    <div class="flex items-center justify-between pt-3 border-t border-slate-100/80">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Karyawan Hari Ini</span>
                        @if($branch['today_attendance'])
                            <div class="flex items-center gap-1.5 text-xs font-bold text-green-600">
                                <span class="material-symbols-outlined text-sm">how_to_reg</span>
                                <span>Absen: {{ $branch['today_attendance'] }}</span>
                            </div>
                        @else
                            <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-400">
                                <span class="material-symbols-outlined text-sm">pending_actions</span>
                                <span>Belum Absen</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Card Footer Action -->
                <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between group-hover:bg-indigo-50/50 transition-colors">
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Operasional</span>
                    <a href="{{ route('branch.show', $branch['id']) }}" onclick="if(typeof window.triggerGlobalLoading === 'function') window.triggerGlobalLoading(true);" class="flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-[10px] font-bold uppercase tracking-wider hover:bg-indigo-700 transition-all shadow-sm">
                        <span>Detail</span>
                        <span class="material-symbols-outlined text-xs">chevron_right</span>
                    </a>
                </div>

            </div>
        @empty
            <div class="col-span-full bg-white border border-slate-200 rounded-xl py-12 text-center text-xs font-medium text-slate-400">
                Tidak ada data cabang untuk filter ini.
            </div>
        @endforelse
    </div>

    <!-- Add Branch Modal -->
    <div id="add-branch-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[999] flex items-center justify-center hidden p-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-500">add_business</span>
                    Registrasi Cabang Baru
                </h3>
                <button onclick="closeAddBranchModal()" class="material-symbols-outlined text-slate-400 hover:text-slate-600 cursor-pointer">close</button>
            </div>
            <form action="{{ route('branch.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Nama Cabang</label>
                        <input type="text" name="name" required placeholder="e.g. Konter Gajah Mada" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Agent ID</label>
                        <input type="text" name="agent_id" placeholder="e.g. operator1 (opsional)" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Status Operasional</label>
                        <select name="status" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-semibold text-slate-700 outline-none focus:ring-1 focus:ring-slate-900 cursor-pointer">
                            <option value="Online">Online</option>
                            <option value="Offline">Offline</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Profit Margin (%)</label>
                        <input type="number" name="profit_margin" required min="0" max="100" placeholder="e.g. 25" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Stok Awal Tersedia</label>
                        <input type="number" name="stock_available" required min="0" placeholder="e.g. 5000" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Kesehatan Stok (%)</label>
                        <input type="number" name="stock_health" required min="0" max="100" placeholder="e.g. 90" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Penjualan (Rupiah)</label>
                    <input type="number" name="revenue_mtd" required min="0" placeholder="e.g. 150000000" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Alamat Cabang</label>
                    <input type="text" name="address" required placeholder="e.g. Jl. Pemuda No. 12" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                </div>
                <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                    <button type="button" onclick="closeAddBranchModal()" class="px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-lg text-xs font-bold uppercase cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-bold uppercase cursor-pointer">Simpan Cabang</button>
                </div>
            </form>
        </div>
    </div>


    </div> <!-- End Main Content Wrapper -->
@endsection

@push('scripts')
<script>
    function openAddBranchModal() {
        document.getElementById('add-branch-modal').classList.remove('hidden');
    }
    function closeAddBranchModal() {
        document.getElementById('add-branch-modal').classList.add('hidden');
    }


    function pollBranchStatus() {
        fetch('/api/cabang/status')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                data.forEach(branch => {
                    const dot = document.querySelector(`.status-dot[data-id="${branch.id}"]`);
                    const text = document.querySelector(`.status-text[data-id="${branch.id}"]`);
                    const revenueText = document.querySelector(`[data-field="revenue"][data-id="${branch.id}"]`);
                    const saldoText = document.querySelector(`[data-field="saldo_elektrik"][data-id="${branch.id}"]`);
                    const kritisContainer = document.querySelector(`[data-field="saldo_elektrik_kritis"][data-id="${branch.id}"]`);
                    const todayCustomerText = document.querySelector(`[data-field="today_customer_count"][data-id="${branch.id}"]`);
                    
                    if (dot && text) {
                        text.innerText = branch.status;
                        
                        // Handle style classes
                        if (branch.status_class) {
                            dot.className = `w-2.5 h-2.5 rounded-full ${branch.status_class} status-dot`;
                        } else {
                            if (branch.status === 'Online' || branch.status === 'Open') {
                                dot.className = 'w-2.5 h-2.5 rounded-full bg-green-500 status-dot';
                            } else {
                                dot.className = 'w-2.5 h-2.5 rounded-full bg-slate-400 status-dot';
                            }
                        }
                    }

                    // Dynamically update revenue / today sales
                    if (revenueText && branch.revenue_today) {
                        revenueText.innerText = branch.revenue_today;
                    }

                    // Dynamically update today's customer count
                    if (todayCustomerText && typeof branch.today_customer_count !== 'undefined') {
                        todayCustomerText.innerText = branch.today_customer_count + ' Orang';
                    }

                    // Dynamically update saldo elektrik and warning classes
                    if (saldoText && branch.saldo_elektrik) {
                        saldoText.innerText = branch.saldo_elektrik;
                        
                        if (branch.saldo_elektrik_val < 1000000) {
                            saldoText.className = 'text-xs font-black text-commander-error truncate';
                            if (kritisContainer) {
                                kritisContainer.innerHTML = '<span class="inline-flex items-center text-[8px] font-black text-commander-error bg-red-50 px-1 rounded border border-red-200 uppercase tracking-wider leading-none">Kritis</span>';
                            }
                        } else {
                            saldoText.className = 'text-xs font-black text-slate-900 truncate';
                            if (kritisContainer) {
                                kritisContainer.innerHTML = '';
                            }
                        }
                    }
                });
            })
            .catch(error => console.error('Error fetching branch statuses:', error));
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Run immediately on page load, then poll every 10 seconds
        pollBranchStatus();
        setInterval(pollBranchStatus, 10000);
    });

    window.customTriggerLoading = function() {
        document.getElementById('main-content').style.display = 'none';
        document.getElementById('custom-page-skeleton').classList.remove('hidden');
    };
    window.customRestoreLoading = function() {
        document.getElementById('main-content').style.display = 'block';
        document.getElementById('custom-page-skeleton').classList.add('hidden');
    };

    function fetchIndexData(url) {
        window.customTriggerLoading();
        fetch(url)
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                const newContent = doc.getElementById('main-content');
                if (newContent) {
                    document.getElementById('main-content').innerHTML = newContent.innerHTML;
                }
                
                // Also update the summary section if needed
                const newSummary = doc.querySelector('section.mb-6.mt-6');
                if (newSummary) {
                    document.querySelector('section.mb-6.mt-6').innerHTML = newSummary.innerHTML;
                }

                window.history.pushState({path: url}, '', url);
                window.customRestoreLoading();
                
                // Restart polling if elements were replaced
                pollBranchStatus();
            })
            .catch(err => {
                console.error('AJAX fetch failed:', err);
                window.location.href = url; // Fallback to full reload
            });
    }
</script>
@endpush
