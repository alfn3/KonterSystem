@extends('layouts.app')

@section('title', 'Laporan Bulanan')
@section('subtitle', 'Analisis komparatif laba kotor, distribusi kategori produk, dan kinerja tim kasir.')

@section('content')

    <!-- Report Period Selector & Controls Bar (Premium Banner) -->
    <div class="greeting-banner mb-6 flex-col sm:flex-row items-center justify-between gap-4" style="padding: 16px 20px;">
        <div class="flex items-center gap-3" style="position:relative;z-index:1;">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-white/20 border border-white/30 backdrop-blur-sm shadow-sm shrink-0">
                <span class="material-symbols-outlined text-white text-[20px]" style="font-variation-settings:'FILL' 1;">analytics</span>
            </div>
            <div>
                <h2 class="text-sm font-black text-white">Periode Laporan</h2>
                <p class="text-[11px] font-bold text-white/80">Laporan bulanan lengkap</p>
            </div>
        </div>
        
        <div class="flex flex-wrap items-center gap-4" style="position:relative;z-index:1;">
            <div class="flex items-center bg-white/95 border border-white/20 rounded-lg px-2.5 py-1.5 gap-2 shadow-sm backdrop-blur-sm">
                <button class="material-symbols-outlined text-slate-400 text-sm hover:text-indigo-600 transition-colors cursor-pointer">chevron_left</button>
                <span class="text-xs font-black text-slate-800 min-w-[90px] text-center">Oktober 2023</span>
                <button class="material-symbols-outlined text-slate-400 text-sm hover:text-indigo-600 transition-colors cursor-pointer">chevron_right</button>
            </div>
            <button class="flex items-center gap-1.5 bg-white/20 border border-white/30 text-white px-4 py-2 rounded-lg text-[11px] font-extrabold uppercase tracking-wider hover:bg-white/30 transition-colors shadow-sm cursor-pointer backdrop-blur-sm">
                <span class="material-symbols-outlined text-sm">download</span> Ekspor PDF/Excel
            </button>
        </div>
    </div>

    <!-- Executive Summary Cards -->
    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($summaries as $summary)
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm border-t-4 {{ $summary['border_color'] }}">
                <div class="flex justify-between items-start mb-2">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $summary['title'] }}</p>
                    <span class="material-symbols-outlined text-slate-400 text-base">{{ $summary['icon'] }}</span>
                </div>
                <p class="text-2xl font-black {{ $summary['text_color'] }}">{{ $summary['value'] }}</p>
                <div class="flex items-center gap-1 mt-2 text-xs">
                    <span class="font-extrabold {{ $summary['title'] == 'Selisih Kas/Stok' ? 'text-commander-error' : 'text-emerald-600' }}">{{ $summary['trend'] }}</span>
                    <span class="text-slate-400 font-semibold">{{ $summary['trend_desc'] }}</span>
                </div>
            </div>
        @endforeach
    </section>

    <!-- Performance & Category Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Performance Trends Chart -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between">
            <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-400 text-sm">bar_chart</span>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tren Performa Bulanan</span>
                </div>
                <div class="flex gap-4 text-xs font-semibold text-slate-500">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-slate-900 rounded-full"></div>
                        <span>Omset</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-slate-200 rounded-full"></div>
                        <span>HPP</span>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <!-- Mock Chart Representation using pure CSS flex heights -->
                <div class="h-64 flex items-end justify-between gap-6 px-4 border-b border-slate-100 pb-2">
                    @foreach($weekly_trends as $trend)
                        <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
                            <div class="w-full flex items-end gap-1.5 h-[80%]">
                                <div class="flex-1 bg-slate-200 rounded-t-sm {{ $trend['hpp_height'] }} transition-all duration-500"></div>
                                <div class="flex-1 bg-slate-900 rounded-t-sm {{ $trend['omzet_height'] }} transition-all duration-500"></div>
                            </div>
                            <span class="text-[10px] text-slate-400 font-extrabold">{{ $trend['week'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Category Breakdown -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between">
            <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-slate-400 text-sm">pie_chart</span>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Distribusi Kategori</span>
            </div>
            <div class="p-6 flex-1 flex flex-col justify-center">
                <div class="space-y-5">
                    @foreach($categories as $cat)
                        <div class="space-y-1">
                            <div class="flex justify-between text-xs font-semibold mb-1">
                                <span class="text-slate-600">{{ $cat['name'] }}</span>
                                <span class="text-slate-900 font-extrabold">{{ $cat['percentage'] }}%</span>
                            </div>
                            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                <div class="bg-slate-900 h-full rounded-full" style="width: {{ $cat['percentage'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Selisih per Kasir Table -->
    <section class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-slate-400 text-sm">account_balance_wallet</span>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Selisih per Kasir</span>
            </div>
            <div class="flex items-center gap-1 text-xs font-bold text-slate-900 cursor-pointer hover:underline">
                <span>Laporan Audit Lengkap</span>
                <span class="material-symbols-outlined text-sm">open_in_new</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Kasir</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Total Penjualan</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Selisih Kas</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">BON</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">INSENTIF</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($cashier_reconciliations as $recon)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-slate-400 text-base">person</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-900">{{ $recon['name'] }}</p>
                                        <p class="text-[10px] text-slate-400 font-semibold">{{ $recon['shift'] }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs font-extrabold text-slate-900">{{ $recon['sales'] }}</td>
                            <td class="px-6 py-4 text-xs font-black {{ $recon['gap_class'] }}">{{ $recon['gap'] }}</td>
                            <td class="px-6 py-4 text-xs font-extrabold {{ $recon['bon'] != 'Rp 0' ? 'text-commander-error' : 'text-slate-500' }}">{{ $recon['bon'] }}</td>
                            <td class="px-6 py-4 text-xs font-extrabold text-emerald-600">{{ $recon['incentive'] }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[9px] font-extrabold uppercase {{ $recon['status_class'] }}">
                                    {{ $recon['status'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <!-- Branch Performance & Top Products -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Branch Performance Table -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-400 text-sm">storefront</span>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Performa Cabang</span>
                </div>
                <button class="text-xs font-bold text-slate-900 hover:underline">Lihat Detail</button>
            </div>
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Cabang</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Omset (IDR)</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Margin</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Status Audit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($branch_performances as $perf)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-slate-900">{{ $perf['name'] }}</p>
                                <p class="text-[11px] text-slate-400 font-semibold">{{ $perf['type'] }}</p>
                            </td>
                            <td class="px-6 py-4 text-xs font-extrabold text-slate-900">{{ $perf['revenue'] }}</td>
                            <td class="px-6 py-4 text-xs font-black text-emerald-600">{{ $perf['margin'] }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[9px] font-extrabold uppercase {{ $perf['status_class'] }}">
                                    <span class="material-symbols-outlined text-[12px] mr-1" style="font-variation-settings: 'FILL' 1;">{{ $perf['status_icon'] }}</span>
                                    {{ $perf['status'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Top Products (Fast Move) -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
            <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-slate-400 text-sm">bolt</span>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Fast Move Items</span>
            </div>
            <div class="p-6 flex-1 flex flex-col justify-between">
                <div class="space-y-4 flex-grow">
                @foreach($fast_move_items as $item)
                    <div class="flex items-center gap-4 group cursor-pointer">
                        <div class="h-12 w-12 rounded bg-slate-950 text-white flex items-center justify-center font-extrabold text-xs">
                            {{ $item['avatar_text'] }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-900 truncate">{{ $item['name'] }}</p>
                            <p class="text-[11px] text-slate-400 font-semibold">{{ $item['sold'] }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold text-emerald-600">{{ $item['price'] }}</p>
                            <p class="text-[9px] text-slate-400 font-bold">Avg Price</p>
                        </div>
                    </div>
                @endforeach
            </div>
            <button class="w-full mt-6 py-2 border border-slate-200 rounded-lg text-xs font-bold text-slate-500 hover:bg-slate-50 transition-colors">
                Selengkapnya
            </button>
        </div>
    </div>

@endsection
