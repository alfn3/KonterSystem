@extends('layouts.app')

@section('title', 'Audit Harian')
@section('subtitle', 'Verifikasi fisik inventoris dan rekonsiliasi kas harian untuk mencegah selisih.')

@section('content')

    <!-- Smart Greeting / Alert Banner (Reskinned) -->
    @php
        $isDanger = strpos(strtolower($alert['title']), 'peringatan') !== false;
        $bannerClass = $isDanger ? 'alert-danger' : 'alert-success';
        $iconName = $isDanger ? 'warning' : 'check_circle';
    @endphp
    <section id="greeting-alert" class="greeting-banner {{ $bannerClass }} mb-6 rounded-xl relative z-10">
        <div style="background:rgba(255,255,255,0.15);border-radius:12px;padding:10px;flex-shrink:0;">
            <span class="material-symbols-outlined text-white text-2xl"
                  style="font-variation-settings:'FILL' 1;display:block;">{{ $iconName }}</span>
        </div>
        <div class="flex-1" style="position:relative;z-index:1;">
            <h2 style="font-size:16px;font-weight:700;color:white;margin-bottom:2px;">
                {{ $alert['title'] }}
            </h2>
            <p style="font-size:12.5px;color:rgba(255,255,255,0.8);">
                {{ $alert['message'] }}
            </p>
        </div>
        <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;position:relative;z-index:1;">
            <button style="background:rgba(255,255,255,0.2);color:white;border:1px solid rgba(255,255,255,0.3);border-radius:8px;padding:8px 16px;font-size:12px;font-weight:700;cursor:pointer;backdrop-filter:blur(4px);transition:background 0.2s;font-family:inherit;"
                    onmouseover="this.style.background='rgba(255,255,255,0.3)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                {{ $alert['action_text'] }}
            </button>
        </div>
    </section>
    <!-- Custom Page Skeleton Loader (Hidden by Default) -->
    <div id="custom-page-skeleton" class="hidden w-full animate-pulse bg-white p-4 sm:p-6 rounded-xl border border-slate-200 shadow-sm mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
            <div class="h-32 bg-slate-50 border border-slate-100 rounded-xl"></div>
            <div class="h-32 bg-slate-50 border border-slate-100 rounded-xl"></div>
            <div class="h-32 bg-slate-50 border border-slate-100 rounded-xl"></div>
            <div class="h-32 bg-slate-50 border border-slate-100 rounded-xl"></div>
        </div>
        <div class="h-64 bg-slate-50 border border-slate-100 rounded-xl"></div>
    </div>

    <!-- Main Content Wrapper -->
    <div id="main-content" class="bg-white p-4 sm:p-6 rounded-xl border border-slate-200 shadow-sm mb-8">


    <!-- Metrics Grid (Dashboard Style) -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
        @php
            $metricIcons = [
                'Audits Completed' => ['icon' => 'task_alt', 'bg' => '#f0fdf4', 'color' => '#16a34a'],
                'Pending Audits'   => ['icon' => 'pending_actions', 'bg' => '#fef2f2', 'color' => '#dc2626'],
                'Total Discrepancy'=> ['icon' => 'payments', 'bg' => '#fff7ed', 'color' => '#ea580c'],
                'Stock Accuracy'   => ['icon' => 'inventory', 'bg' => '#eef2ff', 'color' => '#6366f1'],
            ];
        @endphp
        @foreach($metrics as $metric)
            @php $mi = $metricIcons[$metric['title']] ?? ['icon'=>'bar_chart','bg'=>'#f8fafc','color'=>'#64748b']; @endphp
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex flex-col justify-between hover:border-indigo-300 hover:shadow-md transition-all group gap-3">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $mi['bg'] }};">
                        <span class="material-symbols-outlined" style="color:{{ $mi['color'] }};font-size:20px;font-variation-settings:'FILL' 1;">{{ $mi['icon'] }}</span>
                    </div>
                    @if($metric['trend'])
                        @if(isset($metric['trend_type']) && $metric['trend_type'] == 'up')
                            <span style="font-size:11px;font-weight:700;color:#16a34a;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:99px;padding:2px 8px;display:flex;align-items:center;gap:2px;">
                                <span class="material-symbols-outlined" style="font-size:11px;">trending_up</span>
                                {{ $metric['trend'] }}
                            </span>
                        @else
                            <span style="font-size:11px;font-weight:700;color:#dc2626;background:#fef2f2;border:1px solid #fecaca;border-radius:99px;padding:2px 8px;display:flex;align-items:center;gap:2px;">
                                <span class="material-symbols-outlined" style="font-size:11px;">error</span>
                                {{ $metric['trend'] }}
                            </span>
                        @endif
                    @endif
                </div>
                <div>
                    <p style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">{{ $metric['title'] }}</p>
                    <p style="font-size:26px;font-weight:800;color:#0f172a;letter-spacing:-0.5px;line-height:1.1;">
                        @if(isset($metric['prefix']))
                            <span style="font-size:14px;color:#64748b;font-weight:600;">{{ $metric['prefix'] }}</span>
                        @endif
                        {{ $metric['value'] }}
                    </p>
                    @if(isset($metric['progress']))
                        <div class="w-full bg-slate-100 h-1.5 rounded-full mt-3">
                            <div class="bg-indigo-500 h-full rounded-full" style="width: {{ $metric['progress'] }}%"></div>
                        </div>
                    @elseif(isset($metric['desc']))
                        <p style="font-size:11px;color:#64748b;margin-top:8px;">{{ $metric['desc'] }}</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>



    <!-- Audit Log Table -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-slate-400 text-sm">assignment</span>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Detailed Audit Log</span>
            </div>
            <div class="flex gap-2">
                <button onclick="openAddAuditModal()" class="px-4 py-1.5 bg-slate-900 text-white rounded-lg flex items-center gap-2 text-xs font-bold hover:bg-slate-800 transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-sm">add_circle</span> Mulai Audit Baru
                </button>
                <button class="px-3 py-1.5 text-xs font-bold border border-slate-200 rounded-lg flex items-center gap-2 hover:bg-slate-50 transition-all">
                    <span class="material-symbols-outlined text-sm">filter_list</span> Filter
                </button>
                <button class="px-3 py-1.5 text-xs font-bold border border-slate-200 rounded-lg flex items-center gap-2 hover:bg-slate-50 transition-all">
                    <span class="material-symbols-outlined text-sm">download</span> Export
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Date/Time</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Branch</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Auditor Name</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($audit_logs as $log)
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-900">{{ $log['date'] }}</span>
                                    <span class="text-[11px] text-slate-400">{{ $log['time'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full {{ $log['branch_dot'] }}"></span>
                                    <span class="text-sm font-semibold text-slate-700">{{ $log['branch'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-slate-500">{{ $log['auditor'] }}</td>
                            <td class="px-6 py-4">
                                <span class="text-[9px] px-2.5 py-1 rounded-full font-extrabold uppercase border {{ $log['status_class'] }} flex items-center w-fit gap-1">
                                    <span class="material-symbols-outlined text-[12px]" style="font-variation-settings: 'FILL' 1;">{{ $log['status_icon'] }}</span>
                                    {{ $log['status'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button class="text-xs font-bold text-slate-900 hover:underline">View Detail</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
            <p class="text-xs text-slate-500">Menampilkan 3 dari 124 audits</p>
            <div class="flex items-center gap-2">
                <button class="p-1.5 rounded border border-slate-200 bg-white text-slate-400 hover:text-slate-600 disabled:opacity-50" disabled>
                    <span class="material-symbols-outlined text-sm">chevron_left</span>
                </button>
                <button class="px-3 py-1.5 rounded border border-slate-900 bg-slate-900 text-white text-xs font-bold">1</button>
                <button class="px-3 py-1.5 rounded border border-slate-200 bg-white text-slate-600 text-xs font-medium hover:bg-slate-50">2</button>
                <button class="px-3 py-1.5 rounded border border-slate-200 bg-white text-slate-600 text-xs font-medium hover:bg-slate-50">3</button>
                <button class="p-1.5 rounded border border-slate-200 bg-white text-slate-600 hover:text-slate-900">
                    <span class="material-symbols-outlined text-sm">chevron_right</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Add Audit Modal -->
    <div id="add-audit-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[999] flex items-center justify-center hidden p-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-500">assignment</span>
                    Mulai Audit Baru
                </h3>
                <button onclick="closeAddAuditModal()" class="material-symbols-outlined text-slate-400 hover:text-slate-600 cursor-pointer">close</button>
            </div>
            <form action="{{ route('audit.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Cabang</label>
                    <select name="branch_name" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-semibold text-slate-700 outline-none focus:ring-1 focus:ring-slate-900 cursor-pointer">
                        @foreach($branchesList as $b)
                            <option value="{{ $b->name }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Nama Auditor</label>
                    <input type="text" name="auditor" required placeholder="e.g. Ahmad Fauzi" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Status Awal</label>
                    <select name="status" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-semibold text-slate-700 outline-none focus:ring-1 focus:ring-slate-900 cursor-pointer">
                        <option value="Selesai">Selesai (Cocok)</option>
                        <option value="Selisih">Selisih (Discrepancy)</option>
                        <option value="Pending">Pending</option>
                    </select>
                </div>
                <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                    <button type="button" onclick="closeAddAuditModal()" class="px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-lg text-xs font-bold uppercase cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-bold uppercase cursor-pointer">Mulai Audit</button>
                </div>
            </form>
        </div>
    </div>


    </div> <!-- End Main Content Wrapper -->
@endsection

@push('scripts')
<script>
    function openAddAuditModal() {
        document.getElementById('add-audit-modal').classList.remove('hidden');
    }
    function closeAddAuditModal() {
        document.getElementById('add-audit-modal').classList.add('hidden');
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

