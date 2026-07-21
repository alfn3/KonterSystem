@extends('layouts.app')

@section('title', 'Rincian Setoran Cabang')
@section('subtitle', 'Pilih cabang dan catat rincian fisik uang kas harian.')

@section('content')

    <!-- Premium Banner for Filter -->
    <div class="greeting-banner mb-0 rounded-t-xl rounded-b-none relative z-10 flex-col sm:flex-row items-center justify-between gap-4" style="padding: 16px 20px;">
        <form action="{{ route('daily-deposits.index') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full sm:w-auto m-0" style="position:relative;z-index:1;">
            
            <div class="flex items-center gap-2">
                <!-- Date Picker -->
                <div class="flex items-center gap-1.5 px-3 py-2 bg-white/95 border border-white/20 rounded-lg shadow-sm">
                    <span class="material-symbols-outlined text-sm text-indigo-600">calendar_today</span>
                    <input name="date" onchange="this.form.submit()" class="bg-transparent border-none p-0 text-sm font-bold text-slate-800 focus:ring-0 cursor-pointer w-32 outline-none" type="date" value="{{ $date }}">
                </div>

                <!-- Branch Dropdown -->
                <div class="flex items-center gap-1.5 px-3 py-2 bg-white/95 border border-white/20 rounded-lg shadow-sm">
                    <span class="material-symbols-outlined text-sm text-indigo-600">storefront</span>
                    <select name="branch_id" onchange="this.form.submit()" class="bg-transparent border-none p-0 text-sm font-bold text-slate-800 focus:ring-0 cursor-pointer w-48 outline-none">
                        <option value="">Semua Cabang</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $selectedBranchId == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            
        </form>
    </div>
    <!-- Custom Page Skeleton Loader (Hidden by Default) -->
    <div id="custom-page-skeleton" class="hidden w-full animate-pulse bg-white p-4 sm:p-6 rounded-b-xl border border-slate-200 border-t-0 shadow-sm mb-8" style="margin-top: 0;">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="h-48 bg-slate-50 border border-slate-100 rounded-2xl"></div>
            <div class="h-48 bg-slate-50 border border-slate-100 rounded-2xl"></div>
            <div class="h-48 bg-slate-50 border border-slate-100 rounded-2xl"></div>
            <div class="h-48 bg-slate-50 border border-slate-100 rounded-2xl"></div>
        </div>
    </div>

    <!-- Main Content Wrapper -->
    <div id="main-content" class="bg-white p-4 sm:p-6 rounded-b-xl border border-slate-200 border-t-0 shadow-sm mb-8" style="margin-top: 0;">


    <!-- Alert Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-bold flex items-center gap-3 shadow-sm">
            <span class="material-symbols-outlined">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    @php
        $filteredBranches = $selectedBranchId ? $branches->where('id', $selectedBranchId) : $branches;
        $grandTotalSemua = 0;
    @endphp

    @if($filteredBranches->count() > 0)
        <!-- Grid of Branch Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            @foreach($filteredBranches as $branch)
                @php
                    $dep = $deposits->get($branch->id);
                    $amount = $dep ? $dep->amount : 0;
                    $grandTotalSemua += $amount;
                @endphp
                
                <!-- Branch Card -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col transition-all hover:shadow-md hover:border-indigo-200 group">
                    <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-slate-50 to-white">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100 group-hover:scale-110 transition-transform">
                                <span class="material-symbols-outlined text-sm">store</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 text-sm">{{ $branch->name }}</h3>
                                <p class="text-[10px] text-slate-500 font-medium flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[10px]">schedule</span> {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                                </p>
                            </div>
                        </div>
                        <button type="button" onclick="openModal('modal_deposit_{{ $branch->id }}')" class="px-2 py-1 bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white text-[10px] font-bold rounded border border-indigo-200 hover:border-indigo-600 flex items-center gap-1 transition-colors">
                            <span class="material-symbols-outlined text-[12px]">edit_square</span> Edit
                        </button>
                    </div>
                    
                    <div class="p-5 flex-1 flex flex-col justify-between bg-white relative overflow-hidden">
                        <!-- Decorative bg -->
                        <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-emerald-50 rounded-full blur-2xl opacity-60"></div>
                        
                        @if($dep)
                            <div class="relative z-10 w-full space-y-1.5 flex-1 mb-4">
                                @php
                                    $details = [
                                        'Bendel Jutaan' => $dep->bendel_jutaan,
                                        'Bendel Puluhan' => $dep->bendel_puluhan,
                                        'Bendel Ribuan' => $dep->bendel_ribuan,
                                        'Koin' => $dep->koin,
                                        'Sisa 100/50' => $dep->sisa_100_50,
                                        'Sisa 20/10/5' => $dep->sisa_20_10_5,
                                        'Sisa 2/1' => $dep->sisa_2_1,
                                        'Lainnya' => $dep->sisa_lain,
                                    ];
                                @endphp
                                @foreach($details as $label => $val)
                                    <div class="flex items-center justify-between py-1 border-b border-slate-50 last:border-0 {{ $val == 0 ? 'opacity-50' : '' }}">
                                        <span class="text-[10px] font-semibold text-slate-500">{{ $label }}</span>
                                        <span class="text-[10px] font-bold {{ $val > 0 ? 'text-slate-800' : 'text-slate-400' }}">Rp {{ number_format($val, 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="mt-2 mb-4 flex flex-wrap gap-2 relative z-10 flex-1">
                                <span class="px-2 py-1.5 bg-orange-50 text-orange-600 border border-orange-200 text-[10px] font-bold rounded flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[12px]">warning</span> Belum diinput
                                </span>
                            </div>
                        @endif

                        <div class="flex flex-col gap-0.5 relative z-10 pt-3 border-t border-slate-100 mt-auto">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Setoran Fisik</p>
                            <h4 class="text-xl font-black text-emerald-600">
                                Rp {{ number_format($amount, 0, ',', '.') }}
                            </h4>
                        </div>
                    </div>
                </div>

                <!-- Modal Form for this Branch -->
                <div id="modal_deposit_{{ $branch->id }}" class="fixed inset-0 z-[100] hidden">
                    <!-- Backdrop -->
                    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeModal('modal_deposit_{{ $branch->id }}')"></div>
                    
                    <!-- Modal Content -->
                    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[95%] max-w-md max-h-[90vh] overflow-y-auto bg-white rounded-2xl shadow-2xl">
                        <form action="{{ route('daily-deposits.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="date" value="{{ $date }}">
                            <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                            
                            <div class="sticky top-0 bg-white/95 backdrop-blur-md px-6 py-4 border-b border-slate-100 flex items-center justify-between z-10">
                                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-indigo-600">payments</span>
                                    Edit Setoran
                                </h3>
                                <button type="button" onclick="closeModal('modal_deposit_{{ $branch->id }}')" class="text-slate-400 hover:text-red-500 bg-slate-50 hover:bg-red-50 rounded-lg p-1.5 transition-colors">
                                    <span class="material-symbols-outlined">close</span>
                                </button>
                            </div>

                            @php
                                $bendel_jutaan = $dep ? $dep->bendel_jutaan : 0;
                                $bendel_puluhan = $dep ? $dep->bendel_puluhan : 0;
                                $bendel_ribuan = $dep ? $dep->bendel_ribuan : 0;
                                $koin = $dep ? $dep->koin : 0;
                                
                                $sisa_100_50 = $dep ? $dep->sisa_100_50 : 0;
                                $sisa_20_10_5 = $dep ? $dep->sisa_20_10_5 : 0;
                                $sisa_2_1 = $dep ? $dep->sisa_2_1 : 0;
                                $sisa_lain = $dep ? $dep->sisa_lain : 0;
                            @endphp

                            <div class="p-6">
                                <div class="grid grid-cols-1 gap-y-6">
                                    
                                    <!-- Bendel Section -->
                                    <div>
                                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-1 border-b border-slate-50 pb-2">
                                            <span class="material-symbols-outlined text-[14px]">account_balance_wallet</span> Bendel & Koin
                                        </h4>
                                        <div class="space-y-4">
                                            @foreach([
                                                ['label' => 'Bendel Jutaan', 'name' => 'bendel_jutaan', 'val' => $bendel_jutaan],
                                                ['label' => 'Bendel Puluhan', 'name' => 'bendel_puluhan', 'val' => $bendel_puluhan],
                                                ['label' => 'Bendel Ribuan', 'name' => 'bendel_ribuan', 'val' => $bendel_ribuan],
                                                ['label' => 'Koin', 'name' => 'koin', 'val' => $koin],
                                            ] as $field)
                                            <div class="flex items-center justify-between gap-3">
                                                <label class="text-sm font-semibold text-slate-700 w-1/2">{{ $field['label'] }}</label>
                                                <div class="relative w-1/2">
                                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <span class="text-slate-500 font-semibold text-xs">Rp</span>
                                                    </div>
                                                    <input type="text" id="{{ $field['name'] }}_{{ $branch->id }}" name="{{ $field['name'] }}" value="{{ number_format($field['val'], 0, ',', '.') }}" class="pl-9 w-full text-right text-sm font-bold border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 py-2 format-rupiah" oninput="calculateTotal({{ $branch->id }})">
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Sisa Section -->
                                    <div>
                                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-1 border-b border-slate-50 pb-2">
                                            <span class="material-symbols-outlined text-[14px]">receipt_long</span> Sisa (Lembaran Lepas)
                                        </h4>
                                        <div class="space-y-4">
                                            @foreach([
                                                ['label' => 'Sisa 100/50', 'name' => 'sisa_100_50', 'val' => $sisa_100_50],
                                                ['label' => 'Sisa 20/10/5', 'name' => 'sisa_20_10_5', 'val' => $sisa_20_10_5],
                                                ['label' => 'Sisa 2/1', 'name' => 'sisa_2_1', 'val' => $sisa_2_1],
                                                ['label' => 'Lainnya', 'name' => 'sisa_lain', 'val' => $sisa_lain],
                                            ] as $field)
                                            <div class="flex items-center justify-between gap-3">
                                                <label class="text-sm font-semibold text-slate-700 w-1/2">{{ $field['label'] }}</label>
                                                <div class="relative w-1/2">
                                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <span class="text-slate-500 font-semibold text-xs">Rp</span>
                                                    </div>
                                                    <input type="text" id="{{ $field['name'] }}_{{ $branch->id }}" name="{{ $field['name'] }}" value="{{ number_format($field['val'], 0, ',', '.') }}" class="pl-9 w-full text-right text-sm font-bold border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 py-2 format-rupiah" oninput="calculateTotal({{ $branch->id }})">
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>

                                </div>
                            </div>
                            
                            <div class="px-6 py-5 bg-slate-50 border-t border-slate-100 rounded-b-2xl flex flex-col gap-4">
                                <div class="flex items-center justify-between gap-2 w-full">
                                    <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Total Setoran</label>
                                    <div class="relative w-48">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-emerald-700 font-black text-sm">Rp</span>
                                        </div>
                                        <input type="text" id="total_amount_{{ $branch->id }}" name="amount" value="{{ number_format($amount, 0, ',', '.') }}" class="pl-10 w-full text-right text-base font-black text-emerald-700 bg-emerald-100 border-none rounded-lg focus:ring-0 py-2" readonly>
                                    </div>
                                </div>

                                <button type="submit" class="w-full px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-indigo-200 flex items-center justify-center gap-2 transition-all">
                                    <span class="material-symbols-outlined text-sm">save</span> Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Summary Total if all branches are shown -->
        @if(!$selectedBranchId && $filteredBranches->count() > 1)
            <div class="bg-indigo-600 rounded-2xl shadow-lg shadow-indigo-200 p-6 flex items-center justify-between text-white mt-4">
                <div>
                    <h3 class="text-indigo-100 font-semibold text-sm mb-1 uppercase tracking-wider">Grand Total Setoran (Semua Cabang)</h3>
                    <p class="text-3xl font-black">Rp {{ number_format($grandTotalSemua, 0, ',', '.') }}</p>
                </div>
                <div class="hidden sm:flex w-16 h-16 bg-white/20 rounded-full items-center justify-center backdrop-blur-md border border-white/20">
                    <span class="material-symbols-outlined text-3xl">account_balance</span>
                </div>
            </div>
        @endif
        
    @else
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-12 text-center">
            <span class="material-symbols-outlined text-5xl text-slate-300 mb-4 block">storefront</span>
            <h3 class="text-lg font-bold text-slate-700 mb-1">Tidak Ada Cabang</h3>
            <p class="text-slate-500 text-sm">Silakan buat cabang terlebih dahulu di menu Operasional atau ganti filter pencarian.</p>
        </div>
    @endif

    <script>
        // Modal logic
        function openModal(id) {
            const modal = document.getElementById(id);
            if(modal) {
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            if(modal) {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }

        // Formatting Rupiah as user types
        document.querySelectorAll('.format-rupiah').forEach(function(input) {
            input.addEventListener('focus', function() {
                if(this.value === '0') this.value = '';
            });
            input.addEventListener('blur', function() {
                if(this.value === '') this.value = '0';
            });
            input.addEventListener('input', function(e) {
                let val = this.value.replace(/[^,\d]/g, '');
                this.value = formatRupiah(val);
            });
        });

        function formatRupiah(angka) {
            if(!angka) return '';
            var number_string = angka.toString().replace(/[^,\d]/g, ''),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return rupiah;
        }

        function parseRupiah(rupiahStr) {
            if (!rupiahStr) return 0;
            return parseInt(rupiahStr.replace(/[^,\d]/g, '')) || 0;
        }

        function calculateTotal(branchId) {
            const fields = [
                'bendel_jutaan', 'bendel_puluhan', 'bendel_ribuan', 'koin',
                'sisa_100_50', 'sisa_20_10_5', 'sisa_2_1', 'sisa_lain'
            ];
            
            let total = 0;
            fields.forEach(function(field) {
                let input = document.getElementById(`${field}_${branchId}`);
                if (input) {
                    total += parseRupiah(input.value);
                }
            });
            
            let amountInput = document.getElementById(`total_amount_${branchId}`);
            if (amountInput) {
                amountInput.value = formatRupiah(total.toString());
            }
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


    </div> <!-- End Main Content Wrapper -->
@endsection
