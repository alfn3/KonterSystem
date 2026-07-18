@extends('layouts.app')

@section('title', 'Manajemen Pelanggan')
@section('subtitle', 'Pantau daftar pelanggan terdaftar, riwayat aktivitas transaksi, dan loyalitas berdasarkan cabang.')

@section('content')

    <!-- Controls, Filter & Search (Premium Banner) -->
    <div class="greeting-banner mb-6 flex-col lg:flex-row items-end justify-between gap-4" style="padding: 20px;">
        <!-- Left Group: Search & Filter -->
        <form action="{{ route('customer.index') }}" method="GET" class="flex flex-wrap items-end gap-3 flex-1 min-w-[280px] m-0 p-0" style="position:relative;z-index:1;">
            <!-- Search input -->
            <div class="flex flex-col gap-1.5 min-w-[200px] flex-1 max-w-xs">
                <label class="text-[10px] font-extrabold text-white/80 uppercase tracking-widest">Cari Pelanggan</label>
                <div class="relative w-full shadow-sm rounded-lg">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-indigo-600 text-sm">search</span>
                    <input type="text" name="search" value="{{ $search }}" class="w-full text-xs border border-white/20 rounded-lg pl-9 pr-4 py-2 focus:ring-1 focus:ring-white outline-none transition-all bg-white/95 text-slate-800 font-bold placeholder-slate-400" placeholder="Cari nama atau telepon...">
                </div>
            </div>

            <!-- Branch Filter -->
            <div class="flex flex-col gap-1.5 min-w-[180px] flex-1 max-w-xs">
                <label class="text-[10px] font-extrabold text-white/80 uppercase tracking-widest">Cabang Pendaftaran</label>
                <div class="relative shadow-sm rounded-lg">
                    <select name="branch_id" onchange="this.form.submit()" class="appearance-none bg-white/95 border border-white/20 rounded-lg px-3.5 py-2 pr-8 text-xs font-bold text-slate-800 outline-none focus:ring-1 focus:ring-white w-full cursor-pointer h-[34px]">
                        <option value="">Semua Cabang</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ $selectedBranchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined absolute right-2 top-1/2 -translate-y-1/2 text-indigo-600 pointer-events-none text-base">expand_more</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-white/20 border border-white/30 text-white rounded-lg text-xs font-bold hover:bg-white/30 transition-all cursor-pointer h-[34px] backdrop-blur-sm shadow-sm">Cari</button>
                @if($search || $selectedBranchId)
                    <a href="{{ route('customer.index') }}" class="px-3 py-2 bg-red-500/80 hover:bg-red-500 text-white border border-red-400/50 text-xs font-bold rounded-lg flex items-center justify-center transition-all cursor-pointer h-[34px] backdrop-blur-sm shadow-sm">Reset</a>
                @endif
            </div>
        </form>

        <!-- Right Group: Action -->
        <div class="flex items-center" style="position:relative;z-index:1;">
            <button onclick="openAddCustomerModal()" class="px-4 py-2 bg-white text-indigo-600 border border-white/20 rounded-lg flex items-center gap-2 text-[11px] font-extrabold uppercase tracking-wider hover:bg-slate-50 transition-all cursor-pointer h-[34px] shadow-sm shrink-0">
                <span class="material-symbols-outlined text-sm">person_add</span> Tambah Pelanggan Baru
            </button>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Pelanggan</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">No Telepon</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Layanan Utama</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Cabang Pendaftaran</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-center">Total Transaksi</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Total Pembelian</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-900">
                                {{ $customer->name }}
                            </td>
                            <td class="px-6 py-4 font-mono text-slate-600 text-xs font-medium">
                                {{ $customer->phone }}
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-slate-700">
                                @if($customer->service_type == 'PULSA')
                                    <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded-md text-[10px] font-bold border border-blue-100">PULSA & DATA</span>
                                @elseif($customer->service_type == 'TOKEN_PLN')
                                    <span class="px-2 py-1 bg-amber-50 text-amber-700 rounded-md text-[10px] font-bold border border-amber-100">TOKEN LISTRIK PLN</span>
                                @elseif($customer->service_type == 'TAGIHAN_PLN')
                                    <span class="px-2 py-1 bg-amber-100 text-amber-900 rounded-md text-[10px] font-bold border border-amber-200">TAGIHAN PLN</span>
                                @elseif($customer->service_type == 'TAGIHAN')
                                    <span class="px-2 py-1 bg-amber-50 text-amber-700 rounded-md text-[10px] font-bold border border-amber-100">TOKEN LISTRIK / TAGIHAN</span>
                                @elseif($customer->service_type == 'E_WALLET')
                                    <span class="px-2 py-1 bg-emerald-50 text-emerald-700 rounded-md text-[10px] font-bold border border-emerald-100">E-WALLET DANA</span>
                                @elseif($customer->service_type == 'GAME')
                                    <span class="px-2 py-1 bg-purple-50 text-purple-700 rounded-md text-[10px] font-bold border border-purple-100">VOUCHER GAME</span>
                                @elseif($customer->service_type == 'TRANSFER')
                                    <span class="px-2 py-1 bg-indigo-50 text-indigo-700 rounded-md text-[10px] font-bold border border-indigo-100">TRANSFER BANK</span>
                                @else
                                    <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded-md text-[10px] font-bold border border-slate-200">UMUM</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-slate-700">
                                @if($customer->branch)
                                    <span class="flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-slate-400 text-sm">storefront</span>
                                        {{ $customer->branch->name }}
                                    </span>
                                @else
                                    <span class="flex items-center gap-1.5 text-slate-400 font-normal">
                                        <span class="material-symbols-outlined text-slate-300 text-sm">language</span>
                                        Umum / Mandiri
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-slate-700 text-xs">
                                {{ $customer->total_transactions }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-slate-900 text-xs">
                                Rp {{ number_format($customer->total_spent, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="openEditCustomerModal({{ json_encode($customer) }})" class="p-1.5 text-slate-400 hover:text-slate-900 hover:bg-slate-100 rounded-md transition-colors cursor-pointer bg-transparent border-none outline-none" title="Edit Pelanggan">
                                        <span class="material-symbols-outlined text-base">edit</span>
                                    </button>
                                    <form action="{{ route('customer.destroy', $customer->id) }}" method="POST" class="inline m-0 p-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelanggan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-450 hover:text-rose-700 hover:bg-rose-50 rounded-md transition-colors cursor-pointer bg-transparent border-none outline-none" title="Hapus Pelanggan">
                                            <span class="material-symbols-outlined text-base">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-xs font-medium text-slate-400">
                                Tidak ada data pelanggan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Customer Modal -->
    <div id="add-customer-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[999] flex items-center justify-center hidden p-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-md overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-500">person_add</span>
                    Tambah Pelanggan Baru
                </h3>
                <button onclick="closeAddCustomerModal()" class="material-symbols-outlined text-slate-400 hover:text-slate-600 cursor-pointer border-none bg-transparent outline-none">close</button>
            </div>
            <form action="{{ route('customer.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Nama Pelanggan</label>
                    <input type="text" name="name" required placeholder="e.g. Ahmad Yani" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">No Telepon</label>
                    <input type="text" name="phone" required placeholder="e.g. 0812-3456-7890" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Layanan Utama</label>
                    <select name="service_type" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-semibold text-slate-700 outline-none focus:ring-1 focus:ring-slate-900 cursor-pointer">
                        <option value="PULSA">PULSA & DATA</option>
                        <option value="TOKEN_PLN">TOKEN LISTRIK PLN</option>
                        <option value="TAGIHAN_PLN">TAGIHAN PLN (PASCABAYAR)</option>
                        <option value="TAGIHAN">TOKEN LISTRIK / TAGIHAN</option>
                        <option value="E_WALLET">E-WALLET DANA</option>
                        <option value="GAME">VOUCHER GAME</option>
                        <option value="TRANSFER">TRANSFER BANK</option>
                        <option value="UMUM">UMUM / FISIK</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Cabang Pendaftaran</label>
                    <select name="branch_id" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-semibold text-slate-700 outline-none focus:ring-1 focus:ring-slate-900 cursor-pointer">
                        <option value="">Umum / Mandiri</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                    <button type="button" onclick="closeAddCustomerModal()" class="px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-lg text-xs font-bold uppercase cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-bold uppercase cursor-pointer">Simpan Pelanggan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Customer Modal -->
    <div id="edit-customer-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[999] flex items-center justify-center hidden p-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-md overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-500">edit</span>
                    Edit Pelanggan
                </h3>
                <button onclick="closeEditCustomerModal()" class="material-symbols-outlined text-slate-400 hover:text-slate-600 cursor-pointer border-none bg-transparent outline-none">close</button>
            </div>
            <form id="edit-customer-form" action="" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Nama Pelanggan</label>
                    <input type="text" name="name" id="edit-name" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">No Telepon</label>
                    <input type="text" name="phone" id="edit-phone" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Layanan Utama</label>
                    <select name="service_type" id="edit-service-type" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-semibold text-slate-700 outline-none focus:ring-1 focus:ring-slate-900 cursor-pointer">
                        <option value="PULSA">PULSA & DATA</option>
                        <option value="TOKEN_PLN">TOKEN LISTRIK PLN</option>
                        <option value="TAGIHAN_PLN">TAGIHAN PLN (PASCABAYAR)</option>
                        <option value="TAGIHAN">TOKEN LISTRIK / TAGIHAN</option>
                        <option value="E_WALLET">E-WALLET DANA</option>
                        <option value="GAME">VOUCHER GAME</option>
                        <option value="TRANSFER">TRANSFER BANK</option>
                        <option value="UMUM">UMUM / FISIK</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Cabang Pendaftaran</label>
                    <select name="branch_id" id="edit-branch-id" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-semibold text-slate-700 outline-none focus:ring-1 focus:ring-slate-900 cursor-pointer">
                        <option value="">Umum / Mandiri</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                    <button type="button" onclick="closeEditCustomerModal()" class="px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-lg text-xs font-bold uppercase cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-bold uppercase cursor-pointer">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    function openAddCustomerModal() {
        document.getElementById('add-customer-modal').classList.remove('hidden');
    }
    function closeAddCustomerModal() {
        document.getElementById('add-customer-modal').classList.add('hidden');
    }

    function openEditCustomerModal(customer) {
        document.getElementById('edit-customer-form').action = "/operasional/pelanggan/" + customer.id;
        
        document.getElementById('edit-name').value = customer.name;
        document.getElementById('edit-phone').value = customer.phone;
        document.getElementById('edit-branch-id').value = customer.branch_id || '';
        document.getElementById('edit-service-type').value = customer.service_type || 'UMUM';

        document.getElementById('edit-customer-modal').classList.remove('hidden');
    }
    function closeEditCustomerModal() {
        document.getElementById('edit-customer-modal').classList.add('hidden');
    }
</script>
@endpush
