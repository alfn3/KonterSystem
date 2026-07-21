@extends('layouts.app')

@section('title', 'Manajemen Stok')
@section('subtitle', 'Pantau ketersediaan barang, kelola SKU, HPP, dan margin produk di gudang maupun cabang.')

@push('styles')
<style>
    /* ── Greeting Banner ── */
    .greeting-banner {
        border-radius: 14px;
        padding: 20px 24px;
        display: flex;
        align-items: center;
        gap: 16px;
        position: relative;
        overflow: hidden;
    }
    .greeting-banner.banner-inventory {
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
    }
    .greeting-banner::after {
        content: '';
        position: absolute;
        bottom: -20px; right: 80px;
        width: 100px; height: 100px;
        border-radius: 50%;
        background: rgba(255,255,255,0.04);
    }
</style>
@endpush

@section('content')

    <!-- Filter & Controls (Styled as Premium Banner) -->
    <div class="greeting-banner banner-inventory mb-0 rounded-t-xl rounded-b-none relative z-10 flex-col sm:flex-row items-center justify-between gap-4" style="padding: 16px 20px;">
        <div class="flex items-center gap-2 w-full sm:w-auto" style="position:relative;z-index:1;">
            <div class="flex items-center gap-1.5 px-2.5 py-1.5 bg-white/95 border border-white/20 rounded-lg shadow-sm">
                <span class="material-symbols-outlined text-sm text-indigo-600">store</span>
                <select id="branch-selector" onchange="toggleBranchView()" class="bg-transparent border-none p-0 text-xs font-bold text-slate-800 focus:ring-0 cursor-pointer outline-none">
                    <option value="Gudang" {{ $selectedBranch === 'Gudang' ? 'selected' : '' }}>Gudang</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->name }}" {{ $selectedBranch === $b->name ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-1.5 px-2.5 py-1.5 bg-white/95 border border-white/20 rounded-lg shadow-sm">
                <span class="material-symbols-outlined text-sm text-indigo-600">calendar_today</span>
                <input id="date-selector" onchange="toggleDateView()" class="bg-transparent border-none p-0 text-xs font-bold text-slate-800 focus:ring-0 cursor-pointer w-24 outline-none" type="date" value="{{ $selectedDate }}">
            </div>
            <div class="flex items-center gap-1.5 px-2.5 py-1.5 bg-white/95 border border-white/20 rounded-lg shadow-sm">
                <span class="material-symbols-outlined text-sm text-indigo-600">category</span>
                <select id="category-selector" onchange="filterCategory(this.value)" class="bg-transparent border-none p-0 text-xs font-bold text-slate-800 focus:ring-0 cursor-pointer outline-none">
                    <option value="Semua">Semua Kategori</option>
                    <option value="Perdana">Perdana</option>
                    <option value="Voucher">Voucher</option>
                    <option value="Aksesoris">Aksesoris</option>
                </select>
            </div>
            <div class="relative shadow-sm rounded-lg">
                <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-indigo-600 text-sm">search</span>
                <input id="product-search" onkeyup="searchProducts()" class="w-40 text-xs border border-white/20 rounded-lg pl-8 pr-3 py-1.5 focus:ring-1 focus:ring-white outline-none transition-all bg-white/95 text-slate-800 font-bold placeholder-slate-400" placeholder="Cari produk..." type="text">
            </div>
        </div>
        <!-- Right Group: Actions -->
        <div class="flex items-center gap-2" style="position:relative;z-index:1;">
            <button class="p-1.5 bg-white/20 border border-white/30 rounded-lg text-white hover:bg-white/30 transition-all cursor-pointer backdrop-blur-sm" title="Ekspor CSV">
                <span class="material-symbols-outlined text-[18px]">download</span>
            </button>
            <button id="restock-btn" onclick="openRestockModal()" class="px-3 py-1.5 bg-white text-indigo-600 border border-white/20 rounded-lg flex items-center gap-1.5 text-xs font-extrabold hover:bg-slate-50 transition-all cursor-pointer shadow-sm">
                <span class="material-symbols-outlined text-sm">package_2</span> Restok
            </button>
            <button id="add-product-btn" onclick="openAddProductModal()" class="px-3 py-1.5 bg-slate-900 text-white rounded-lg flex items-center gap-1.5 text-xs font-bold hover:bg-slate-800 transition-all cursor-pointer shadow-sm hidden">
                <span class="material-symbols-outlined text-sm">add_box</span> Tambah Produk
            </button>
        </div>
    </div>

    <!-- Custom Page Skeleton Loader (Hidden by Default) -->
    <div id="custom-page-skeleton" class="hidden w-full animate-pulse bg-white p-4 sm:p-6 rounded-b-xl border border-slate-200 border-t-0 shadow-sm mb-8" style="margin-top: 0;">
        <div class="h-10 bg-slate-100 rounded w-full mb-4"></div>
        <div class="space-y-3">
            <div class="h-12 bg-slate-50 border border-slate-100 rounded"></div>
            <div class="h-12 bg-slate-50 border border-slate-100 rounded"></div>
            <div class="h-12 bg-slate-50 border border-slate-100 rounded"></div>
            <div class="h-12 bg-slate-50 border border-slate-100 rounded"></div>
            <div class="h-12 bg-slate-50 border border-slate-100 rounded"></div>
        </div>
    </div>

    <!-- Main Content Wrapper -->
    <div id="main-content" class="bg-white p-4 sm:p-6 rounded-b-xl border border-slate-200 border-t-0 shadow-sm mb-8" style="margin-top: 0;">



    <!-- Table Section -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" id="product-table">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Produk</th>
                                @if($selectedBranch === 'Gudang')
                                <th class="px-4 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">HPP</th>
                                @endif
                                <th class="px-4 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Harga Jual</th>
                                @if($selectedBranch === 'Gudang')
                                <th class="px-4 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Margin</th>
                                @endif
                                <th class="px-4 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Awal</th>
                                <th class="px-4 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Topup</th>
                                <th class="px-4 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Akhir</th>
                                <th class="px-4 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">{{ $selectedBranch === 'Gudang' ? 'Keluar (-)' : 'Terjual (-)' }}</th>
                                <th class="px-4 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-center">Status</th>
                                <th class="px-4 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($products as $product)
                                <tr class="hover:bg-slate-50 transition-colors group {{ $product['row_class'] }}" data-category="{{ $product['category'] }}">
                                    {{-- Kolom: Produk (Nama, Brand, SKU) --}}
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <div class="flex items-center gap-1.5 mb-1">
                                                <span class="text-[8px] px-1.5 py-0.5 rounded font-extrabold border {{ $product['category_class'] }} uppercase tracking-wider">{{ $product['category'] }}</span>
                                                <span class="text-[9px] font-bold {{ $product['brand_class'] }} uppercase tracking-tight">{{ $product['brand'] }}</span>
                                            </div>
                                            <span class="text-sm font-bold text-slate-900 product-name leading-snug">{{ $product['name'] }}</span>
                                            <span class="text-[11px] text-slate-400 font-medium mt-0.5">SKU: {{ $product['sku'] }}</span>
                                        </div>
                                    </td>

                                    {{-- Kolom: HPP (Gudang only) --}}
                                    @if($selectedBranch === 'Gudang')
                                    <td class="px-4 py-4 text-right">
                                        <span class="text-xs font-semibold text-slate-500">{{ $product['hpp_formatted'] }}</span>
                                    </td>
                                    @endif

                                    {{-- Kolom: Harga Jual --}}
                                    <td class="px-4 py-4 text-right">
                                        <span class="text-xs font-black text-indigo-600">{{ $product['price_formatted'] }}</span>
                                    </td>

                                    {{-- Kolom: Margin (Gudang only) --}}
                                    @if($selectedBranch === 'Gudang')
                                    <td class="px-4 py-4 text-right">
                                        <span class="text-[11px] font-extrabold text-emerald-600 bg-emerald-50 px-2 py-1 rounded border border-emerald-100 whitespace-nowrap">{{ $product['margin'] }}</span>
                                    </td>
                                    @endif

                                    <td class="px-6 py-4 text-right text-xs font-bold text-slate-600">
                                        @if(isset($product['initial_warning']) && $product['initial_warning'])
                                            <div class="flex items-center justify-end gap-1">
                                                {{ $product['initial'] }}<span class="material-symbols-outlined text-[13px] text-amber-500" style="font-variation-settings: 'FILL' 1;">error</span>
                                            </div>
                                        @else
                                            {{ $product['initial'] }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right text-xs font-bold text-emerald-600">
                                        @if($product['incoming'] > 0)
                                            <div class="flex items-center justify-end gap-1">
                                                +{{ $product['incoming'] }}
                                                @if($product['incoming_warning'])
                                                    <span class="material-symbols-outlined text-[13px] text-amber-500" style="font-variation-settings: 'FILL' 1;">error</span>
                                                @endif
                                            </div>
                                        @else
                                            +0
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right text-xs font-black {{ $product['status'] == 'Kritis' || $product['status'] == 'Habis' ? 'text-commander-error' : 'text-slate-900' }}">
                                        {{ $product['final'] }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-xs font-bold {{ $product['sold'] < 0 ? 'text-commander-error' : 'text-slate-500' }}">
                                        {{ $product['sold'] }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <span class="w-2 h-2 rounded-full {{ $product['status_color'] }}"></span>
                                                <span class="text-[10px] font-bold {{ $product['status_text_color'] }} uppercase">{{ $product['status'] }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button onclick="openEditProductModal({{ json_encode($product) }})" class="p-1.5 text-slate-400 hover:text-slate-900 hover:bg-slate-100 rounded-md transition-colors cursor-pointer bg-transparent border-none outline-none" title="Edit Produk">
                                            <span class="material-symbols-outlined text-base">edit</span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-xs font-medium text-slate-400">
                                        Tidak ada data produk untuk cabang ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                    <p class="text-xs text-slate-500">Menampilkan 1 sampai <span id="displayed-count">0</span> dari <span id="total-count">0</span> produk</p>
                    <div class="flex items-center gap-2">
                        <button class="p-1.5 rounded border border-slate-200 bg-white text-slate-400 hover:text-slate-650 disabled:opacity-50" disabled>
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
    </div>

    <!-- Add Product Modal -->
    <div id="add-product-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[999] flex items-center justify-center hidden p-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-500">inventory_2</span>
                    Tambah Produk Baru
                </h3>
                <button onclick="closeAddProductModal()" class="material-symbols-outlined text-slate-400 hover:text-slate-600 cursor-pointer border-none bg-transparent outline-none">close</button>
            </div>
            <form action="{{ route('inventory.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Brand</label>
                        <input type="text" name="brand" id="add-brand" required placeholder="e.g. Smartfren" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Kategori</label>
                        <select name="category" id="add-category" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-semibold text-slate-700 outline-none focus:ring-1 focus:ring-slate-900 cursor-pointer">
                            <option value="Perdana">Perdana</option>
                            <option value="Voucher">Voucher</option>
                            <option value="Aksesoris">Aksesoris</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Nama Produk</label>
                    <input type="text" name="name" id="add-name" required placeholder="e.g. Perdana Smartfren 15GB" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">SKU</label>
                        <input type="text" id="add-sku" disabled placeholder="Otomatis..." class="w-full bg-slate-100 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-semibold text-slate-450 cursor-not-allowed select-none outline-none">
                        <input type="hidden" name="sku" id="hidden-add-sku">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">HPP (Rupiah)</label>
                        <input type="number" name="hpp" required min="0" placeholder="e.g. 25000" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Harga (Rupiah)</label>
                        <input type="number" name="price" required min="0" placeholder="e.g. 35000" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                    </div>
                </div>
                <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                    <button type="button" onclick="closeAddProductModal()" class="px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-lg text-xs font-bold uppercase cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-bold uppercase cursor-pointer">Simpan Produk</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="edit-product-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[999] flex items-center justify-center hidden p-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-500">edit</span>
                    Edit Produk
                </h3>
                <button onclick="closeEditProductModal()" class="material-symbols-outlined text-slate-400 hover:text-slate-600 cursor-pointer border-none bg-transparent outline-none">close</button>
            </div>
            <form id="delete-product-form" action="" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
            <form id="edit-product-form" action="" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <!-- Hidden inputs for branch mode (disabled by default, enabled dynamically) -->
                <input type="hidden" name="brand" id="hidden-brand" disabled>
                <input type="hidden" name="category" id="hidden-category" disabled>
                <input type="hidden" name="name" id="hidden-name" disabled>

                <!-- Cabang Info Header (Only visible in Cabang mode) -->
                <div id="edit-cabang-info-header" class="hidden p-4 bg-slate-50 border border-slate-200 rounded-xl mb-4 space-y-1">
                    <div id="cabang-info-row-1" class="text-xs font-bold text-slate-500 uppercase tracking-wider"></div>
                    <div id="cabang-info-row-2" class="text-sm font-extrabold text-slate-900"></div>
                </div>

                <!-- Gudang Fields Wrapper (Only visible in Gudang mode) -->
                <div id="edit-gudang-fields-grid" class="space-y-4 mb-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Brand</label>
                            <input type="text" name="brand" id="edit-brand" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Kategori</label>
                            <select name="category" id="edit-category" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-semibold text-slate-700 outline-none focus:ring-1 focus:ring-slate-900 cursor-pointer">
                                <option value="Perdana">Perdana</option>
                                <option value="Voucher">Voucher</option>
                                <option value="Aksesoris">Aksesoris</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-2">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Nama Produk</label>
                            <input type="text" name="name" id="edit-name" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">SKU</label>
                            <input type="text" id="edit-sku-display" disabled class="w-full bg-slate-100 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-semibold text-slate-450 cursor-not-allowed select-none outline-none">
                            <input type="hidden" name="sku" id="edit-sku">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">HPP (Rupiah)</label>
                        <input type="text" name="hpp" id="edit-hpp" required oninput="handleRupiahInput(this)" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Harga Jual (Rupiah)</label>
                        <input type="text" name="price" id="edit-price" required oninput="handleRupiahInput(this)" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2" id="edit-stock-grid">
                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-tight mb-1.5">Stok Awal</label>
                        <input type="number" name="initial_stock" id="edit-initial" required min="0" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                    </div>
                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-tight mb-1.5">Stok Masuk</label>
                        <input type="number" name="incoming_stock" id="edit-incoming" required min="0" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                    </div>
                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-tight mb-1.5">Stok Akhir</label>
                        <input type="number" name="final_stock" id="edit-final" required min="0" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                    </div>
                    <input type="hidden" name="sold_stock" id="edit-sold">
                </div>
                <div class="pt-4 border-t border-slate-100 flex justify-between items-center gap-3">
                    <div>
                        <button type="button" id="delete-product-btn-modal" onclick="confirmDeleteProduct()" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold uppercase cursor-pointer hidden">Hapus Produk</button>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="closeEditProductModal()" class="px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-lg text-xs font-bold uppercase cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-bold uppercase cursor-pointer">Simpan Perubahan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Restok Step 1 Modal: Pilih Item -->
    <div id="restock-pilih-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[999] flex items-center justify-center hidden p-4">
        <div class="bg-white w-full max-w-2xl h-[650px] flex flex-col shadow-2xl rounded-xl overflow-hidden border border-slate-200">
            <!-- Modal Header -->
            <div class="p-5 border-b border-slate-200 bg-white flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Pilih Item Restok</h2>
                    <p class="text-xs text-slate-500 mt-1">Pilih produk yang ingin ditambahkan ke daftar restok baru untuk cabang <span class="font-bold text-slate-900">{{ $selectedBranch }}</span></p>
                </div>
                <button onclick="closeRestockModal()" class="p-2 hover:bg-slate-100 rounded-full transition-colors cursor-pointer border-none bg-transparent outline-none">
                    <span class="material-symbols-outlined text-slate-400">close</span>
                </button>
            </div>
            <!-- Search & Filters -->
            <div class="p-5 space-y-4 bg-white border-b border-slate-200">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                    <input id="restock-search" onkeyup="searchRestockProducts()" class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 focus:ring-1 focus:ring-slate-900 focus:border-slate-900 rounded-xl text-xs font-semibold text-slate-700 outline-none" placeholder="Cari nama produk, SKU, atau kategori..."/>
                </div>
                <div class="flex gap-2 overflow-x-auto no-scrollbar pb-1">
                    <button type="button" onclick="filterRestockCategory('Semua', this)" class="restock-category-btn px-4 py-1.5 text-xs font-bold rounded-full bg-slate-900 text-white shadow-sm transition-all cursor-pointer">Semua</button>
                    <button type="button" onclick="filterRestockCategory('Perdana', this)" class="restock-category-btn px-4 py-1.5 text-xs font-semibold rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200 transition-all cursor-pointer">Perdana</button>
                    <button type="button" onclick="filterRestockCategory('Voucher', this)" class="restock-category-btn px-4 py-1.5 text-xs font-semibold rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200 transition-all cursor-pointer">Voucher</button>
                    <button type="button" onclick="filterRestockCategory('Aksesoris', this)" class="restock-category-btn px-4 py-1.5 text-xs font-semibold rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200 transition-all cursor-pointer">Aksesoris</button>
                </div>
            </div>
            <!-- Product List Area -->
            <div class="flex-1 overflow-y-auto divide-y divide-slate-150" id="restock-product-list">
                @foreach($gudangProducts as $gp)
                    @php
                        $branchProd = collect($products)->firstWhere('sku', $gp->sku);
                        $branchQty = $branchProd ? $branchProd['final'] : 0;
                        $branchStatus = $branchProd ? $branchProd['status'] : 'Habis';
                        $branchStatusClass = 'text-commander-success';
                        if ($branchStatus === 'Kritis' || $branchStatus === 'Habis') {
                            $branchStatusClass = 'text-commander-error';
                        } elseif ($branchStatus === 'Tipis') {
                            $branchStatusClass = 'text-amber-600';
                        }
                    @endphp
                    <label class="flex items-center px-5 py-4 hover:bg-slate-50 cursor-pointer transition-colors group restock-product-row" data-name="{{ strtolower($gp->name) }}" data-sku="{{ strtolower($gp->sku) }}" data-category="{{ strtolower($gp->category) }}">
                        <div class="relative flex items-center justify-center mr-4">
                            <input type="checkbox" class="restock-checkbox w-5 h-5 rounded border-slate-350 text-slate-900 focus:ring-0 cursor-pointer" 
                                   data-sku="{{ $gp->sku }}" 
                                   data-name="{{ $gp->name }}" 
                                   data-branch-qty="{{ $branchQty }}" 
                                   data-gudang-qty="{{ $gp->final_stock }}"/>
                        </div>
                        <div class="flex-1 min-w-0 pr-4">
                            <p class="text-xs font-bold text-slate-900 truncate">{{ $gp->name }}</p>
                            <p class="text-[10px] text-slate-500 font-medium flex items-center gap-2 mt-1">
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase bg-slate-100 text-slate-800 border border-slate-200">{{ $gp->brand }}</span>
                                <span>• SKU: {{ $gp->sku }}</span>
                            </p>
                        </div>
                        <div class="text-right">
                            @if($selectedBranch === 'Gudang')
                                <p class="text-xs font-bold text-slate-800">{{ $branchQty }} Pcs</p>
                            @else
                                <p class="text-xs font-bold text-slate-800">{{ $branchQty }} Pcs ({{ strtolower(explode(' ', $selectedBranch)[0]) }})</p>
                                <p class="text-[10px] font-black text-slate-500 uppercase mt-0.5">Gudang: {{ $gp->final_stock }} Pcs</p>
                            @endif
                            <p class="text-[10px] font-extrabold {{ $branchStatusClass }} uppercase mt-0.5">{{ $branchStatus }}</p>
                        </div>
                    </label>
                @endforeach
            </div>
            <!-- Modal Footer -->
            <div class="p-5 border-t border-slate-200 bg-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-slate-900 text-white w-9 h-9 rounded-lg flex items-center justify-center font-bold text-sm shadow-sm" id="selected-count-badge">0</div>
                    <div>
                        <p class="text-xs font-bold text-slate-900">Terpilih: <span id="selected-count-text">0</span> Item</p>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Restok Gudang Utama</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeRestockModal()" class="px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-650 rounded-lg font-bold text-xs uppercase cursor-pointer">Batal</button>
                    <button type="button" onclick="proceedToConfirmation()" class="px-5 py-2 bg-slate-900 text-white text-xs font-bold rounded-lg shadow-md active:scale-[0.98] transition-all uppercase">Lanjutkan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Restok Step 2 Modal: Konfirmasi Restok -->
    <div id="restock-konfirmasi-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[999] flex items-center justify-center hidden p-4">
        <div class="bg-white w-full max-w-lg rounded-xl shadow-2xl overflow-hidden border border-slate-200 animate-in fade-in zoom-in duration-200">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <h3 class="text-sm font-bold text-slate-900">Konfirmasi Restok - {{ $selectedBranch }}</h3>
                <button onclick="backToPilihModal()" class="text-slate-400 hover:text-slate-650 transition-colors cursor-pointer border-none bg-transparent outline-none">
                    <span class="material-symbols-outlined text-base">arrow_back</span>
                </button>
            </div>
            <!-- Modal Body -->
            <form action="{{ route('inventory.restock') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="branch_name" value="{{ $selectedBranch }}"/>
                
                <div class="grid grid-cols-1 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Sumber / Supplier</label>
                        <select name="supplier" class="w-full text-xs font-semibold text-slate-700 border border-slate-200 rounded-lg px-3 py-2 bg-slate-50 outline-none focus:ring-1 focus:ring-slate-900 cursor-pointer">
                            @if($selectedBranch === 'Gudang')
                                <option value="PT Jaya Abadi Telekom" selected>PT Jaya Abadi Telekom (Supplier)</option>
                                <option value="Global Cell Distribution">Global Cell Distribution (Supplier)</option>
                                <option value="Sinar Terang Partner">Sinar Terang Partner (Supplier)</option>
                                @foreach($branches as $b)
                                    <option value="Cabang: {{ $b->name }}">Mutasi: Penarikan dari {{ $b->name }}</option>
                                @endforeach
                            @else
                                <option value="Gudang Pusat" selected>Gudang Pusat</option>
                                @foreach($branches as $b)
                                    @if($b->name !== $selectedBranch)
                                        <option value="Cabang: {{ $b->name }}">Mutasi: Ambil dari {{ $b->name }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Referensi Dokumen</label>
                        <input type="text" name="reference_no" required value="PO-{{ date('Y-m') }}-{{ rand(100, 999) }}" class="w-full text-xs font-medium text-slate-700 border border-slate-200 rounded-lg px-3 py-2 bg-slate-50 outline-none focus:ring-1 focus:ring-slate-900"/>
                    </div>
                </div>
                
                <!-- Summary Table -->
                <div class="flex flex-col gap-3">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Ringkasan Item</label>
                    <div class="border border-slate-150 rounded-lg overflow-hidden max-h-[250px] overflow-y-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 border-b border-slate-150">
                                <tr class="text-[10px] font-bold text-slate-400 uppercase"><th class="px-4 py-2.5">Produk</th><th class="px-4 py-2.5 text-right">{{ $selectedBranch === 'Gudang' ? 'Stok Gudang' : 'Stok Cabang' }}</th><th class="px-4 py-2.5 text-right w-[150px]">Jumlah Restok</th></tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100" id="confirmation-items-tbody">
                                <!-- Populated dynamically by JS -->
                            </tbody>
                        </table>
                    </div>
                    <button type="button" onclick="backToPilihModal()" class="mt-2 flex items-center gap-1.5 text-xs font-bold text-slate-900 hover:opacity-70 transition-opacity w-fit bg-transparent border-none outline-none cursor-pointer">
                        <span class="material-symbols-outlined text-base">add_circle</span>
                        + Tambah Produk
                    </button>
                </div>
                
                <!-- Modal Footer -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" onclick="backToPilihModal()" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors uppercase cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-slate-900 text-white rounded-lg font-bold text-xs hover:bg-slate-800 transition-all flex items-center gap-2 cursor-pointer">
                        <span class="material-symbols-outlined text-sm">check_circle</span>
                        Konfirmasi Restok
                    </button>
                </div>
            </form>
        </div>
    </div>

    </div> <!-- End Main Content Wrapper -->
@endsection

@push('scripts')
<script>
    function openAddProductModal() {
        document.getElementById('add-product-modal').classList.remove('hidden');
    }
    function closeAddProductModal() {
        document.getElementById('add-product-modal').classList.add('hidden');
    }

    function formatRupiah(value) {
        if (value === undefined || value === null || value === '') return '';
        let numberString = value.toString().replace(/[^0-9]/g, '');
        let formatted = numberString.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        return formatted ? 'Rp ' + formatted : '';
    }

    function handleRupiahInput(input) {
        let selectionStart = input.selectionStart;
        let originalLength = input.value.length;
        let rawValue = input.value.replace(/[^0-9]/g, '');
        let formattedValue = formatRupiah(rawValue);
        input.value = formattedValue;
        let newLength = formattedValue.length;
        let cursorPosition = selectionStart + (newLength - originalLength);
        input.setSelectionRange(cursorPosition, cursorPosition);
    }

    function toggleFieldState(id, isReadonly) {
        const el = document.getElementById(id);
        if (isReadonly) {
            el.readOnly = true;
            el.classList.add('bg-slate-100', 'cursor-not-allowed');
            el.classList.remove('bg-slate-50');
        } else {
            el.readOnly = false;
            el.classList.remove('bg-slate-100', 'cursor-not-allowed');
            el.classList.add('bg-slate-50');
        }
    }

    function openEditProductModal(product) {
        document.getElementById('edit-product-form').action = "/inventoris/stok/" + product.id;
        
        // Populate inputs
        document.getElementById('edit-brand').value = product.brand;
        document.getElementById('edit-category').value = product.category;
        document.getElementById('edit-name').value = product.name;
        document.getElementById('edit-sku').value = product.sku;
        document.getElementById('edit-sku-display').value = product.sku;
        document.getElementById('edit-hpp').value = formatRupiah(product.hpp);
        document.getElementById('edit-price').value = formatRupiah(product.price);
        document.getElementById('edit-initial').value = product.initial;
        document.getElementById('edit-incoming').value = product.incoming;
        document.getElementById('edit-final').value = product.final;
        document.getElementById('edit-sold').value = Math.abs(product.sold);

        // Populate hidden inputs
        document.getElementById('hidden-brand').value = product.brand;
        document.getElementById('hidden-category').value = product.category;
        document.getElementById('hidden-name').value = product.name;

        // Populate text labels
        document.getElementById('cabang-info-row-1').textContent = product.category + " > " + product.brand;
        document.getElementById('cabang-info-row-2').textContent = product.sku + " | " + product.name;

        const selectedBranch = "{{ $selectedBranch }}";

        if (selectedBranch === 'Gudang') {
            // Gudang Mode: Show Gudang Fields Grid, hide Cabang info header
            document.getElementById('edit-gudang-fields-grid').classList.remove('hidden');
            document.getElementById('edit-cabang-info-header').classList.add('hidden');
            
            document.getElementById('edit-brand').disabled = false;
            document.getElementById('hidden-brand').disabled = true;

            document.getElementById('edit-category').disabled = false;
            document.getElementById('hidden-category').disabled = true;

            document.getElementById('edit-name').disabled = false;
            document.getElementById('hidden-name').disabled = true;

            toggleFieldState('edit-hpp', false);
            toggleFieldState('edit-price', false);
            toggleFieldState('edit-initial', false);
            toggleFieldState('edit-incoming', false);
            toggleFieldState('edit-final', false);
        } else {
            // Cabang Mode: Hide Gudang Fields Grid, show Cabang info header
            document.getElementById('edit-gudang-fields-grid').classList.add('hidden');
            document.getElementById('edit-cabang-info-header').classList.remove('hidden');

            document.getElementById('edit-brand').disabled = true;
            document.getElementById('hidden-brand').disabled = false;

            document.getElementById('edit-category').disabled = true;
            document.getElementById('hidden-category').disabled = false;

            document.getElementById('edit-name').disabled = true;
            document.getElementById('hidden-name').disabled = false;

            toggleFieldState('edit-hpp', true);
            toggleFieldState('edit-price', false);
            toggleFieldState('edit-initial', false);
            toggleFieldState('edit-incoming', false);
            toggleFieldState('edit-final', false);
        }

        // Show/hide Delete button based on empty stock
        const deleteBtn = document.getElementById('delete-product-btn-modal');
        if (product.final === 0) {
            deleteBtn.classList.remove('hidden');
            document.getElementById('delete-product-form').action = "/inventoris/stok/" + product.id;
        } else {
            deleteBtn.classList.add('hidden');
        }

        document.getElementById('edit-product-modal').classList.remove('hidden');
    }
    function closeEditProductModal() {
        document.getElementById('edit-product-modal').classList.add('hidden');
    }
    function confirmDeleteProduct() {
        if (confirm("Apakah Anda yakin ingin menghapus produk ini dari inventoris?")) {
            document.getElementById('delete-product-form').submit();
        }
    }

    // Restock Modal Flow logic
    function openRestockModal() {
        // Reset checkboxes
        document.querySelectorAll('.restock-checkbox').forEach(cb => cb.checked = false);
        updateSelectedCount();

        // Reset category and search filters
        activeRestockCategory = 'Semua';
        restockSearchQuery = '';
        const searchInput = document.getElementById('restock-search');
        if (searchInput) {
            searchInput.value = '';
        }

        // Reset active tab class for restock categories
        document.querySelectorAll('.restock-category-btn').forEach(btn => {
            if (btn.textContent.trim() === 'Semua') {
                btn.className = 'restock-category-btn px-4 py-1.5 text-xs font-bold rounded-full bg-slate-900 text-white shadow-sm transition-all cursor-pointer';
            } else {
                btn.className = 'restock-category-btn px-4 py-1.5 text-xs font-semibold rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200 transition-all cursor-pointer';
            }
        });

        applyRestockFilter();

        document.getElementById('restock-pilih-modal').classList.remove('hidden');
    }
    function closeRestockModal() {
        document.getElementById('restock-pilih-modal').classList.add('hidden');
    }
    
    // Listen to checkbox changes
    document.querySelectorAll('.restock-checkbox').forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });

    function updateSelectedCount() {
        const checked = document.querySelectorAll('.restock-checkbox:checked').length;
        document.getElementById('selected-count-badge').textContent = checked;
        document.getElementById('selected-count-text').textContent = checked;
    }

    let activeRestockCategory = 'Semua';
    let restockSearchQuery = '';

    function filterRestockCategory(category, button) {
        activeRestockCategory = category;
        
        // Update button visual states
        document.querySelectorAll('.restock-category-btn').forEach(btn => {
            btn.className = 'restock-category-btn px-4 py-1.5 text-xs font-semibold rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200 transition-all cursor-pointer';
        });
        button.className = 'restock-category-btn px-4 py-1.5 text-xs font-bold rounded-full bg-slate-900 text-white shadow-sm transition-all cursor-pointer';

        applyRestockFilter();
    }

    function searchRestockProducts() {
        restockSearchQuery = document.getElementById('restock-search').value.toLowerCase();
        applyRestockFilter();
    }

    function applyRestockFilter() {
        const rows = document.querySelectorAll('.restock-product-row');
        rows.forEach(row => {
            const name = row.getAttribute('data-name');
            const sku = row.getAttribute('data-sku');
            const cat = row.getAttribute('data-category');
            
            const categoryMatch = (activeRestockCategory === 'Semua' || cat === activeRestockCategory.toLowerCase());
            const searchMatch = name.includes(restockSearchQuery) || sku.includes(restockSearchQuery) || cat.includes(restockSearchQuery);

            if (categoryMatch && searchMatch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function proceedToConfirmation() {
        const selectedCheckboxes = document.querySelectorAll('.restock-checkbox:checked');
        if (selectedCheckboxes.length === 0) {
            alert('Pilih setidaknya satu produk untuk di-restok.');
            return;
        }

        const tbody = document.getElementById('confirmation-items-tbody');
        tbody.innerHTML = ''; // clear

        selectedCheckboxes.forEach((cb, idx) => {
            const sku = cb.getAttribute('data-sku');
            const name = cb.getAttribute('data-name');
            const branchQty = cb.getAttribute('data-branch-qty');
            const gudangQty = parseInt(cb.getAttribute('data-gudang-qty')) || 0;

            const selectedBranch = "{{ $selectedBranch }}";
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50';
            tr.innerHTML = `
                <td class="px-4 py-3">
                    <div class="font-bold text-slate-900">${name}</div>
                    <div class="text-[10px] text-slate-400 font-medium">SKU: ${sku}</div>
                    <input type="hidden" name="items[${idx}][sku]" value="${sku}"/>
                </td>
                <td class="px-4 py-3 text-right font-medium text-slate-700">
                    <div>${branchQty}</div>
                    ${selectedBranch !== 'Gudang' ? `<div class="text-[10px] text-slate-400 mt-0.5">Gudang: ${gudangQty} Pcs</div>` : ''}
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-1.5">
                        <button type="button" onclick="adjustRestockQty('${sku}', -5)" class="w-7 h-7 flex items-center justify-center rounded border border-slate-200 text-slate-500 hover:bg-slate-50 cursor-pointer text-xs font-bold">-</button>
                        <input type="number" name="items[${idx}][quantity]" id="qty-input-${sku}" data-sku="${sku}" data-gudang-qty="${gudangQty}" onkeyup="checkGudangWarning(this)" onchange="checkGudangWarning(this)" value="10" min="1" class="w-12 h-7 p-0 text-center text-xs font-bold border border-slate-200 rounded focus:ring-1 focus:ring-slate-900 outline-none"/>
                        <button type="button" onclick="adjustRestockQty('${sku}', 5)" class="w-7 h-7 flex items-center justify-center rounded border border-slate-200 text-slate-500 hover:bg-slate-50 cursor-pointer text-xs font-bold">+</button>
                    </div>
                    ${selectedBranch !== 'Gudang' ? `
                    <div id="warning-${sku}" class="text-[9px] font-bold text-commander-error uppercase tracking-wider mt-1 flex items-center justify-end gap-1 hidden">
                        <span class="material-symbols-outlined text-[11px]" style="font-variation-settings: 'FILL' 1;">warning</span>
                        Melebihi Stok Gudang!
                    </div>
                    ` : ''}
                </td>
            `;
            tbody.appendChild(tr);
        });

        // Hide pilih modal, show konfirmasi modal
        document.getElementById('restock-pilih-modal').classList.add('hidden');
        document.getElementById('restock-konfirmasi-modal').classList.remove('hidden');
    }

    function adjustRestockQty(sku, amount) {
        const input = document.getElementById('qty-input-' + sku);
        let val = parseInt(input.value) || 0;
        val += amount;
        if (val < 1) val = 1;
        input.value = val;
        checkGudangWarning(input);
    }

    function checkGudangWarning(input) {
        if ("{{ $selectedBranch }}" === "Gudang") return;
        const sku = input.getAttribute('data-sku');
        const qty = parseInt(input.value) || 0;
        const gudangQty = parseInt(input.getAttribute('data-gudang-qty')) || 0;
        const warningEl = document.getElementById('warning-' + sku);
        if (qty > gudangQty) {
            warningEl.classList.remove('hidden');
        } else {
            warningEl.classList.add('hidden');
        }
    }

    function backToPilihModal() {
        document.getElementById('restock-konfirmasi-modal').classList.add('hidden');
        document.getElementById('restock-pilih-modal').classList.remove('hidden');
    }

    function toggleBranchView() {
        const selector = document.getElementById('branch-selector');
        const selectedValue = selector.value;
        const dateEl = document.getElementById('date-selector');
        const dateValue = dateEl ? dateEl.value : "{{ $selectedDate }}";
        if (typeof window.triggerGlobalLoading === 'function') window.triggerGlobalLoading();
        window.location.href = "{{ route('inventory.index') }}?branch=" + encodeURIComponent(selectedValue) + "&date=" + encodeURIComponent(dateValue);
    }

    function toggleDateView() {
        const selector = document.getElementById('branch-selector');
        const branchValue = selector ? selector.value : "{{ $selectedBranch }}";
        const dateEl = document.getElementById('date-selector');
        const dateValue = dateEl ? dateEl.value : "";
        if (typeof window.triggerGlobalLoading === 'function') window.triggerGlobalLoading();
        window.location.href = "{{ route('inventory.index') }}?branch=" + encodeURIComponent(branchValue) + "&date=" + encodeURIComponent(dateValue);
    }

    let activeCategory = 'Semua';
    let searchQuery = '';

    function filterCategory(category) {
        activeCategory = category;
        applyFilter();
    }

    function searchProducts() {
        searchQuery = document.getElementById('product-search').value.toLowerCase();
        applyFilter();
    }

    function applyFilter() {
        const rows = document.querySelectorAll('#product-table tbody tr');
        let visibleCount = 0;
        let totalCount = 0;

        rows.forEach(row => {
            const rowCategory = row.getAttribute('data-category');
            const productName = row.querySelector('.product-name');
            if (!productName) return; // empty row handling
            const nameText = productName.textContent.toLowerCase();
            
            const categoryMatch = (activeCategory === 'Semua' || rowCategory === activeCategory);
            const searchMatch = nameText.includes(searchQuery);

            if (categoryMatch && searchMatch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
            totalCount++;
        });

        const displayedCountEl = document.getElementById('displayed-count');
        const totalCountEl = document.getElementById('total-count');
        if (displayedCountEl) displayedCountEl.textContent = visibleCount;
        if (totalCountEl) totalCountEl.textContent = totalCount;
    }

    function initBranchSettings() {
        const selector = document.getElementById('branch-selector');
        const selectedValue = selector.value;
        const addProductBtn = document.getElementById('add-product-btn');
        const restockBtn = document.getElementById('restock-btn');
        
        if (selectedValue === 'Gudang') {
            if (addProductBtn) addProductBtn.classList.remove('hidden');
            if (restockBtn) restockBtn.classList.remove('hidden');
        } else {
            if (addProductBtn) addProductBtn.classList.add('hidden');
            if (restockBtn) restockBtn.classList.remove('hidden');
        }
    }

    function calculateEditSold() {
        const initial = parseInt(document.getElementById('edit-initial').value) || 0;
        const incoming = parseInt(document.getElementById('edit-incoming').value) || 0;
        const final = parseInt(document.getElementById('edit-final').value) || 0;
        const sold = Math.max(0, initial + incoming - final);
        document.getElementById('edit-sold').value = sold;
    }

    function generateSku() {
        const brandInput = document.getElementById('add-brand');
        const categoryInput = document.getElementById('add-category');
        const nameInput = document.getElementById('add-name');
        
        if (!brandInput || !categoryInput || !nameInput) return;
        
        const brand = brandInput.value.trim();
        const category = categoryInput.value;
        const name = nameInput.value.trim();
        
        if (!brand || !name) {
            document.getElementById('add-sku').value = '';
            document.getElementById('hidden-add-sku').value = '';
            return;
        }
        
        // Category prefix
        let catPrefix = 'PROD';
        const catLower = category.toLowerCase();
        if (catLower.includes('perd')) {
            catPrefix = 'PDN';
        } else if (catLower.includes('vouch')) {
            catPrefix = 'VCH';
        } else if (catLower.includes('akses') || catLower.includes('access')) {
            catPrefix = 'ACC';
        } else {
            catPrefix = category.replace(/[^a-zA-Z0-9]/g, '').substring(0, 3).toUpperCase();
        }
        
        // Brand prefix
        const brandClean = brand.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        let brandPrefix = brandClean.substring(0, 4);
        if (brandClean === 'TELKOMSEL') {
            brandPrefix = 'TSEL';
        } else if (brandClean === 'SMARTFREN') {
            brandPrefix = 'SF';
        } else if (brandClean === 'INDOSAT') {
            brandPrefix = 'ISAT';
        }
        
        // Name part
        let nameClean = name.toUpperCase().replace(/[^A-Z0-9]/g, '');
        // Remove brand and category words
        nameClean = nameClean.replace(brand.toUpperCase(), '').replace(category.toUpperCase(), '');
        nameClean = nameClean.replace(/[^A-Z0-9]/g, '');
        
        let namePart = nameClean.substring(0, 6);
        if (!namePart) {
            namePart = Math.random().toString(36).substring(2, 6).toUpperCase();
        }
        
        const sku = `${catPrefix}-${brandPrefix}-${namePart}`;
        document.getElementById('add-sku').value = sku;
        document.getElementById('hidden-add-sku').value = sku;
    }

    // Initialize counts on load
    document.addEventListener('DOMContentLoaded', () => {
        applyFilter();
        initBranchSettings();

        // Listeners for edit stock calculation
        document.getElementById('edit-initial').addEventListener('input', calculateEditSold);
        document.getElementById('edit-incoming').addEventListener('input', calculateEditSold);
        document.getElementById('edit-final').addEventListener('input', calculateEditSold);

        // Listeners for auto-generating SKU
        const brandInput = document.getElementById('add-brand');
        const categoryInput = document.getElementById('add-category');
        const nameInput = document.getElementById('add-name');
        
        if (brandInput && categoryInput && nameInput) {
            brandInput.addEventListener('input', generateSku);
            categoryInput.addEventListener('change', generateSku);
            nameInput.addEventListener('input', generateSku);
        }
    });

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
