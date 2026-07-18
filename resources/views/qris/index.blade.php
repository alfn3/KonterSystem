@extends('layouts.app')

@section('title', 'Konfirmasi QRIS')
@section('subtitle', 'Pantau dan verifikasi pembayaran QRIS dari aplikasi mobile secara langsung.')

@section('content')

    <!-- Stats Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-5 mb-6 mt-6">
        <!-- Card 1: Total QRIS Transactions -->
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex flex-col justify-between hover:border-slate-300 hover:shadow-md transition-all group gap-3">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" style="background:#f1f5f9;">
                    <span class="material-symbols-outlined" style="color:#475569;font-size:20px;font-variation-settings:'FILL' 1;">payments</span>
                </div>
            </div>
            <div>
                <p style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Total QRIS</p>
                <p style="font-size:26px;font-weight:800;color:#0f172a;letter-spacing:-0.5px;line-height:1.1;">{{ $stats['total_count'] }}</p>
                <p style="font-size:11px;color:#94a3b8;margin-top:4px;">Semua Transaksi</p>
            </div>
        </div>

        <!-- Card 2: Pending QRIS -->
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex flex-col justify-between hover:border-amber-300 hover:shadow-md transition-all group gap-3">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" style="background:#fffbeb;">
                    <span class="material-symbols-outlined" style="color:#d97706;font-size:20px;font-variation-settings:'FILL' 1;">pending</span>
                </div>
            </div>
            <div>
                <p style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Diproses</p>
                <p style="font-size:26px;font-weight:800;color:#d97706;letter-spacing:-0.5px;line-height:1.1;">{{ $stats['pending_count'] }}</p>
                <p style="font-size:11px;color:#94a3b8;margin-top:4px;">Menunggu Konfirmasi</p>
            </div>
        </div>

        <!-- Card 3: Success QRIS -->
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex flex-col justify-between hover:border-emerald-300 hover:shadow-md transition-all group gap-3">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" style="background:#ecfdf5;">
                    <span class="material-symbols-outlined" style="color:#059669;font-size:20px;font-variation-settings:'FILL' 1;">check_circle</span>
                </div>
            </div>
            <div>
                <p style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Sukses</p>
                <p style="font-size:26px;font-weight:800;color:#059669;letter-spacing:-0.5px;line-height:1.1;">{{ $stats['success_count'] }}</p>
                <p style="font-size:11px;color:#94a3b8;margin-top:4px;">Berhasil Terbayar</p>
            </div>
        </div>

        <!-- Card 4: Failed QRIS -->
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex flex-col justify-between hover:border-rose-300 hover:shadow-md transition-all group gap-3">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" style="background:#fff1f2;">
                    <span class="material-symbols-outlined" style="color:#e11d48;font-size:20px;font-variation-settings:'FILL' 1;">cancel</span>
                </div>
            </div>
            <div>
                <p style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Gagal</p>
                <p style="font-size:26px;font-weight:800;color:#e11d48;letter-spacing:-0.5px;line-height:1.1;">{{ $stats['failed_count'] }}</p>
                <p style="font-size:11px;color:#94a3b8;margin-top:4px;">Dibatalkan/Gagal</p>
            </div>
        </div>

        <!-- Card 5: Revenue MTD via QRIS -->
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex flex-col justify-between hover:border-indigo-300 hover:shadow-md transition-all group gap-3">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" style="background:#eef2ff;">
                    <span class="material-symbols-outlined" style="color:#4f46e5;font-size:20px;font-variation-settings:'FILL' 1;">account_balance</span>
                </div>
            </div>
            <div>
                <p style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Total Pendapatan</p>
                <p style="font-size:26px;font-weight:800;color:#4f46e5;letter-spacing:-0.5px;line-height:1.1;">Rp {{ number_format($stats['total_amount'], 0, ',', '.') }}</p>
                <p style="font-size:11px;color:#94a3b8;margin-top:4px;">Hanya Transaksi Sukses</p>
            </div>
        </div>
    </div>

    <!-- Filters & Search Bar (Premium Banner) -->
    <div class="greeting-banner mb-6" style="padding: 20px;">
        <form action="{{ route('qris.index') }}" method="GET" class="flex flex-wrap items-end gap-4 m-0 p-0 w-full" style="position:relative;z-index:1;">
            <!-- Search ID/Pelanggan -->
            <div class="flex-1 min-w-[200px]">
                <label class="block text-[10px] font-extrabold text-white/80 uppercase tracking-widest mb-1.5">Cari Transaksi</label>
                <div class="relative shadow-sm rounded-lg">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-indigo-600 text-sm">search</span>
                    <input type="text" name="search" value="{{ request('search') }}" class="w-full text-xs border border-white/20 rounded-lg pl-9 pr-4 py-2 focus:ring-1 focus:ring-white outline-none bg-white/95 text-slate-800 font-bold placeholder-slate-400" placeholder="ID Transaksi, No. HP...">
                </div>
            </div>

            <!-- Filter Cabang -->
            <div class="w-full sm:w-auto min-w-[150px]">
                <label class="block text-[10px] font-extrabold text-white/80 uppercase tracking-widest mb-1.5">Cabang (Branch)</label>
                <div class="relative shadow-sm rounded-lg">
                    <select name="branch_id" class="appearance-none w-full text-xs border border-white/20 rounded-lg px-3 py-2 pr-8 focus:ring-1 focus:ring-white outline-none bg-white/95 text-slate-800 font-bold cursor-pointer h-[34px]">
                        <option value="">Semua Cabang</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined absolute right-2 top-1/2 -translate-y-1/2 text-indigo-600 pointer-events-none text-sm">expand_more</span>
                </div>
            </div>

            <!-- Filter Status -->
            <div class="w-full sm:w-auto min-w-[120px]">
                <label class="block text-[10px] font-extrabold text-white/80 uppercase tracking-widest mb-1.5">Status</label>
                <div class="relative shadow-sm rounded-lg">
                    <select name="status" class="appearance-none w-full text-xs border border-white/20 rounded-lg px-3 py-2 pr-8 focus:ring-1 focus:ring-white outline-none bg-white/95 text-slate-800 font-bold cursor-pointer h-[34px]">
                        <option value="">Semua Status</option>
                        <option value="Diproses" {{ request('status') == 'Diproses' ? 'selected' : '' }}>Diproses (Pending)</option>
                        <option value="awaiting_confirmation" {{ request('status') == 'awaiting_confirmation' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                        <option value="Sukses" {{ request('status') == 'Sukses' ? 'selected' : '' }}>Sukses</option>
                        <option value="Gagal" {{ request('status') == 'Gagal' ? 'selected' : '' }}>Gagal</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-2 top-1/2 -translate-y-1/2 text-indigo-600 pointer-events-none text-sm">expand_more</span>
                </div>
            </div>

            <!-- Filter Date Range -->
            <div class="w-full sm:w-auto">
                <label class="block text-[10px] font-extrabold text-white/80 uppercase tracking-widest mb-1.5">Dari Tanggal</label>
                <div class="relative shadow-sm rounded-lg">
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full text-xs border border-white/20 rounded-lg px-3 py-1.5 focus:ring-1 focus:ring-white outline-none bg-white/95 text-slate-800 font-bold cursor-pointer h-[34px]">
                </div>
            </div>

            <div class="w-full sm:w-auto">
                <label class="block text-[10px] font-extrabold text-white/80 uppercase tracking-widest mb-1.5">Sampai Tanggal</label>
                <div class="relative shadow-sm rounded-lg">
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full text-xs border border-white/20 rounded-lg px-3 py-1.5 focus:ring-1 focus:ring-white outline-none bg-white/95 text-slate-800 font-bold cursor-pointer h-[34px]">
                </div>
            </div>

            <!-- Control Buttons -->
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-white/20 border border-white/30 text-white text-xs font-bold rounded-lg hover:bg-white/30 transition-all cursor-pointer h-[34px] backdrop-blur-sm shadow-sm">Filter</button>
                @if(request()->anyFilled(['search', 'branch_id', 'status', 'start_date', 'end_date']))
                    <a href="{{ route('qris.index') }}" class="px-3 py-2 bg-red-500/80 border border-red-400/50 hover:bg-red-500 text-white text-xs font-bold rounded-lg flex items-center justify-center transition-all cursor-pointer h-[34px] backdrop-blur-sm shadow-sm">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">ID Transaksi</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Cabang</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Tanggal & Waktu</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Jumlah Pembayaran</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-center">Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $tx)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 font-mono font-bold text-slate-900 text-xs">
                                {{ $tx->id }}
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-slate-700">
                                {{ $tx->branch ? $tx->branch->name : '-' }}
                            </td>
                            <td class="px-6 py-4 text-slate-600 text-xs font-medium">
                                {{ $tx->created_at ? $tx->created_at->translatedFormat('d M Y H:i:s') : '-' }}
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900 text-xs">
                                Rp {{ number_format($tx->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($tx->status === 'Sukses')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Sukses
                                    </span>
                                @elseif($tx->status === 'awaiting_confirmation')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-orange-50 text-orange-700 border border-orange-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse"></span> Menunggu Konfirmasi
                                    </span>
                                @elseif($tx->status === 'Diproses')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Diproses
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Gagal
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Detail Button -->
                                    <button onclick="openDetailModal({{ json_encode($tx) }}, {{ json_encode($tx->items) }})" class="px-2.5 py-1 bg-slate-100 text-slate-700 hover:bg-slate-200 hover:text-slate-900 rounded text-[11px] font-bold flex items-center gap-1 transition-all cursor-pointer">
                                        <span class="material-symbols-outlined text-[14px]">info</span> Detail
                                    </button>

                                    <!-- Confirm Button -->
                                    @if($tx->status !== 'Sukses')
                                        <form action="{{ route('qris.confirm', $tx->id) }}" method="POST" class="inline m-0 p-0" onsubmit="return confirm('Apakah Anda yakin ingin mengonfirmasi pembayaran QRIS {{ $tx->id }} senilai Rp {{ number_format($tx->total_amount, 0, ',', '.') }} secara manual?')">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[11px] font-bold flex items-center gap-1 transition-all cursor-pointer">
                                                <span class="material-symbols-outlined text-[14px]">check</span> Konfirmasi
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-xs font-medium text-slate-400">
                                Tidak ada data transaksi QRIS yang cocok.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex items-center justify-between">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    <!-- Transaction Detail Modal -->
    <div id="detail-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[999] flex items-center justify-center hidden p-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-500">receipt_long</span>
                    Rincian Transaksi
                </h3>
                <button onclick="closeDetailModal()" class="material-symbols-outlined text-slate-400 hover:text-slate-600 cursor-pointer border-none bg-transparent outline-none">close</button>
            </div>
            
            <div class="p-6 space-y-6">
                <!-- Header Info Grid -->
                <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-100 text-xs">
                    <div>
                        <p class="font-bold text-slate-400 uppercase tracking-wider text-[9px] mb-1">ID Transaksi</p>
                        <p class="font-mono font-bold text-slate-900" id="detail_tx_id">-</p>
                    </div>
                    <div>
                        <p class="font-bold text-slate-400 uppercase tracking-wider text-[9px] mb-1">Cabang</p>
                        <p class="font-semibold text-slate-800" id="detail_tx_branch">-</p>
                    </div>
                    <div>
                        <p class="font-bold text-slate-400 uppercase tracking-wider text-[9px] mb-1">Waktu Transaksi</p>
                        <p class="font-medium text-slate-700" id="detail_tx_time">-</p>
                    </div>
                    <div>
                        <p class="font-bold text-slate-400 uppercase tracking-wider text-[9px] mb-1">Status Pembayaran</p>
                        <div id="detail_tx_status_container"></div>
                    </div>
                    <div>
                        <p class="font-bold text-slate-400 uppercase tracking-wider text-[9px] mb-1">Pelanggan</p>
                        <p class="font-medium text-slate-700" id="detail_tx_customer">-</p>
                    </div>
                    <div>
                        <p class="font-bold text-slate-400 uppercase tracking-wider text-[9px] mb-1">Kasir / Operator</p>
                        <p class="font-medium text-slate-700" id="detail_tx_operator">-</p>
                    </div>
                </div>

                <!-- QRIS Preview if Pending -->
                <div id="qris_preview_container" class="hidden flex flex-col items-center justify-center p-4 border border-slate-150 rounded-xl bg-slate-50">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">QR Code QRIS</p>
                    <img id="detail_qris_image" src="" alt="QRIS Code" class="w-40 h-40 border border-slate-200 bg-white p-2 rounded-lg">
                    <p class="text-[9px] text-slate-400 mt-1 font-mono text-center max-w-xs break-all" id="detail_qris_string"></p>
                </div>

                <!-- Proof Image Preview if Awaiting Confirmation -->
                <div id="proof_image_container" class="hidden flex flex-col items-center justify-center p-4 border border-slate-150 rounded-xl bg-slate-50">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Bukti Pembayaran / Transfer</p>
                    <a id="detail_proof_link" href="" target="_blank" class="block">
                        <img id="detail_proof_image" src="" alt="Bukti Pembayaran" class="max-w-xs max-h-60 border border-slate-200 bg-white p-1 rounded-lg hover:opacity-90 transition-opacity">
                    </a>
                </div>

                <!-- Items Purchased -->
                <div>
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-3">Item Pembelian</h4>
                    <div class="border border-slate-200 rounded-lg overflow-hidden">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th class="px-4 py-2.5 font-bold text-slate-400 uppercase tracking-wider">Produk</th>
                                    <th class="px-4 py-2.5 font-bold text-slate-400 uppercase tracking-wider text-center">Jumlah</th>
                                    <th class="px-4 py-2.5 font-bold text-slate-400 uppercase tracking-wider text-right">Harga</th>
                                    <th class="px-4 py-2.5 font-bold text-slate-400 uppercase tracking-wider text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="detail_items_body" class="divide-y divide-slate-100">
                                <!-- Dynamically populated -->
                            </tbody>
                            <tfoot>
                                <tr class="bg-slate-50 font-bold border-t border-slate-200">
                                    <td colspan="3" class="px-4 py-3 text-slate-700 uppercase tracking-wider text-right">Total Transaksi</td>
                                    <td class="px-4 py-3 text-slate-900 text-right text-sm" id="detail_tx_total">Rp 0</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Footer / Action Buttons inside Modal -->
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="closeDetailModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition-all cursor-pointer">Tutup</button>
                    <div id="modal_action_container"></div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    function openDetailModal(tx, items) {
        // Populate header details
        document.getElementById('detail_tx_id').innerText = tx.id;
        document.getElementById('detail_tx_branch').innerText = tx.branch ? tx.branch.name : '-';
        
        // Format Date
        const date = new Date(tx.created_at);
        const options = { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
        document.getElementById('detail_tx_time').innerText = date.toLocaleDateString('id-ID', options);
        
        document.getElementById('detail_tx_customer').innerText = tx.customer_phone || tx.customer_id || '-';
        document.getElementById('detail_tx_operator').innerText = tx.operator || '-';
        document.getElementById('detail_tx_total').innerText = 'Rp ' + Number(tx.total_amount).toLocaleString('id-ID');
        
        // Populate status badge
        const statusContainer = document.getElementById('detail_tx_status_container');
        let statusBadge = '';
        if (tx.status === 'Sukses') {
            statusBadge = `
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                    Sukses
                </span>`;
        } else if (tx.status === 'awaiting_confirmation') {
            statusBadge = `
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-orange-100 text-orange-850 border border-orange-200">
                    Menunggu Konfirmasi
                </span>`;
        } else if (tx.status === 'Diproses') {
            statusBadge = `
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                    Diproses
                </span>`;
        } else {
            statusBadge = `
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800 border border-red-200">
                    Gagal
                </span>`;
        }
        statusContainer.innerHTML = statusBadge;

        // Populate items table
        const itemsBody = document.getElementById('detail_items_body');
        itemsBody.innerHTML = '';
        items.forEach(item => {
            const subtotal = Number(item.price) * item.quantity;
            let destText = '';
            if (item.destination_number) {
                destText = `<div class="text-[10px] text-slate-400 mt-0.5 font-medium">Tujuan: ${item.destination_number}</div>`;
            }
            itemsBody.innerHTML += `
                <tr>
                    <td class="px-4 py-2.5 font-semibold text-slate-800">
                        ${item.product_name}
                        <div class="text-[10px] text-slate-400 font-mono mt-0.5">${item.product_sku}</div>
                        ${destText}
                    </td>
                    <td class="px-4 py-2.5 text-center text-slate-700 font-semibold">${item.quantity}</td>
                    <td class="px-4 py-2.5 text-right text-slate-700 font-medium">Rp ${Number(item.price).toLocaleString('id-ID')}</td>
                    <td class="px-4 py-2.5 text-right text-slate-900 font-bold">Rp ${subtotal.toLocaleString('id-ID')}</td>
                </tr>
            `;
        });

        // Set up the local QR code preview (using real QRIS payload if available)
        const qrisPreview = document.getElementById('qris_preview_container');
        const qrisImage = document.getElementById('detail_qris_image');
        const qrisString = document.getElementById('detail_qris_string');
        
        const qrData = tx.qris || tx.id;
        if (tx.status === 'Diproses' || tx.qris) {
            qrisPreview.classList.remove('hidden');
            // We load QR Code dynamically from our local QR code API!
            qrisImage.src = `/api/qrcode?data=${encodeURIComponent(qrData)}`;
            qrisString.innerText = tx.qris ? qrData : `Data QR: ${qrData}`;
        } else {
            qrisPreview.classList.add('hidden');
            qrisImage.src = '';
            qrisString.innerText = '';
        }

        // Set up proof image if available
        const proofPreview = document.getElementById('proof_image_container');
        const proofImage = document.getElementById('detail_proof_image');
        const proofLink = document.getElementById('detail_proof_link');
        
        if (tx.proof_image) {
            proofPreview.classList.remove('hidden');
            proofImage.src = '/' + tx.proof_image;
            proofLink.href = '/' + tx.proof_image;
        } else {
            proofPreview.classList.add('hidden');
            proofImage.src = '';
            proofLink.href = '';
        }

        // Add action button inside modal if pending
        const actionContainer = document.getElementById('modal_action_container');
        actionContainer.innerHTML = '';
        if (tx.status !== 'Sukses') {
            actionContainer.innerHTML = `
                <form action="/keuangan/qris/${tx.id}/confirm" method="POST" class="inline m-0 p-0" onsubmit="return confirm('Konfirmasi pembayaran secara manual?')">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="PUT">
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold flex items-center gap-1.5 transition-all cursor-pointer">
                        <span class="material-symbols-outlined text-[16px]">check</span> Konfirmasi Manual
                    </button>
                </form>
            `;
        }

        // Open modal
        document.getElementById('detail-modal').classList.remove('hidden');
    }

    function closeDetailModal() {
        document.getElementById('detail-modal').classList.add('hidden');
    }
</script>
@endpush
