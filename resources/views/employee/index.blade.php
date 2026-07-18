@extends('layouts.app')

@section('title', 'Manajemen Karyawan')
@section('subtitle', 'Kelola daftar karyawan, peranan (role), kontak, penugasan cabang, dan status aktif.')

@section('content')

    <!-- Controls & Add Button (Styled as Premium Banner) -->
    <div class="greeting-banner mb-6 flex-col sm:flex-row items-center justify-between gap-4" style="padding: 16px 20px;">
        <div class="flex flex-wrap items-center justify-between gap-4 w-full">
            <!-- Left Group: Search -->
            <form action="{{ route('employee.index') }}" method="GET" class="flex items-center gap-3 flex-1 min-w-[280px] m-0 p-0" style="position:relative;z-index:1;">
                <div class="relative w-full max-w-[280px] shadow-sm rounded-lg">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-indigo-600 text-sm">search</span>
                    <input type="text" name="search" value="{{ $search }}" class="w-full text-xs border border-white/20 rounded-lg pl-9 pr-4 py-2 focus:ring-1 focus:ring-white outline-none transition-all bg-white/95 text-slate-800 font-bold placeholder-slate-400" placeholder="Cari nama, role, status...">
                </div>
                <button type="submit" class="px-4 py-2 bg-white/20 border border-white/30 text-white rounded-lg text-xs font-bold hover:bg-white/30 transition-all cursor-pointer backdrop-blur-sm shadow-sm">Cari</button>
                @if($search)
                    <a href="{{ route('employee.index') }}" class="px-3 py-2 bg-red-500/80 hover:bg-red-500 text-white border border-red-400/50 text-xs font-bold rounded-lg flex items-center justify-center transition-all cursor-pointer backdrop-blur-sm shadow-sm">Reset</a>
                @endif
            </form>

            <!-- Right Group: Action -->
            <button onclick="openAddEmployeeModal()" class="px-4 py-2 bg-white text-indigo-600 border border-white/20 rounded-lg flex items-center gap-2 text-[11px] font-extrabold uppercase tracking-wider hover:bg-slate-50 transition-all cursor-pointer shadow-sm shrink-0" style="position:relative;z-index:1;">
                <span class="material-symbols-outlined text-sm">person_add</span> Tambah Karyawan Baru
            </button>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Karyawan</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Peran (Role)</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">No Telepon</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Alamat Asal</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Mulai Kerja</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-center">Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($employees as $employee)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-900">
                                {{ $employee->name }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $employee->role === 'Admin' ? 'bg-blue-100 text-blue-700 border border-blue-200' : ($employee->role === 'Supervisor' ? 'bg-purple-100 text-purple-700 border border-purple-200' : 'bg-slate-100 text-slate-700 border border-slate-200') }}">
                                    {{ $employee->role }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-650 text-xs font-medium">
                                {{ $employee->email ?? '-' }}
                            </td>
                            <td class="px-6 py-4 font-mono text-slate-600 text-xs font-medium">
                                {{ $employee->phone ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-slate-650 text-xs font-medium max-w-[150px] truncate" title="{{ $employee->home_address }}">
                                {{ $employee->home_address ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-slate-650 text-xs font-medium">
                                {{ $employee->start_date ? $employee->start_date->translatedFormat('d M Y') : '-' }}
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wider {{ $employee->status === 'Aktif' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-slate-100 text-slate-500 border border-slate-200' }}">
                                    {{ $employee->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="openEditEmployeeModal({{ json_encode($employee) }})" class="p-1.5 text-slate-400 hover:text-slate-900 hover:bg-slate-100 rounded-md transition-colors cursor-pointer bg-transparent border-none outline-none" title="Edit Karyawan">
                                        <span class="material-symbols-outlined text-base">edit</span>
                                    </button>
                                    <form action="{{ route('employee.destroy', $employee->id) }}" method="POST" class="inline m-0 p-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus karyawan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-450 hover:text-rose-700 hover:bg-rose-50 rounded-md transition-colors cursor-pointer bg-transparent border-none outline-none" title="Hapus Karyawan">
                                            <span class="material-symbols-outlined text-base">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-xs font-medium text-slate-400">
                                Tidak ada data karyawan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Employee Modal -->
    <div id="add-employee-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[999] flex items-center justify-center hidden p-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-500">person_add</span>
                    Tambah Karyawan Baru
                </h3>
                <button onclick="closeAddEmployeeModal()" class="material-symbols-outlined text-slate-400 hover:text-slate-600 cursor-pointer border-none bg-transparent outline-none">close</button>
            </div>
            <form action="{{ route('employee.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Nama Lengkap</label>
                        <input type="text" name="name" required placeholder="e.g. Andini" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Peranan (Role)</label>
                        <select name="role" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-semibold text-slate-700 outline-none focus:ring-1 focus:ring-slate-900 cursor-pointer">
                            <option value="Kasir">Kasir</option>
                            <option value="Supervisor">Supervisor</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Email</label>
                        <input type="email" name="email" placeholder="e.g. andini@example.com" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">No Telepon</label>
                        <input type="text" name="phone" placeholder="e.g. 0812-3456-7890" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Alamat Asal</label>
                        <input type="text" name="home_address" placeholder="e.g. Bandung, Jawa Barat" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Tanggal Mulai Kerja</label>
                        <input type="date" name="start_date" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Status</label>
                        <select name="status" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-semibold text-slate-700 outline-none focus:ring-1 focus:ring-slate-900 cursor-pointer">
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                    <button type="button" onclick="closeAddEmployeeModal()" class="px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-lg text-xs font-bold uppercase cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-bold uppercase cursor-pointer">Simpan Karyawan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Employee Modal -->
    <div id="edit-employee-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[999] flex items-center justify-center hidden p-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-500">edit</span>
                    Edit Karyawan
                </h3>
                <button onclick="closeEditEmployeeModal()" class="material-symbols-outlined text-slate-400 hover:text-slate-600 cursor-pointer border-none bg-transparent outline-none">close</button>
            </div>
            <form id="edit-employee-form" action="" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Nama Lengkap</label>
                        <input type="text" name="name" id="edit-name" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Peranan (Role)</label>
                        <select name="role" id="edit-role" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-semibold text-slate-700 outline-none focus:ring-1 focus:ring-slate-900 cursor-pointer">
                            <option value="Kasir">Kasir</option>
                            <option value="Supervisor">Supervisor</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Email</label>
                        <input type="email" name="email" id="edit-email" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">No Telepon</label>
                        <input type="text" name="phone" id="edit-phone" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Alamat Asal</label>
                        <input type="text" name="home_address" id="edit-home-address" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Tanggal Mulai Kerja</label>
                        <input type="date" name="start_date" id="edit-start-date" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Status</label>
                        <select name="status" id="edit-status" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-semibold text-slate-700 outline-none focus:ring-1 focus:ring-slate-900 cursor-pointer">
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                    <button type="button" onclick="closeEditEmployeeModal()" class="px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-lg text-xs font-bold uppercase cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-bold uppercase cursor-pointer">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    function openAddEmployeeModal() {
        document.getElementById('add-employee-modal').classList.remove('hidden');
    }
    function closeAddEmployeeModal() {
        document.getElementById('add-employee-modal').classList.add('hidden');
    }

    function openEditEmployeeModal(employee) {
        document.getElementById('edit-employee-form').action = "/operasional/karyawan/" + employee.id;
        
        document.getElementById('edit-name').value = employee.name;
        document.getElementById('edit-role').value = employee.role;
        document.getElementById('edit-email').value = employee.email || '';
        document.getElementById('edit-phone').value = employee.phone || '';
        document.getElementById('edit-home-address').value = employee.home_address || '';
        
        let startDateVal = '';
        if (employee.start_date) {
            startDateVal = employee.start_date.split('T')[0];
        }
        document.getElementById('edit-start-date').value = startDateVal;

        document.getElementById('edit-status').value = employee.status;

        document.getElementById('edit-employee-modal').classList.remove('hidden');
    }
    function closeEditEmployeeModal() {
        document.getElementById('edit-employee-modal').classList.add('hidden');
    }
</script>
@endpush
