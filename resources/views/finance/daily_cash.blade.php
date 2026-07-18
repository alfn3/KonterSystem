@extends('layouts.app')

@section('title', 'Kas Harian')
@section('subtitle', 'Pantau arus kas masuk dan keluar (pemasukan & pengeluaran) pada setiap cabang per hari.')

@section('content')

    <!-- Premium Banner for Filter -->
    <div class="greeting-banner mb-6 flex-col sm:flex-row items-center justify-between gap-4" style="padding: 16px 20px;">
        <form action="{{ route('daily-cash.index') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full sm:w-auto m-0" style="position:relative;z-index:1;">
            <div class="flex items-center gap-3">
                <span class="text-[11px] font-extrabold text-white/80 uppercase tracking-wider">Cabang:</span>
                <div class="flex items-center gap-1.5 px-2.5 py-1.5 bg-white/95 border border-white/20 rounded-lg shadow-sm">
                    <span class="material-symbols-outlined text-sm text-indigo-600">store</span>
                    <select name="branch_id" onchange="this.form.submit()" class="bg-transparent border-none p-0 text-xs font-bold text-slate-800 focus:ring-0 cursor-pointer outline-none">
                        <option value="">Semua Cabang</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ $selectedBranchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="flex items-center gap-1.5 px-2.5 py-1.5 bg-white/95 border border-white/20 rounded-lg shadow-sm">
                <span class="material-symbols-outlined text-sm text-indigo-600">calendar_today</span>
                <input name="date" onchange="this.form.submit()" class="bg-transparent border-none p-0 text-xs font-bold text-slate-800 focus:ring-0 cursor-pointer w-28 outline-none" type="date" value="{{ $date }}">
            </div>
        </form>

        <!-- Right Group: Actions -->
        <div class="flex items-center gap-2" style="position:relative;z-index:1;">
            <button class="px-4 py-2 bg-white text-indigo-600 border border-white/20 rounded-lg flex items-center gap-2 text-[11px] font-extrabold uppercase tracking-wider hover:bg-slate-50 transition-all cursor-pointer shadow-sm shrink-0">
                <span class="material-symbols-outlined text-sm">add</span> Catat Transaksi
            </button>
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <!-- Saldo Awal -->
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex flex-col justify-between hover:border-slate-300 hover:shadow-md transition-all group gap-3">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" style="background:#f1f5f9;">
                    <span class="material-symbols-outlined" style="color:#475569;font-size:20px;font-variation-settings:'FILL' 1;">account_balance_wallet</span>
                </div>
            </div>
            <div>
                <p style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Saldo Awal</p>
                <p style="font-size:26px;font-weight:800;color:#0f172a;letter-spacing:-0.5px;line-height:1.1;">Rp {{ number_format($stats['saldo_awal'], 0, ',', '.') }}</p>
                <p style="font-size:11px;color:#94a3b8;margin-top:4px;">Di Awal Hari</p>
            </div>
        </div>

        <!-- Pemasukan -->
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex flex-col justify-between hover:border-emerald-300 hover:shadow-md transition-all group gap-3">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" style="background:#ecfdf5;">
                    <span class="material-symbols-outlined" style="color:#059669;font-size:20px;font-variation-settings:'FILL' 1;">south_west</span>
                </div>
            </div>
            <div>
                <p style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Pemasukan</p>
                <p style="font-size:26px;font-weight:800;color:#059669;letter-spacing:-0.5px;line-height:1.1;">Rp {{ number_format($stats['pemasukan'], 0, ',', '.') }}</p>
                <p style="font-size:11px;color:#94a3b8;margin-top:4px;">Uang Masuk</p>
            </div>
        </div>

        <!-- Pengeluaran -->
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex flex-col justify-between hover:border-rose-300 hover:shadow-md transition-all group gap-3">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" style="background:#fff1f2;">
                    <span class="material-symbols-outlined" style="color:#e11d48;font-size:20px;font-variation-settings:'FILL' 1;">north_east</span>
                </div>
            </div>
            <div>
                <p style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Pengeluaran</p>
                <p style="font-size:26px;font-weight:800;color:#e11d48;letter-spacing:-0.5px;line-height:1.1;">Rp {{ number_format($stats['pengeluaran'], 0, ',', '.') }}</p>
                <p style="font-size:11px;color:#94a3b8;margin-top:4px;">Uang Keluar</p>
            </div>
        </div>

        <!-- Saldo Akhir -->
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex flex-col justify-between hover:border-indigo-300 hover:shadow-md transition-all group gap-3">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" style="background:#eef2ff;">
                    <span class="material-symbols-outlined" style="color:#4f46e5;font-size:20px;font-variation-settings:'FILL' 1;">savings</span>
                </div>
            </div>
            <div>
                <p style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Saldo Akhir</p>
                <p style="font-size:26px;font-weight:800;color:#4f46e5;letter-spacing:-0.5px;line-height:1.1;">Rp {{ number_format($stats['saldo_akhir'], 0, ',', '.') }}</p>
                <p style="font-size:11px;color:#94a3b8;margin-top:4px;">Total Saldo Saat Ini</p>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Waktu</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Deskripsi</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Kategori</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Nominal</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-center">Jenis</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 text-xs font-medium text-slate-600">
                                {{ $trx->created_at->format('H:i') }}
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900">
                                {{ $trx->description }}
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600">
                                {{ $trx->category }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-slate-900 text-xs">
                                Rp {{ number_format($trx->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($trx->type == 'in')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">Masuk</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-rose-50 text-rose-700 border border-rose-200">Keluar</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-xs font-medium text-slate-400">
                                Belum ada transaksi kas hari ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
