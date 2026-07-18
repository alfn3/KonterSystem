@extends('layouts.app')

@section('title', 'Manajemen User')
@section('subtitle', 'Kelola pengguna yang memiliki hak akses untuk masuk ke dalam panel admin sistem ini.')

@section('content')

    <!-- Controls & Add Button (Premium Banner) -->
    <div class="greeting-banner mb-6 flex-col sm:flex-row items-center justify-between gap-4" style="padding: 16px 20px;">
        <div class="flex flex-wrap items-center justify-between gap-4 w-full">
            <!-- Left Group: Search -->
            <form action="{{ route('user.index') }}" method="GET" class="flex items-center gap-3 flex-1 min-w-[280px] m-0 p-0" style="position:relative;z-index:1;">
                <div class="relative w-full max-w-[280px] shadow-sm rounded-lg">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-indigo-600 text-sm">search</span>
                    <input type="text" name="search" value="{{ request('search') }}" class="w-full text-xs border border-white/20 rounded-lg pl-9 pr-4 py-2 focus:ring-1 focus:ring-white outline-none transition-all bg-white/95 text-slate-800 font-bold placeholder-slate-400" placeholder="Cari nama, email, atau no WA...">
                </div>
                <button type="submit" class="px-4 py-2 bg-white/20 border border-white/30 text-white rounded-lg text-xs font-bold hover:bg-white/30 transition-all cursor-pointer backdrop-blur-sm shadow-sm">Cari</button>
                @if(request('search'))
                    <a href="{{ route('user.index') }}" class="px-3 py-2 bg-red-500/80 hover:bg-red-500 text-white border border-red-400/50 text-xs font-bold rounded-lg flex items-center justify-center transition-all cursor-pointer backdrop-blur-sm shadow-sm">Reset</a>
                @endif
            </form>

            <!-- Right Group: Action -->
            <button onclick="openAddUserModal()" class="px-4 py-2 bg-white text-indigo-600 border border-white/20 rounded-lg flex items-center gap-2 text-[11px] font-extrabold uppercase tracking-wider hover:bg-slate-50 transition-all cursor-pointer shadow-sm shrink-0" style="position:relative;z-index:1;">
                <span class="material-symbols-outlined text-sm">person_add</span> Tambah User Baru
            </button>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider w-16">Inisial</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Pengguna</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Akses Login</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Cabang</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Terdaftar Sejak</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-center">Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="h-8 w-8 rounded-full bg-slate-900 text-white text-xs font-bold flex items-center justify-center">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900">
                                {{ $user->name }}
                                @if($user->id === auth()->id())
                                    <span class="ml-2 px-1.5 py-0.5 rounded text-[9px] bg-emerald-100 text-emerald-800 border border-emerald-200 font-bold uppercase tracking-wider">Saya</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1.5">
                                    @if($user->email)
                                        <div class="flex items-center gap-1.5 text-xs text-slate-700">
                                            <span class="material-symbols-outlined text-sm text-slate-500">mail</span>
                                            <span class="font-bold">Web:</span> {{ $user->email }}
                                        </div>
                                    @endif
                                    @if($user->whatsapp || $user->agent_id)
                                        <div class="flex items-center gap-1.5 text-xs text-slate-700">
                                            <span class="material-symbols-outlined text-sm text-slate-500">smartphone</span>
                                            <span class="font-bold text-indigo-600">Mobile:</span> ID: <span class="font-semibold">{{ $user->agent_id ?: '-' }}</span> • WA: <span class="font-semibold">{{ $user->whatsapp ?: '-' }}</span>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-slate-700">
                                @if($user->agent_id)
                                    {{ $user->branch ? $user->branch->name : 'Cabang Tidak Ditemukan' }}
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-650 text-xs font-medium">
                                {{ $user->created_at ? $user->created_at->translatedFormat('d M Y H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <form action="{{ route('user.update', $user->id) }}" method="POST" class="inline-flex items-center m-0 p-0">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="toggle_status" value="1">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" onchange="this.form.submit()" class="sr-only peer" {{ $user->is_active ? 'checked' : '' }} @if($user->id === auth()->id()) disabled @endif>
                                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500 @if($user->id === auth()->id()) opacity-60 cursor-not-allowed @endif"></div>
                                        <span class="ml-2 text-xs font-bold @if($user->is_active) text-emerald-600 @else text-slate-400 @endif">
                                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </label>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="openEditUserModal({{ json_encode($user) }})" class="p-1.5 text-slate-400 hover:text-slate-900 hover:bg-slate-100 rounded-md transition-colors cursor-pointer bg-transparent border-none outline-none" title="Edit User">
                                        <span class="material-symbols-outlined text-base">edit</span>
                                    </button>
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('user.destroy', $user->id) }}" method="POST" class="inline m-0 p-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-rose-450 hover:text-rose-700 hover:bg-rose-50 rounded-md transition-colors cursor-pointer bg-transparent border-none outline-none" title="Hapus User">
                                                <span class="material-symbols-outlined text-base">delete</span>
                                            </button>
                                        </form>
                                    @else
                                        <button disabled class="p-1.5 text-slate-300 cursor-not-allowed bg-transparent border-none outline-none" title="Anda tidak dapat menghapus diri sendiri">
                                            <span class="material-symbols-outlined text-base">delete</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-xs font-medium text-slate-400">
                                Tidak ada data user.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add User Modal -->
    <div id="add-user-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[999] flex items-center justify-center hidden p-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-md overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-500">person_add</span>
                    Tambah User Baru
                </h3>
                <button onclick="closeAddUserModal()" class="material-symbols-outlined text-slate-400 hover:text-slate-600 cursor-pointer border-none bg-transparent outline-none">close</button>
            </div>
            <form action="{{ route('user.store') }}" method="POST" id="add-user-form" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Nama Lengkap</label>
                    <input type="text" name="name" required placeholder="e.g. Administrator" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Tipe Akses Login</label>
                    <select name="login_access" id="add_login_access" onchange="toggleAddFields()" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-semibold text-slate-700 outline-none focus:ring-1 focus:ring-slate-900 cursor-pointer">
                        <option value="web">Akses Web Portal</option>
                        <option value="mobile">Akses Aplikasi Mobile</option>
                    </select>
                </div>

                <!-- Web Portal Fields -->
                <div id="add_web_fields" class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Alamat Email</label>
                        <input type="email" name="email" id="add_email" placeholder="e.g. admin@example.com" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Kata Sandi</label>
                        <input type="password" name="password" id="add_password" placeholder="Minimal 8 karakter" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Konfirmasi Kata Sandi</label>
                        <input type="password" name="password_confirmation" id="add_password_confirmation" placeholder="Ulangi kata sandi" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                    </div>
                </div>

                <!-- Mobile Fields -->
                <div id="add_mobile_fields" class="space-y-4 hidden">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Cabang (Branch)</label>
                        <select name="agent_id" id="add_agent_id" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-semibold text-slate-700 outline-none focus:ring-1 focus:ring-slate-900 cursor-pointer">
                            <option value="">-- Pilih Cabang --</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->agent_id }}">{{ $b->name }} ({{ $b->agent_id ?: 'Belum Ada Agent ID' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Nomor WhatsApp Aktif</label>
                        <input type="text" name="whatsapp" id="add_whatsapp" placeholder="e.g. 08123456789" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="closeAddUserModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition-all cursor-pointer">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg text-xs font-bold hover:bg-slate-800 transition-all cursor-pointer">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="edit-user-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[999] flex items-center justify-center hidden p-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-md overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-500">manage_accounts</span>
                    Edit Data User
                </h3>
                <button onclick="closeEditUserModal()" class="material-symbols-outlined text-slate-400 hover:text-slate-600 cursor-pointer border-none bg-transparent outline-none">close</button>
            </div>
            <form id="edit-user-form" action="" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Nama Lengkap</label>
                    <input type="text" name="name" id="edit_name" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Tipe Akses Login</label>
                    <select name="login_access" id="edit_login_access" onchange="toggleEditFields()" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-semibold text-slate-700 outline-none focus:ring-1 focus:ring-slate-900 cursor-pointer">
                        <option value="web">Akses Web Portal</option>
                        <option value="mobile">Akses Aplikasi Mobile</option>
                    </select>
                </div>

                <!-- Web Portal Fields -->
                <div id="edit_web_fields" class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Alamat Email</label>
                        <input type="email" name="email" id="edit_email" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                    </div>
                    <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 text-[11px] text-slate-500 font-medium">
                        Kosongkan kata sandi di bawah jika Anda tidak ingin merubahnya.
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Kata Sandi Baru</label>
                        <input type="password" name="password" placeholder="Minimal 8 karakter (opsional)" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Konfirmasi Kata Sandi Baru</label>
                        <input type="password" name="password_confirmation" placeholder="Ulangi kata sandi baru (opsional)" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                    </div>
                </div>

                <!-- Mobile Fields -->
                <div id="edit_mobile_fields" class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Cabang (Branch)</label>
                        <select name="agent_id" id="edit_agent_id" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-semibold text-slate-700 outline-none focus:ring-1 focus:ring-slate-900 cursor-pointer">
                            <option value="">-- Pilih Cabang --</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->agent_id }}">{{ $b->name }} ({{ $b->agent_id ?: 'Belum Ada Agent ID' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Nomor WhatsApp Aktif</label>
                        <input type="text" name="whatsapp" id="edit_whatsapp" placeholder="e.g. 08123456789" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-xs font-medium text-slate-700 outline-none focus:ring-1 focus:ring-slate-900">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="closeEditUserModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition-all cursor-pointer">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg text-xs font-bold hover:bg-slate-800 transition-all cursor-pointer">Perbarui</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    function toggleAddFields() {
        const loginAccess = document.getElementById('add_login_access').value;
        const webFields = document.getElementById('add_web_fields');
        const mobileFields = document.getElementById('add_mobile_fields');
        
        const emailInput = document.getElementById('add_email');
        const passwordInput = document.getElementById('add_password');
        const passConfirmInput = document.getElementById('add_password_confirmation');
        const agentIdInput = document.getElementById('add_agent_id');
        const whatsappInput = document.getElementById('add_whatsapp');
        
        if (loginAccess === 'web') {
            webFields.classList.remove('hidden');
            mobileFields.classList.add('hidden');
            emailInput.required = true;
            passwordInput.required = true;
            passConfirmInput.required = true;
            agentIdInput.required = false;
            whatsappInput.required = false;
            agentIdInput.value = '';
            whatsappInput.value = '';
        } else {
            webFields.classList.add('hidden');
            mobileFields.classList.remove('hidden');
            emailInput.required = false;
            passwordInput.required = false;
            passConfirmInput.required = false;
            emailInput.value = '';
            passwordInput.value = '';
            passConfirmInput.value = '';
            agentIdInput.required = true;
            whatsappInput.required = true;
        }
    }

    function toggleEditFields() {
        const loginAccess = document.getElementById('edit_login_access').value;
        const webFields = document.getElementById('edit_web_fields');
        const mobileFields = document.getElementById('edit_mobile_fields');
        
        const emailInput = document.getElementById('edit_email');
        const agentIdInput = document.getElementById('edit_agent_id');
        const whatsappInput = document.getElementById('edit_whatsapp');
        
        if (loginAccess === 'web') {
            webFields.classList.remove('hidden');
            mobileFields.classList.add('hidden');
            emailInput.required = true;
            agentIdInput.required = false;
            whatsappInput.required = false;
            agentIdInput.value = '';
            whatsappInput.value = '';
        } else {
            webFields.classList.add('hidden');
            mobileFields.classList.remove('hidden');
            emailInput.required = false;
            agentIdInput.required = true;
            whatsappInput.required = true;
        }
    }

    function openAddUserModal() {
        document.getElementById('add_login_access').value = 'web';
        toggleAddFields();
        document.getElementById('add-user-modal').classList.remove('hidden');
    }
    
    function closeAddUserModal() {
        document.getElementById('add-user-modal').classList.add('hidden');
    }
    
    function openEditUserModal(user) {
        document.getElementById('edit_name').value = user.name;
        document.getElementById('edit_email').value = user.email || '';
        document.getElementById('edit_agent_id').value = user.agent_id || '';
        document.getElementById('edit_whatsapp').value = user.whatsapp || '';
        
        const loginAccessSelect = document.getElementById('edit_login_access');
        if (user.email) {
            loginAccessSelect.value = 'web';
        } else {
            loginAccessSelect.value = 'mobile';
        }
        
        toggleEditFields();
        
        // Update form action dynamically
        const form = document.getElementById('edit-user-form');
        form.action = `/sistem/user/${user.id}`;
        
        document.getElementById('edit-user-modal').classList.remove('hidden');
    }
    
    function closeEditUserModal() {
        document.getElementById('edit-user-modal').classList.add('hidden');
    }
</script>
@endpush
