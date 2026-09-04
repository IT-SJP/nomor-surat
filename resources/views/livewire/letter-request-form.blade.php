<div class="space-y-7 max-w-4xl mx-auto" x-data="{ 
    copiedPreview: false,
    copiedSuccess: false,
    setSubject(val) {
        $wire.set('subject', val);
    },
    setTarget(val) {
        $wire.set('target_code', val);
    }
}">
    <!-- Top Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center gap-4 pb-1">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                Buat Nomor Surat Keluar
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-2xl">
                @if($isKaryawan)
                    Formulir penerbitan nomor surat resmi cabang <strong class="text-primary-600 font-bold">{{ $branch_name }}</strong>.
                @else
                    Penerbitan nomor surat keluar untuk seluruh entitas anak perusahaan SJP Holding.
                @endif
            </p>
        </div>
    </div>

    <!-- Live Preview Card (Modern Glass / SJP Green Card) -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-primary-900 via-emerald-950 to-slate-900 text-white p-6 sm:p-7 shadow-lg border border-primary-500/30">
        <!-- Background subtle glow -->
        <div class="absolute -right-16 -top-16 w-56 h-56 bg-primary-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -bottom-16 w-56 h-56 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span wire:loading.remove wire:target="branch_code, month, year, target_code" class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-400"></span>
                    </span>
                    <span wire:loading wire:target="branch_code, month, year, target_code" class="loading loading-spinner loading-xs text-emerald-400"></span>
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-300">Live Preview Nomor Surat Berikutnya</span>
                </div>
            </div>

            <!-- Big Monospace Number Display -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-black/25 p-4 sm:p-5 rounded-2xl border border-white/10 backdrop-blur-md">
                <div wire:loading.class="opacity-50 animate-sjp-pulse" wire:target="branch_code, month, year, target_code" class="font-mono font-bold text-2xl sm:text-3xl lg:text-4xl text-emerald-300 tracking-wider select-all break-all drop-shadow-sm transition-opacity">
                    {{ $previewNumber }}
                </div>

                <button
                    type="button"
                    class="btn btn-sm btn-ghost bg-white/10 hover:bg-white/20 text-white font-semibold rounded-lg gap-2 self-start sm:self-auto border border-white/10 transition-all cursor-pointer"
                    @click="window.copyToClipboard('{{ $previewNumber }}', 'Preview Nomor'); copiedPreview = true; setTimeout(() => copiedPreview = false, 2500)"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    <span x-text="copiedPreview ? 'Tersalin!' : 'Salin'">Salin</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Interactive Form -->
    <form wire:submit="submit" class="space-y-6">
        <!-- Section 1: Entitas & Periode -->
        <div class="card bg-base-100 shadow-xs border border-slate-200/80 rounded-3xl p-6 sm:p-8 space-y-6">
            <div class="flex items-center gap-3 pb-5 border-b border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-primary-50 text-primary-700 border border-primary-100 flex items-center justify-center font-extrabold shadow-2xs">
                    1
                </div>
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900">Entitas Perusahaan & Periode Surat</h2>
                    <p class="text-xs text-slate-500">Tentukan cabang penerbit dan periode waktu surat resmi</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-5">
                <!-- Cabang Input -->
                <div class="md:col-span-6 space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Cabang / Entitas Penerbit <span class="text-rose-500">*</span>
                    </label>

                    @if($isKaryawan)
                        <!-- Karyawan Mode: Clean locked visual card -->
                        <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 flex items-center justify-between">
                            <div class="space-y-0.5">
                                <span class="text-[10px] text-slate-400 font-medium">Cabang Terdaftar Anda:</span>
                                <h4 class="font-bold text-sm text-primary-600">{{ $branch_name }}</h4>
                            </div>
                        </div>
                    @else
                        <!-- Admin Mode: Select branch -->
                        <select wire:model.live="branch_code" class="select select-bordered w-full rounded-lg text-sm text-slate-700 font-semibold bg-white focus:border-primary-500">
                            @foreach($branches as $b)
                                <option value="{{ $b['code'] }}">
                                    {{ $b['code'] }} &mdash; {{ $b['name'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('branch_code') <span class="text-rose-600 text-xs block font-semibold mt-1">{{ $message }}</span> @enderror
                    @endif
                </div>

                <!-- Tujuan / Penerima -->
                <div class="md:col-span-6 space-y-1.5" x-data="{ openTargetSuggest: false }" @click.outside="openTargetSuggest = false">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                            Tujuan / Instansi / Penerima <span class="text-rose-500">*</span>
                        </label>
                    </div>

                    <div class="relative">
                        <input
                            wire:model.live.debounce.150ms="target_code"
                            @focus="openTargetSuggest = true"
                            @click="openTargetSuggest = true"
                            type="text"
                            placeholder="Contoh: Internal Memo / Bank Mandiri"
                            class="input input-bordered w-full rounded-lg text-sm focus:border-primary-500 bg-slate-50/80 focus:bg-white pr-10"
                            autocomplete="off"
                            required
                        />
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center gap-1">
                            <span wire:loading wire:target="target_code">
                                <span class="loading loading-spinner loading-xs text-primary-600"></span>
                            </span>
                            @if(!empty($target_code))
                                <button type="button" wire:click="$set('target_code', '')" class="text-slate-400 hover:text-slate-600 cursor-pointer p-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            @endif
                        </div>

                        <!-- Autocomplete Suggestion Dropdown -->
                        <div
                            x-show="openTargetSuggest"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-1"
                            class="absolute top-full left-0 right-0 z-40 mt-1 bg-white rounded-xl shadow-xl border border-slate-200 overflow-hidden"
                            style="display: none;"
                        >
                            <div class="p-1.5 max-h-56 overflow-y-auto divide-y divide-slate-100">
                                <div class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    Pilihan Tujuan Baku Terdaftar:
                                </div>
                                @foreach($standardTargets as $st)
                                    <button
                                        type="button"
                                        wire:click="selectTarget('{{ $st->code }}', '{{ addslashes($st->name) }}')"
                                        @click="openTargetSuggest = false"
                                        class="w-full flex items-center justify-between p-2 rounded-lg hover:bg-primary-50/80 transition-colors text-left group cursor-pointer"
                                    >
                                        <div class="flex items-center gap-2">
                                            <span class="badge badge-primary badge-outline badge-sm font-mono font-bold">{{ $st->code }}</span>
                                            <span class="text-xs font-semibold text-slate-800 group-hover:text-primary-700">{{ $st->name }}</span>
                                        </div>
                                        @if($st->description)
                                            <span class="text-[11px] text-slate-400 truncate max-w-[140px]">{{ $st->description }}</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @error('target_code') <span class="text-rose-600 text-xs block font-semibold mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Bulan & Tahun -->
                <div class="md:col-span-6 grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Bulan Surat <span class="text-rose-500">*</span>
                        </label>
                        <select wire:model.live="month" class="select select-bordered w-full rounded-lg text-sm text-slate-700 bg-white focus:border-primary-500">
                            @foreach($romanMonths as $num => $roman)
                                <option value="{{ $num }}">{{ $monthNames[$num] ?? "Bulan {$num}" }} ({{ $roman }})</option>
                            @endforeach
                        </select>
                        @error('month') <span class="text-rose-600 text-xs block font-semibold mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Tahun <span class="text-rose-500">*</span>
                        </label>
                        <input
                            wire:model.live="year"
                            type="number"
                            min="2000"
                            max="3000"
                            class="input input-bordered w-full rounded-lg text-sm font-mono focus:border-primary-500 bg-slate-50/80 focus:bg-white"
                            required
                        />
                        @error('year') <span class="text-rose-600 text-xs block font-semibold mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Detail Perihal & Keperluan -->
        <div class="card bg-base-100 shadow-xs border border-slate-200/80 rounded-3xl p-6 sm:p-8 space-y-6">
            <div class="flex items-center gap-3 pb-5 border-b border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-primary-50 text-primary-700 border border-primary-100 flex items-center justify-center font-extrabold shadow-2xs">
                    2
                </div>
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900">Perihal & Keperluan Surat</h2>
                    <p class="text-xs text-slate-500">Tuliskan peruntukan dan isi ringkas nomor surat yang diajukan</p>
                </div>
            </div>

            <div class="space-y-5">
                <!-- Perihal Input -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Perihal / Judul Surat <span class="text-rose-500">*</span>
                    </label>
                    <input
                        wire:model="subject"
                        type="text"
                        placeholder="Contoh: Laporan Kegiatan Operasional Tambang Periode Q3"
                        class="input input-bordered w-full rounded-lg text-sm focus:border-primary-500 bg-slate-50/80 focus:bg-white"
                        required
                    />
                    @error('subject') <span class="text-rose-600 text-xs block font-semibold mt-1">{{ $message }}</span> @enderror

                    <!-- Fast suggestion chips for Subject -->
                    <div class="flex flex-wrap items-center gap-1.5 pt-1">
                        <span class="text-[10px] text-slate-400 font-medium">Kategori:</span>
                        <button type="button" @click="setSubject('Surat Tugas Lapangan')" class="badge badge-primary badge-soft hover:bg-primary-600 hover:text-white text-[10px] font-semibold cursor-pointer py-1 px-2 rounded-md transition-all">Surat Tugas</button>
                        <button type="button" @click="setSubject('Surat Pengantar Dokumen')" class="badge badge-primary badge-soft hover:bg-primary-600 hover:text-white text-[10px] font-semibold cursor-pointer py-1 px-2 rounded-md transition-all">Surat Pengantar</button>
                        <button type="button" @click="setSubject('Laporan Kegiatan Bulanan')" class="badge badge-primary badge-soft hover:bg-primary-600 hover:text-white text-[10px] font-semibold cursor-pointer py-1 px-2 rounded-md transition-all">Laporan Bulanan</button>
                        <button type="button" @click="setSubject('Surat Penawaran Harga')" class="badge badge-primary badge-soft hover:bg-primary-600 hover:text-white text-[10px] font-semibold cursor-pointer py-1 px-2 rounded-md transition-all">Penawaran</button>
                        <button type="button" @click="setSubject('Surat Permohonan Kerjasama')" class="badge badge-primary badge-soft hover:bg-primary-600 hover:text-white text-[10px] font-semibold cursor-pointer py-1 px-2 rounded-md transition-all">Permohonan</button>
                    </div>
                </div>

                <!-- Keperluan / Keterangan -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Keperluan / Keterangan
                    </label>
                    <textarea
                        wire:model="purpose"
                        rows="3"
                        placeholder="Jelaskan secara ringkas peruntukan atau isi surat keluar..."
                        class="textarea textarea-bordered w-full rounded-lg text-sm focus:border-primary-500 bg-slate-50/80 focus:bg-white"
                    ></textarea>
                    @error('purpose') <span class="text-rose-600 text-xs block font-semibold mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Lokasi Arsip / Link Dokumen -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Lokasi Arsip Fisik / Link Drive <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                    </label>
                    <input
                        wire:model="archive_location"
                        type="text"
                        placeholder="Contoh: Lemari Arsip 01-B / Link Dokumen Google Drive"
                        class="input input-bordered w-full rounded-lg text-sm focus:border-primary-500 bg-slate-50/80 focus:bg-white"
                    />
                </div>
            </div>
        </div>

        <!-- Section 3: Data Pemohon (Karyawan) -->
        <div class="card bg-base-100 shadow-xs border border-slate-200/80 rounded-3xl p-6 sm:p-8 space-y-6">
            <div class="flex items-center justify-between pb-5 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary-50 text-primary-700 border border-primary-100 flex items-center justify-center font-extrabold shadow-2xs">
                        3
                    </div>
                    <div>
                        <h2 class="text-lg font-extrabold text-slate-900">Data Pemohon (Karyawan)</h2>
                        <p class="text-xs text-slate-500">Identitas penanggung jawab pengajuan nomor surat</p>
                    </div>
                </div>
            </div>

            <!-- Admin Mode: Select2-Style Searchable Employee Combobox -->
            @if(!$isKaryawan)
                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-primary-700">
                        Cari / Pilih Karyawan:
                    </label>

                    @if($selectedEmployee)
                        <!-- Selected Employee Card (Select2 Selected State) -->
                        <div class="flex items-center justify-between p-3.5 rounded-2xl border border-primary-200 bg-primary-50/60 transition-all shadow-2xs">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-primary-600 text-white flex items-center justify-center font-extrabold text-sm shadow-xs shrink-0">
                                    {{ strtoupper(substr($selectedEmployee['name'], 0, 2)) }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-extrabold text-sm text-slate-900">{{ $selectedEmployee['name'] }}</span>
                                        <span class="badge badge-primary badge-outline badge-xs font-mono font-bold">{{ $selectedEmployee['branch_code'] ?: 'Pusat' }}</span>
                                    </div>
                                    <p class="text-xs text-slate-600">{{ $selectedEmployee['department'] }} &bull; {{ $selectedEmployee['position'] }}</p>
                                </div>
                            </div>
                            <button
                                type="button"
                                wire:click="clearSelectedEmployee"
                                class="btn btn-sm btn-ghost btn-circle text-slate-400 hover:text-rose-600 hover:bg-rose-50 cursor-pointer"
                                title="Hapus Pilihan"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @else
                        <!-- Searchable Select Input  -->
                        <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input
                                    wire:model.live.debounce.150ms="employeeSearch"
                                    @focus="open = true"
                                    @click="open = true"
                                    type="text"
                                    placeholder="Ketik nama karyawan..."
                                    class="input input-bordered w-full rounded-xl text-sm pl-10 pr-16 focus:border-primary-500 bg-slate-50/80 focus:bg-white transition-all shadow-2xs"
                                    autocomplete="off"
                                />
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center gap-1.5">
                                    <span wire:loading wire:target="employeeSearch">
                                        <span class="loading loading-spinner loading-xs text-primary-600"></span>
                                    </span>
                                    @if(!empty($employeeSearch))
                                        <button type="button" wire:click="$set('employeeSearch', '')" class="text-slate-400 hover:text-slate-600 cursor-pointer p-1">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    @endif
                                    <button type="button" @click="open = !open" class="text-slate-400 hover:text-slate-600 cursor-pointer p-1">
                                        <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Autocomplete Dropdown Menu -->
                            <div
                                x-show="open"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 translate-y-1"
                                class="absolute top-full left-0 right-0 z-50 mt-1.5 bg-base-100 rounded-2xl shadow-xl border border-slate-200 overflow-hidden"
                                style="display: none;"
                            >
                                @if(!empty($employeeResults))
                                    <div class="p-1.5 max-h-72 overflow-y-auto divide-y divide-slate-100">
                                        @foreach($employeeResults as $emp)
                                            <button
                                                type="button"
                                                wire:click="selectEmployee({{ json_encode($emp) }})"
                                                @click="open = false"
                                                class="w-full flex items-center justify-between p-2.5 rounded-xl hover:bg-primary-50/80 transition-colors text-left group cursor-pointer"
                                            >
                                                <div class="flex items-center gap-3">
                                                    <div class="w-9 h-9 rounded-xl bg-primary-100 text-primary-700 group-hover:bg-primary-600 group-hover:text-white flex items-center justify-center font-extrabold text-xs transition-colors shrink-0 shadow-2xs">
                                                        {{ strtoupper(substr($emp['name'], 0, 2)) }}
                                                    </div>
                                                    <div>
                                                        <p class="font-bold text-xs text-slate-900 group-hover:text-primary-700 transition-colors">{{ $emp['name'] }}</p>
                                                        <p class="text-[11px] text-slate-500 leading-tight">{{ $emp['department'] }} &bull; {{ $emp['position'] }}</p>
                                                    </div>
                                                </div>
                                                <div class="text-right shrink-0 pl-2">
                                                    <span class="badge badge-outline badge-sm font-mono font-bold text-primary-600 border-primary-200 rounded-md">{{ $emp['branch_code'] ?: 'Pusat' }}</span>
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                @elseif(strlen(trim($employeeSearch)) >= 1)
                                    <div class="p-4 text-center text-xs text-slate-500">
                                        Tidak ada karyawan ditemukan dengan kata kunci "<span class="font-semibold text-slate-800">{{ $employeeSearch }}</span>"
                                    </div>
                                @else
                                    <div class="p-3 text-center text-xs text-slate-400">
                                        Ketik nama karyawan untuk mencari...
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="space-y-1.5 sm:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nama Lengkap Pemohon <span class="text-rose-500">*</span>
                    </label>
                    <input
                        wire:model="requestor_name"
                        type="text"
                        placeholder="Nama Lengkap Pemohon"
                        class="input input-bordered w-full rounded-lg text-sm focus:border-primary-500 bg-slate-50/80 focus:bg-white {{ $isKaryawan ? 'font-semibold cursor-not-allowed text-slate-600' : '' }}"
                        {{ $isKaryawan ? 'readonly' : '' }}
                        required
                    />
                    @error('requestor_name') <span class="text-rose-600 text-xs block font-semibold mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Departemen</label>
                    <input
                        wire:model="requestor_department"
                        type="text"
                        placeholder="Nama Departemen"
                        class="input input-bordered w-full rounded-lg text-sm text-slate-600 bg-slate-50/80 {{ ($isKaryawan && $isDepartmentLocked) ? 'font-semibold cursor-not-allowed text-slate-600 bg-slate-100/90 select-none' : 'focus:bg-white focus:border-primary-500' }}"
                        {{ ($isKaryawan && $isDepartmentLocked) ? 'readonly' : '' }}
                    />
                    @error('requestor_department') <span class="text-rose-600 text-xs block font-semibold mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Jabatan</label>
                    <input
                        wire:model="requestor_position"
                        type="text"
                        placeholder="Jabatan"
                        class="input input-bordered w-full rounded-lg text-sm text-slate-600 bg-slate-50/80 {{ ($isKaryawan && $isPositionLocked) ? 'font-semibold cursor-not-allowed text-slate-600 bg-slate-100/90 select-none' : 'focus:bg-white focus:border-primary-500' }}"
                        {{ ($isKaryawan && $isPositionLocked) ? 'readonly' : '' }}
                    />
                    @error('requestor_position') <span class="text-rose-600 text-xs block font-semibold mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Email</label>
                    <input
                        wire:model="requestor_email"
                        type="email"
                        placeholder="example@gmail.com"
                        class="input input-bordered w-full rounded-lg text-sm {{ $isEmailLocked ? 'font-semibold cursor-not-allowed text-slate-600 bg-slate-100/90 border-slate-200 select-none' : 'focus:border-primary-500 bg-slate-50/80 focus:bg-white' }}"
                        {{ $isEmailLocked ? 'readonly' : '' }}
                    />
                    @error('requestor_email') <span class="text-rose-600 text-xs block font-semibold mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">No. Telepon / WhatsApp</label>
                    <input
                        wire:model="requestor_phone"
                        type="text"
                        placeholder="08..."
                        class="input input-bordered w-full rounded-lg text-sm font-mono {{ $isPhoneLocked ? 'font-semibold cursor-not-allowed text-slate-600 bg-slate-100/90 border-slate-200 select-none' : 'focus:border-primary-500 bg-slate-50/80 focus:bg-white' }}"
                        {{ $isPhoneLocked ? 'readonly' : '' }}
                    />
                    @error('requestor_phone') <span class="text-rose-600 text-xs block font-semibold mt-1">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- Action Submission -->
        <div class="pt-2">
            <button
                type="submit"
                class="btn btn-primary w-full text-white font-extrabold text-sm sm:text-base rounded-xl shadow-md shadow-primary-600/20 py-3.5 h-auto transition-all duration-150 hover:shadow-lg hover:shadow-primary-600/30 cursor-pointer"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove class="flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Terbitkan Nomor Surat</span>
                </span>
                <span wire:loading class="flex items-center justify-center gap-2">
                    <span class="loading loading-spinner loading-sm"></span>
                    <span>Memproses Registrasi...</span>
                </span>
            </button>
        </div>
    </form>

    <!-- Success Modal Popup -->
    <div class="modal {{ $showSuccessModal ? 'modal-open' : '' }} z-[100] backdrop-blur-md bg-slate-900/40" role="dialog">
        <div class="modal-box max-w-lg rounded-3xl border border-slate-200/80 p-6 sm:p-8 text-center space-y-5 shadow-2xl bg-white relative">
            <!-- Close Button Corner -->
            <button
                type="button"
                wire:click="closeSuccessModal"
                class="btn btn-sm sm:btn-md btn-circle btn-ghost absolute right-3.5 top-3.5 sm:right-4 sm:top-4 text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer"
                title="Tutup Modal"
                aria-label="Tutup"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Icon -->
            <div class="w-16 h-16 rounded-2xl bg-primary-50 text-primary-600 border border-primary-200 flex items-center justify-center mx-auto shadow-2xs">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-9 h-9" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <div class="space-y-1">
                <span class="badge badge-success badge-soft font-bold text-emerald-800 uppercase tracking-wider px-3 py-1 rounded-md text-xs">Berhasil Diterbitkan</span>
                <h3 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 mt-1">Nomor Surat Siap Digunakan</h3>
            </div>

            @if($createdLetter)
                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 space-y-3">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Nomor Surat:</div>
                    <div class="font-mono font-black text-2xl sm:text-3xl text-primary-600 tracking-wider break-all select-all py-1">
                        {{ $createdLetter->reference_number }}
                    </div>
                    <div class="divider my-1 border-slate-200"></div>
                    <div class="text-xs text-left space-y-1.5 text-slate-600">
                        <p><strong class="text-slate-900">Perihal:</strong> {{ $createdLetter->subject }}</p>
                        <p><strong class="text-slate-900">Tujuan:</strong> {{ $createdLetter->target_code }}</p>
                        <p><strong class="text-slate-900">Pemohon:</strong> {{ $createdLetter->requestor_name }} ({{ $createdLetter->branch_code }})</p>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 justify-center pt-2">
                    <button
                        type="button"
                        class="btn btn-primary text-white font-extrabold rounded-xl gap-2 flex-1 h-12 text-sm sm:text-base shadow-md shadow-primary-600/25 cursor-pointer active:scale-[0.98] transition-all"
                        @click="window.copyToClipboard('{{ $createdLetter->reference_number }}', 'Nomor Surat'); copiedSuccess = true; setTimeout(() => copiedSuccess = false, 3000)"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <span x-text="copiedSuccess ? '✓ Nomor Tersalin!' : 'Salin Nomor Surat'">Salin Nomor Surat</span>
                    </button>

                    <a href="{{ route('letter.history') }}" wire:navigate class="btn btn-outline border-slate-200 hover:border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold rounded-xl flex-1 h-12 text-sm sm:text-base shadow-2xs active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Lihat Riwayat</span>
                    </a>
                </div>
            @endif

            <div class="modal-action justify-center pt-2 border-t border-slate-100">
                <button type="button" wire:click="createAnother" class="btn btn-ghost btn-sm sm:btn-md text-xs sm:text-sm font-bold text-slate-500 hover:text-primary-600 hover:bg-slate-50 rounded-xl px-4 cursor-pointer">
                    + Buat Nomor Surat Lainnya
                </button>
            </div>
        </div>
        <div class="modal-backdrop bg-transparent" wire:click="closeSuccessModal">
            <button class="cursor-default">close</button>
        </div>
    </div>
</div>
