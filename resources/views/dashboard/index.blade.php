@extends('layouts.app')

@section('title', 'Beranda')
@section('subtitle', 'Pusat pantauan aktivitas transaksi, kesehatan stok konter, dan metrik performa ritel secara real-time.')

@push('styles')
<style>
    /* ── Greeting Banner ── */
    .greeting-banner {
        background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
        border-radius: 14px;
        padding: 20px 24px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 4px 20px rgba(99,102,241,0.3);
        position: relative;
        overflow: hidden;
    }
    .greeting-banner::before {
        content: '';
        position: absolute;
        top: -40px; right: -40px;
        width: 160px; height: 160px;
        border-radius: 50%;
        background: rgba(255,255,255,0.06);
    }
    .greeting-banner::after {
        content: '';
        position: absolute;
        bottom: -20px; right: 80px;
        width: 100px; height: 100px;
        border-radius: 50%;
        background: rgba(255,255,255,0.04);
    }

    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }
    .section-title {
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 8px;
    }
</style>
@endpush

@section('content')

    {{-- ── Greeting Banner ── --}}
    <section id="greeting-alert" class="greeting-banner mb-6">
        <div style="background:rgba(255,255,255,0.15);border-radius:12px;padding:10px;flex-shrink:0;">
            <span class="material-symbols-outlined text-white text-2xl"
                  style="font-variation-settings:'FILL' 1;display:block;">campaign</span>
        </div>
        <div class="flex-1" style="position:relative;z-index:1;">
            <h2 style="font-size:16px;font-weight:700;color:white;margin-bottom:2px;">
                Halo {{ $greeting['user'] }} 👋
            </h2>
            <p style="font-size:12.5px;color:rgba(255,255,255,0.8);">
                <strong style="color:white;">{{ $greeting['alert_title'] }}:</strong>
                {{ $greeting['alert_message'] }}
            </p>
        </div>
        <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;position:relative;z-index:1;">
            <button onclick="window.location.href='{{ route('inventory.index') }}'"
                    style="background:rgba(255,255,255,0.2);color:white;border:1px solid rgba(255,255,255,0.3);border-radius:8px;padding:8px 16px;font-size:12px;font-weight:700;cursor:pointer;backdrop-filter:blur(4px);transition:background 0.2s;font-family:inherit;"
                    onmouseover="this.style.background='rgba(255,255,255,0.3)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                Lihat Stok
            </button>
            <button onclick="document.getElementById('greeting-alert').style.display='none'"
                    style="background:rgba(255,255,255,0.1);color:rgba(255,255,255,0.7);border:none;border-radius:8px;width:32px;height:32px;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background 0.2s;font-family:inherit;"
                    onmouseover="this.style.background='rgba(255,255,255,0.2)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                <span class="material-symbols-outlined" style="font-size:16px;">close</span>
            </button>
        </div>
    </section>

    {{-- ── Metric Pulse Section ── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
        @php
            $metricIcons = [
                0 => ['icon' => 'paid',          'bg' => '#eef2ff', 'color' => '#6366f1'],
                1 => ['icon' => 'trending_up',   'bg' => '#f0fdf4', 'color' => '#16a34a'],
                2 => ['icon' => 'inventory_2',   'bg' => '#fff7ed', 'color' => '#ea580c'],
                3 => ['icon' => 'storefront',    'bg' => '#fdf4ff', 'color' => '#9333ea'],
            ];
        @endphp
        @foreach($metrics as $i => $metric)
            @php $mi = $metricIcons[$i] ?? ['icon'=>'bar_chart','bg'=>'#f8fafc','color'=>'#64748b']; @endphp
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex flex-col justify-between hover:border-indigo-300 hover:shadow-md transition-all group gap-3">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $mi['bg'] }};">
                        <span class="material-symbols-outlined" style="color:{{ $mi['color'] }};font-size:20px;font-variation-settings:'FILL' 1;">{{ $mi['icon'] }}</span>
                    </div>
                    @if($metric['trend_direction'] == 'up')
                        <span style="font-size:11px;font-weight:700;color:#16a34a;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:99px;padding:2px 8px;display:flex;align-items:center;gap:2px;">
                            <span class="material-symbols-outlined" style="font-size:11px;">trending_up</span>
                            {{ $metric['trend'] }}
                        </span>
                    @elseif($metric['trend_direction'] == 'down')
                        <span style="font-size:11px;font-weight:700;color:#dc2626;background:#fef2f2;border:1px solid #fecaca;border-radius:99px;padding:2px 8px;display:flex;align-items:center;gap:2px;">
                            <span class="material-symbols-outlined" style="font-size:11px;">trending_down</span>
                            {{ $metric['trend'] }}
                        </span>
                    @else
                        <span style="font-size:11px;font-weight:600;color:#94a3b8;">{{ $metric['trend'] }}</span>
                    @endif
                </div>
                <div>
                    <p style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">{{ $metric['title'] }}</p>
                    <p style="font-size:26px;font-weight:800;color:#0f172a;letter-spacing:-0.5px;line-height:1.1;">{{ $metric['value'] }}</p>
                    <p style="font-size:11px;color:#94a3b8;margin-top:4px;">{{ $metric['comparison'] }}</p>
                </div>
                @if(isset($metric['link']))
                    <a href="{{ $metric['link'] }}"
                       style="font-size:11px;font-weight:700;color:#6366f1;display:flex;align-items:center;gap:3px;text-decoration:none;margin-top:auto;" class="group-hover:text-indigo-700">
                        Selengkapnya
                        <span class="material-symbols-outlined" style="font-size:13px;">arrow_forward</span>
                    </a>
                @endif
            </div>
        @endforeach
    </div>

    {{-- ── Store Health Monitor ── --}}
    <div>
        <div class="section-header">
            <h3 class="section-title">
                <span class="material-symbols-outlined" style="font-size:18px;color:#6366f1;font-variation-settings:'FILL' 1">store</span>
                Store Health Monitor
                <span style="font-size:11px;font-weight:500;color:#94a3b8;">
                    (Data Kemarin: {{ now()->subDay()->translatedFormat('d F Y') }})
                </span>
            </h3>
            <div style="display:flex;gap:6px;">
                <button style="background:white;border:1px solid #e2e8f0;border-radius:8px;padding:6px 10px;cursor:pointer;display:flex;align-items:center;gap:4px;font-size:12px;color:#64748b;transition:all 0.15s;"
                        onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                    <span class="material-symbols-outlined" style="font-size:15px;">filter_list</span>
                    Filter
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($branches as $branch)
                @php
                    $statusColor = match($branch['border_color'] ?? '') {
                        'border-emerald-500'     => '#22c55e',
                        'border-amber-500'       => '#f59e0b',
                        'border-red-500', 'border-commander-error' => '#ef4444',
                        default                  => '#94a3b8',
                    };
                @endphp
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm hover:shadow-md hover:border-indigo-300 transition-all duration-300 flex flex-col overflow-hidden group">
                    
                    <!-- Card Header -->
                    <div style="padding:16px 16px 12px;border-bottom:1px solid #f1f5f9;">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;">
                            <div style="min-width:0;">
                                <h4 style="font-size:14px;font-weight:800;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    {{ $branch['name'] }}
                                </h4>
                                <p style="font-size:11px;color:#94a3b8;display:flex;align-items:center;gap:3px;margin-top:2px;">
                                    <span class="material-symbols-outlined" style="font-size:12px;">location_on</span>
                                    <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $branch['address'] }}</span>
                                </p>
                            </div>
                            <span style="flex-shrink:0;font-size:10px;font-weight:700;padding:3px 8px;border-radius:99px;
                                background:{{ $statusColor }}18;color:{{ $statusColor }};border:1px solid {{ $statusColor }}33;">
                                {{ $branch['status'] ?? 'Online' }}
                            </span>
                        </div>
                    </div>

                    <!-- Metrics -->
                    <div class="p-5 flex-1 space-y-4">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Penjualan</span>
                                <span class="text-xs font-black text-slate-900">{{ $branch['revenue_yesterday'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Selisih</span>
                                <span class="text-xs font-black {{ $branch['gap_class'] }}">{{ $branch['gap_yesterday'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pelanggan</span>
                                <span class="text-xs font-black text-slate-900">{{ $branch['customer_count'] }}</span>
                            </div>
                        </div>

                        <!-- Attendance row -->
                        <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kehadiran</span>
                            @if($branch['yesterday_attendance'])
                                <span class="flex items-center gap-1.5 text-xs font-bold text-green-600">
                                    <span class="material-symbols-outlined text-sm">how_to_reg</span>
                                    {{ $branch['yesterday_attendance'] }}
                                </span>
                            @else
                                <span class="flex items-center gap-1.5 text-xs font-semibold text-slate-400">
                                    <span class="material-symbols-outlined text-sm">pending_actions</span>
                                    Belum Absen
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Card Footer -->
                    <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between group-hover:bg-indigo-50/50 transition-colors">
                        <span class="text-[10px] font-semibold text-slate-400">
                            {{ now()->subDay()->translatedFormat('d M Y') }}
                        </span>
                        <a href="{{ route('branch.show', $branch['id']) }}"
                           class="flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-[10px] font-bold uppercase tracking-wider hover:bg-indigo-700 transition-all shadow-sm">
                            Detail
                            <span class="material-symbols-outlined text-xs">chevron_right</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

@endsection

@push('scripts')
<script>
    // Close dropdowns on outside click
    document.addEventListener('click', () => {
        document.querySelectorAll('.branch-dropdown').forEach(d => d.classList.add('hidden'));
    });
</script>
@endpush
