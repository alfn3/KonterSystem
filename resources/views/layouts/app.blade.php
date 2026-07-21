<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MobileCell') — Admin Panel</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand': '#6366f1',
                        'brand-dark': '#4f46e5',
                        'brand-light': '#e0e7ff',
                        'sidebar-bg': '#0f172a',
                        'sidebar-hover': '#1e293b',
                        'sidebar-active': '#312e81',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    <style>
        * { box-sizing: border-box; }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        body {
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            background-color: #f1f5f9;
        }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* ── Premium Banner ── */
        .greeting-banner {
            border-radius: 14px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
            box-shadow: 0 4px 20px rgba(99,102,241,0.3);
        }
        .greeting-banner::before {
            content: '';
            position: absolute;
            top: -40px; right: -40px;
            width: 160px; height: 160px;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
            pointer-events: none;
        }
        .greeting-banner::after {
            content: '';
            position: absolute;
            bottom: -20px; right: 80px;
            width: 100px; height: 100px;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
            pointer-events: none;
        }
        .greeting-banner.alert-danger {
            background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
            box-shadow: 0 4px 20px rgba(239,68,68,0.3);
        }
        .greeting-banner.alert-success {
            background: linear-gradient(135deg, #10b981 0%, #047857 100%);
            box-shadow: 0 4px 20px rgba(16,185,129,0.3);
        }

        /* ── Global: Primary button override ── */
        /* Any bg-slate-900 / bg-slate-950 button gets the indigo treatment */
        button.bg-slate-900, a.bg-slate-900,
        button.bg-slate-950, a.bg-slate-950 {
            background-color: #6366f1 !important;
        }
        button.bg-slate-900:hover, a.bg-slate-900:hover,
        button.bg-slate-950:hover, a.bg-slate-950:hover,
        button.hover\:bg-slate-800:hover, a.hover\:bg-slate-800:hover {
            background-color: #4f46e5 !important;
        }
        /* Category active button in inventory */
        .category-btn.bg-slate-900,
        .category-btn.bg-slate-950 {
            background-color: #6366f1 !important;
        }

        /* ── Global: Table styles ── */
        table thead tr {
            background: #f8fafc !important;
        }
        table thead th {
            font-size: 11px !important;
            font-weight: 700 !important;
            color: #94a3b8 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
        }
        table tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.1s;
        }
        table tbody tr:hover {
            background: #f8fafc !important;
        }
        table tbody td {
            font-size: 13px;
            color: #334155;
        }

        /* ── Global: Form inputs ── */
        input[type=text]:not(.form-input),
        input[type=email]:not(.form-input),
        input[type=password]:not(.form-input),
        input[type=number]:not(.form-input),
        input[type=date]:not(.form-input),
        select:not(.nav-select),
        textarea {
            border-radius: 8px !important;
            border-color: #e2e8f0 !important;
            font-size: 13px;
            transition: all 0.2s;
        }
        input:focus, select:focus, textarea:focus {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.12) !important;
            outline: none !important;
        }

        /* ── Global: Status badges ── */
        .badge-online  { background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0; }
        .badge-offline { background:#fef2f2;color:#dc2626;border:1px solid #fecaca; }
        .badge-warning { background:#fffbeb;color:#b45309;border:1px solid #fde68a; }

        /* ── Sidebar ── */
        #sidebar {
            background: #0f172a;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            color: #94a3b8;
            transition: all 0.15s ease;
            cursor: pointer;
        }
        .nav-link:hover {
            background: #1e293b;
            color: #e2e8f0;
        }
        .nav-link.active {
            background: #312e81;
            color: #ffffff;
            font-weight: 600;
        }
        .nav-link.active .nav-icon {
            color: #a5b4fc;
        }
        .nav-icon {
            font-size: 18px !important;
            width: 20px;
            text-align: center;
            color: #64748b;
            transition: color 0.15s;
        }
        .nav-link:hover .nav-icon {
            color: #94a3b8;
        }

        /* ── Submenu ── */
        .submenu-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px 7px 36px;
            border-radius: 6px;
            font-size: 12.5px;
            color: #64748b;
            transition: all 0.15s ease;
        }
        .submenu-item:hover {
            background: #1e293b;
            color: #e2e8f0;
        }
        .submenu-item.active {
            color: #a5b4fc;
            font-weight: 600;
        }
        .submenu-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: #334155;
            flex-shrink: 0;
            transition: background 0.15s;
        }
        .submenu-item.active .submenu-dot {
            background: #818cf8;
        }

        /* ── Section label ── */
        .nav-section-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #475569;
            padding: 0 12px;
            margin-top: 20px;
            margin-bottom: 4px;
        }

        /* ── Topbar ── */
        .search-input {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 7px 12px 7px 36px;
            font-size: 13px;
            outline: none;
            width: 220px;
            transition: all 0.2s;
        }
        .search-input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
        }

        /* ── Status badge ── */
        .live-badge {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 99px;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 600;
            color: #15803d;
        }

        /* ── Alert banners ── */
        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-left: 4px solid #16a34a;
            border-radius: 10px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-left: 4px solid #dc2626;
            border-radius: 10px;
            padding: 12px 16px;
        }

        /* ── Topbar icon buttons ── */
        .topbar-icon-btn {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            transition: all 0.15s;
            cursor: pointer;
            border: none;
            background: transparent;
        }
        .topbar-icon-btn:hover {
            background: #f1f5f9;
            color: #334155;
        }

        /* ── Avatar ── */
        .avatar {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            border: 2px solid #e0e7ff;
        }

        /* ── Page title area ── */
        .page-header {
            margin-bottom: 4px;
        }
        .page-title {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.3px;
        }
        .page-subtitle {
            font-size: 12.5px;
            color: #64748b;
            margin-top: 2px;
        }

        /* ── Card base ── */
        .card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }

        /* ── Submenu animation ── */
        .submenu-container {
            overflow: hidden;
            max-height: 0;
            transition: max-height 0.25s ease-out;
        }
        .submenu-container.open {
            max-height: 300px;
            transition: max-height 0.35s ease-in;
        }

        /* ── Ring focus override ── */
        .focus\:ring-slate-900:focus {
            --tw-ring-color: #6366f1 !important;
        }
        .focus\:border-slate-900:focus {
            border-color: #6366f1 !important;
        }
    </style>
    @stack('styles')
</head>


<body class="text-slate-800 min-h-screen flex">

    <!-- ════════════════════ SIDEBAR ════════════════════ -->
    <aside id="sidebar" class="fixed left-0 top-0 h-full w-60 flex flex-col z-50 overflow-y-auto">

        <!-- Brand -->
        <div class="px-4 pt-5 pb-4 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-lg flex items-center justify-center flex-shrink-0"
                     style="background: linear-gradient(135deg, #6366f1, #4338ca)">
                    <span class="material-symbols-outlined text-white text-lg">storefront</span>
                </div>
                <div>
                    <p class="font-bold text-white text-sm leading-tight">MobileCell</p>
                    <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-widest">Admin Panel</p>
                </div>
            </div>
        </div>

        <!-- Nav -->
        <nav class="flex-1 px-3 py-4">

            <!-- Beranda -->
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
               href="{{ route('dashboard') }}">
                <span class="material-symbols-outlined nav-icon">space_dashboard</span>
                <span>Beranda</span>
            </a>

            <!-- OPERASIONAL -->
            <p class="nav-section-label">Operasional</p>
            @php
                $sidebarFirstBranch = \App\Models\Branch::orderBy('name','asc')->first();
                $sidebarFirstBranchId = $sidebarFirstBranch ? $sidebarFirstBranch->id : 1;
                $isInventoryActive = request()->routeIs('inventory.*') && !request()->routeIs('inventory.history');
                $isOperationalActive = (request()->routeIs('branch.*') && !request()->routeIs('branch.activities'))
                    || request()->routeIs('audit.*') || request()->routeIs('employee.*') || request()->routeIs('customer.*');
            @endphp

            <!-- Inventaris -->
            <button onclick="toggleSubmenu('inv')"
                    class="nav-link w-full {{ $isInventoryActive ? 'active' : '' }}">
                <span class="material-symbols-outlined nav-icon">inventory_2</span>
                <span class="flex-1 text-left">Inventaris</span>
                <span class="material-symbols-outlined text-slate-500 transition-transform duration-200 text-sm"
                      id="arrow-inv" style="{{ $isInventoryActive ? 'transform:rotate(180deg)' : '' }}">expand_more</span>
            </button>
            <div class="submenu-container {{ $isInventoryActive ? 'open' : '' }}" id="sub-inv">
                <a class="submenu-item {{ request()->routeIs('inventory.index') ? 'active' : '' }}"
                   href="{{ route('inventory.index') }}">
                    <span class="submenu-dot"></span>Manajemen Stok
                </a>
                <a class="submenu-item {{ request()->routeIs('inventory.analytics') ? 'active' : '' }}"
                   href="{{ route('inventory.analytics') }}">
                    <span class="submenu-dot"></span>Detail Analitik
                </a>
            </div>

            <!-- Operasional Cabang -->
            <button onclick="toggleSubmenu('ops')"
                    class="nav-link w-full {{ $isOperationalActive ? 'active' : '' }}">
                <span class="material-symbols-outlined nav-icon">store</span>
                <span class="flex-1 text-left">Cabang & SDM</span>
                <span class="material-symbols-outlined text-slate-500 transition-transform duration-200 text-sm"
                      id="arrow-ops" style="{{ $isOperationalActive ? 'transform:rotate(180deg)' : '' }}">expand_more</span>
            </button>
            <div class="submenu-container {{ $isOperationalActive ? 'open' : '' }}" id="sub-ops">
                <a class="submenu-item {{ (request()->routeIs('branch.index') || request()->routeIs('branch.show')) ? 'active' : '' }}"
                   href="{{ route('branch.index') }}">
                    <span class="submenu-dot"></span>Manajemen Cabang
                </a>
                <a class="submenu-item {{ request()->routeIs('employee.index') ? 'active' : '' }}"
                   href="{{ route('employee.index') }}">
                    <span class="submenu-dot"></span>Manajemen Karyawan
                </a>
                <a class="submenu-item {{ request()->routeIs('customer.index') ? 'active' : '' }}"
                   href="{{ route('customer.index') }}">
                    <span class="submenu-dot"></span>Manajemen Pelanggan
                </a>
                <a class="submenu-item {{ request()->routeIs('audit.index') ? 'active' : '' }}"
                   href="{{ route('audit.index') }}">
                    <span class="submenu-dot"></span>Audit Harian
                </a>
            </div>

            <!-- KEUANGAN & LAPORAN -->
            <p class="nav-section-label">Keuangan & Laporan</p>
            @php
                $isFinanceActive = request()->routeIs('qris.*') || request()->routeIs('daily-cash.*');
                $isReportActive  = request()->routeIs('report.*');
                $isActivityActive = request()->routeIs('inventory.history') || request()->routeIs('branch.activities');
            @endphp

            <!-- Keuangan -->
            <button onclick="toggleSubmenu('fin')"
                    class="nav-link w-full {{ $isFinanceActive ? 'active' : '' }}">
                <span class="material-symbols-outlined nav-icon">account_balance_wallet</span>
                <span class="flex-1 text-left">Keuangan</span>
                <span class="material-symbols-outlined text-slate-500 transition-transform duration-200 text-sm"
                      id="arrow-fin" style="{{ $isFinanceActive ? 'transform:rotate(180deg)' : '' }}">expand_more</span>
            </button>
            <div class="submenu-container {{ $isFinanceActive ? 'open' : '' }}" id="sub-fin">
                <a class="submenu-item {{ request()->routeIs('qris.*') ? 'active' : '' }}"
                   href="{{ route('qris.index') }}">
                    <span class="submenu-dot"></span>Konfirmasi QRIS
                </a>
                <a class="submenu-item {{ request()->routeIs('daily-deposits.*') ? 'active' : '' }}"
                   href="{{ route('daily-deposits.index') }}">
                    <span class="submenu-dot"></span>Rincian Setoran
                </a>
                <a class="submenu-item {{ request()->routeIs('daily-cash.*') ? 'active' : '' }}"
                   href="{{ route('daily-cash.index') }}">
                    <span class="submenu-dot"></span>Kas Harian
                </a>
            </div>

            <!-- Laporan -->
            <button onclick="toggleSubmenu('rep')"
                    class="nav-link w-full {{ $isReportActive ? 'active' : '' }}">
                <span class="material-symbols-outlined nav-icon">bar_chart</span>
                <span class="flex-1 text-left">Laporan</span>
                <span class="material-symbols-outlined text-slate-500 transition-transform duration-200 text-sm"
                      id="arrow-rep" style="{{ $isReportActive ? 'transform:rotate(180deg)' : '' }}">expand_more</span>
            </button>
            <div class="submenu-container {{ $isReportActive ? 'open' : '' }}" id="sub-rep">
                <a class="submenu-item {{ request()->routeIs('report.monthly') ? 'active' : '' }}"
                   href="{{ route('report.monthly') }}">
                    <span class="submenu-dot"></span>Laporan Bulanan
                </a>
            </div>

            <!-- Aktivitas -->
            <button onclick="toggleSubmenu('act')"
                    class="nav-link w-full {{ $isActivityActive ? 'active' : '' }}">
                <span class="material-symbols-outlined nav-icon">history_edu</span>
                <span class="flex-1 text-left">Aktivitas</span>
                <span class="material-symbols-outlined text-slate-500 transition-transform duration-200 text-sm"
                      id="arrow-act" style="{{ $isActivityActive ? 'transform:rotate(180deg)' : '' }}">expand_more</span>
            </button>
            <div class="submenu-container {{ $isActivityActive ? 'open' : '' }}" id="sub-act">
                <a class="submenu-item {{ request()->routeIs('inventory.history') ? 'active' : '' }}"
                   href="{{ route('inventory.history') }}">
                    <span class="submenu-dot"></span>Riwayat Pergerakan
                </a>
                <a class="submenu-item {{ request()->routeIs('branch.activities') ? 'active' : '' }}"
                   href="{{ route('branch.activities', $sidebarFirstBranchId) }}">
                    <span class="submenu-dot"></span>Aktivitas Cabang
                </a>
            </div>

            <!-- SISTEM -->
            <p class="nav-section-label">Sistem</p>
            @php $isSystemActive = request()->routeIs('user.*') || request()->routeIs('device.*'); @endphp

            <button onclick="toggleSubmenu('sys')"
                    class="nav-link w-full {{ $isSystemActive ? 'active' : '' }}">
                <span class="material-symbols-outlined nav-icon">settings_suggest</span>
                <span class="flex-1 text-left">Pengaturan</span>
                <span class="material-symbols-outlined text-slate-500 transition-transform duration-200 text-sm"
                      id="arrow-sys" style="{{ $isSystemActive ? 'transform:rotate(180deg)' : '' }}">expand_more</span>
            </button>
            <div class="submenu-container {{ $isSystemActive ? 'open' : '' }}" id="sub-sys">
                <a class="submenu-item {{ request()->routeIs('user.index') ? 'active' : '' }}"
                   href="{{ route('user.index') }}">
                    <span class="submenu-dot"></span>Manajemen User
                </a>
                <a class="submenu-item {{ request()->routeIs('device.index') ? 'active' : '' }}"
                   href="{{ route('device.index') }}">
                    <span class="submenu-dot"></span>Manajemen Device
                </a>
            </div>
        </nav>

        <!-- User Footer -->
        <div class="px-3 py-4 border-t border-slate-800">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                     style="background: linear-gradient(135deg, #6366f1, #4338ca)">
                    {{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 1)) : 'A' }}
                </div>
                <div class="min-w-0">
                    <p class="text-white text-xs font-semibold truncate">
                        {{ auth()->check() ? auth()->user()->name : 'Admin' }}
                    </p>
                    <p class="text-slate-500 text-[10px] truncate">
                        {{ auth()->check() ? auth()->user()->email : '' }}
                    </p>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" id="logout-form" class="hidden">@csrf</form>
            <button onclick="document.getElementById('logout-form').submit()"
                    class="nav-link w-full text-slate-400 hover:text-red-400">
                <span class="material-symbols-outlined nav-icon" style="font-size:16px!important">logout</span>
                <span class="text-xs">Keluar</span>
            </button>
        </div>
    </aside>

    <!-- ════════════════════ MAIN ════════════════════ -->
    <main class="flex-1 min-h-screen flex flex-col" style="margin-left: 240px;">

        <!-- Topbar -->
        <header class="shrink-0 sticky top-0 z-40 bg-white border-b border-slate-200"
                style="height:60px; display:flex; align-items:center; padding:0 24px; gap:16px;">

            <!-- Left: Title + Search -->
            <div class="flex items-center gap-4 flex-1">
                <span class="font-bold text-slate-900 text-sm tracking-wide uppercase">
                    @if(request()->routeIs('inventory.history') || request()->routeIs('branch.activities'))
                        Aktivitas
                    @elseif(request()->routeIs('inventory.*'))
                        Inventaris
                    @elseif(request()->routeIs('branch.*') || request()->routeIs('audit.*') || request()->routeIs('employee.*') || request()->routeIs('customer.*'))
                        Operasional
                    @elseif(request()->routeIs('report.*'))
                        Laporan
                    @elseif(request()->routeIs('qris.*'))
                        Keuangan
                    @elseif(request()->routeIs('user.*') || request()->routeIs('device.*'))
                        Sistem
                    @else
                        Beranda
                    @endif
                </span>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-base pointer-events-none">search</span>
                    <input class="search-input" placeholder="Cari data..." type="text" id="global-search">
                </div>
            </div>

            <!-- Right: actions -->
            <div class="flex items-center gap-2">
                <span class="live-badge">
                    <span style="width:7px;height:7px;border-radius:50%;background:#22c55e;display:inline-block;animation:pulse 2s infinite;"></span>
                    Live
                </span>
                <span class="text-xs font-medium text-slate-400 hidden md:block" style="white-space:nowrap">
                    {{ now()->translatedFormat('d M Y') }}
                </span>
                <div style="width:1px;height:20px;background:#e2e8f0;margin:0 4px;"></div>
                <button class="topbar-icon-btn" title="Notifikasi">
                    <span class="material-symbols-outlined" style="font-size:18px">notifications</span>
                </button>
                <button class="topbar-icon-btn" title="Bantuan">
                    <span class="material-symbols-outlined" style="font-size:18px">help_outline</span>
                </button>
                <div class="avatar ml-1" title="{{ auth()->check() ? auth()->user()->name : 'Admin' }}">
                    {{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 1)) : 'A' }}
                </div>
            </div>
        </header>

        <!-- Content Canvas -->
        <div class="p-6" style="min-height: calc(100vh - 60px);">


            <!-- Flash messages -->
            @if(session('success'))
                <div class="alert-success mb-5">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span class="material-symbols-outlined text-green-600" style="font-size:20px;font-variation-settings:'FILL' 1">check_circle</span>
                        <p class="text-sm font-semibold text-slate-800">{{ session('success') }}</p>
                    </div>
                    <button onclick="this.parentElement.remove()"
                            class="text-slate-400 hover:text-slate-600 material-symbols-outlined"
                            style="font-size:18px;cursor:pointer;background:none;border:none;">close</button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert-error mb-5">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span class="material-symbols-outlined text-red-500" style="font-size:20px;font-variation-settings:'FILL' 1">error</span>
                            <p class="text-sm font-semibold text-slate-800">Terjadi kesalahan input:</p>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()"
                                class="material-symbols-outlined text-slate-400 hover:text-slate-600"
                                style="font-size:18px;cursor:pointer;background:none;border:none;">close</button>
                    </div>
                    <ul style="list-style:disc;padding-left:32px;font-size:12px;color:#475569;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Page Header -->
            <div id="page-header" class="page-header mb-5">
                <h1 class="page-title">
                    @hasSection('page_title')
                        @yield('page_title')
                    @else
                        @yield('title')
                    @endif
                </h1>
                <p class="page-subtitle">@yield('subtitle', 'Keterangan halaman')</p>
            </div>

            <!-- Page Header Skeleton -->
            <div id="page-header-skeleton" class="page-header mb-5 hidden animate-pulse flex flex-col gap-2">
                <div class="h-6 bg-slate-200 rounded-md w-1/3"></div>
                <div class="h-3 bg-slate-200 rounded-md w-1/4"></div>
            </div>

            <div id="page-content-wrapper" class="transition-opacity duration-200">
                @yield('content')
            </div>

            <!-- Global Skeleton Loader (Hidden by Default) -->
            <div id="global-skeleton" class="hidden w-full animate-pulse">
                <!-- Generic Skeleton Dashboard Layout -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="h-[120px] bg-white border border-slate-100 rounded-xl shadow-sm"></div>
                    <div class="h-[120px] bg-white border border-slate-100 rounded-xl shadow-sm"></div>
                    <div class="h-[120px] bg-white border border-slate-100 rounded-xl shadow-sm"></div>
                    <div class="h-[120px] bg-white border border-slate-100 rounded-xl shadow-sm"></div>
                </div>
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                    <div class="xl:col-span-2 space-y-6">
                        <div class="h-[300px] bg-white border border-slate-100 rounded-xl shadow-sm"></div>
                        <div class="h-[200px] bg-white border border-slate-100 rounded-xl shadow-sm"></div>
                    </div>
                    <div class="space-y-6">
                        <div class="h-[525px] bg-white border border-slate-100 rounded-xl shadow-sm"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <style>
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }
    </style>

    <script>
        function toggleSubmenu(key) {
            const sub   = document.getElementById('sub-' + key);
            const arrow = document.getElementById('arrow-' + key);
            const isOpen = sub.classList.contains('open');
            if (isOpen) {
                sub.classList.remove('open');
                if (arrow) arrow.style.transform = '';
            } else {
                sub.classList.add('open');
                if (arrow) arrow.style.transform = 'rotate(180deg)';
            }
        }

        // Global Skeleton Logic
        window.triggerGlobalLoading = function(forceGlobal = false) {
            if (!forceGlobal && typeof window.customTriggerLoading === 'function') {
                window.customTriggerLoading();
                return;
            }

            // If the current page has a custom skeleton (like branch dashboard), don't show the global one.
            if (!forceGlobal && document.getElementById('dashboard-skeleton')) {
                // Let the page handle its own skeleton if it has a showSkeletonAndNavigate function
                if (typeof showSkeletonAndNavigate === 'function') {
                    showSkeletonAndNavigate();
                } else {
                    document.getElementById('page-content-wrapper').style.display = 'none';
                    document.getElementById('dashboard-skeleton').classList.remove('hidden');
                }
                return;
            }
            
            const wrapper = document.getElementById('page-content-wrapper');
            const skeleton = document.getElementById('global-skeleton');
            const header = document.getElementById('page-header');
            const headerSkeleton = document.getElementById('page-header-skeleton');
            
            if (wrapper && skeleton) {
                wrapper.style.display = 'none';
                skeleton.classList.remove('hidden');
            }
            if (header && headerSkeleton) {
                header.classList.add('hidden');
                headerSkeleton.classList.remove('hidden');
            }
        };

        document.addEventListener('DOMContentLoaded', () => {
            const wrapper = document.getElementById('page-content-wrapper');
            const skeleton = document.getElementById('global-skeleton');

            // Intercept standard navigation links
            const links = document.querySelectorAll('a[href]:not([target="_blank"]):not([href^="#"]):not([href^="javascript"])');
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    // Ignore clicks with modifier keys (new tab/window)
                    if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;
                    
                    // Trigger skeleton if the link is not just an empty hash
                    const href = this.getAttribute('href');
                    if (href && href !== '#' && href !== '') {
                        const isSidebar = this.closest('aside') !== null;
                        window.triggerGlobalLoading(isSidebar);
                    }
                });
            });

            // Intercept form submissions
            const forms = document.querySelectorAll('form:not([target="_blank"])');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    // Note: forms within the page usually trigger local skeletons unless otherwise needed
                    window.triggerGlobalLoading(false);
                });
            });

            // Handle back button caching (bfcache)
            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    if (typeof window.customRestoreLoading === 'function') {
                        window.customRestoreLoading();
                    }
                    if (wrapper && skeleton) {
                        wrapper.style.display = 'block';
                        skeleton.classList.add('hidden');
                    }
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
