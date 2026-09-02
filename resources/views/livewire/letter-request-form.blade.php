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
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-1">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                Buat Nomor Surat Keluar
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-2xl">
                @if($isKaryawan)
                    Formulir penerbitan nomor surat resmi cabang <strong class="text-primary-600 font-bold">{{ $branch_name }}</strong>. Urutan otomatis dan terjamin unik.
                @else
                    Penerbitan nomor surat keluar terpusat untuk seluruh entitas anak perusahaan PT Selamat Jaya Persada (SJP Holding).
                @endif
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <span class="badge {{ $isKaryawan ? 'badge-ghost text-slate-600' : 'badge-primary badge-soft text-primary-700' }} font-bold px-3 py-1.5 rounded-lg shadow-2xs text-xs">
                {{ $isKaryawan ? "Karyawan • {$branch_code}" : 'Admin Mode' }}
            </span>
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
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-400"></span>
                    </span>
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-300">Live Preview Nomor Registrasi Berikutnya</span>
                </div>
                <span class="text-[11px] font-mono bg-white/10 px-2.5 py-1 rounded-lg text-emerald-200 border border-white/10">
                    Auto-Increment &bull; Anti-Bentrok
                </span>
            </div>

            <!-- Big Monospace Number Display -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-black/25 p-4 sm:p-5 rounded-2xl border border-white/10 backdrop-blur-md">
                <div class="font-mono font-bold text-2xl sm:text-3xl lg:text-4xl text-emerald-300 tracking-wider select-all break-all drop-shadow-sm">
                    {{ $previewNumber }}
                </div>

                <button
                    type="button"
                    class="btn btn-sm btn-ghost bg-white/10 hover:bg-white/20 text-white font-semibold rounded-lg gap-2 self-start sm:self-auto border border-white/10 transition-all"
                    @click="navigator.clipboard.writeText('{{ $previewNumber }}'); copiedPreview = true; setTimeout(() => copiedPreview = false, 2500)"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    <span x-text="copiedPreview ? 'Tersalin!' : 'Salin'">Salin</span>
                </button>
            </div>

            <!-- Breakdown Segment Tags -->
            <div class="flex flex-wrap items-center gap-2 pt-1 text-[11px] text-emerald-200/80">
                <span class="font-semibold text-white/60">Struktur Format:</span>
                <span class="bg-white/10 px-2 py-0.5 rounded font-mono font-bold text-emerald-300">{{ $branch_code ?: 'KODE' }}</span>
                <span class="text-white/40">/</span>
                <span class="bg-white/10 px-2 py-0.5 rounded font-mono font-bold text-emerald-300">{{ $romanMonths[$month] ?? 'BULAN' }}</span>
                <span class="text-white/40">/</span>
                <span class="bg-white/10 px-2 py-0.5 rounded font-mono font-bold text-emerald-300">{{ $year ?: 'TAHUN' }}</span>
                <span class="text-white/40">/</span>
                <span class="bg-emerald-500/20 px-2 py-0.5 rounded font-mono font-bold text-emerald-200 border border-emerald-500/30">NO_URUT</span>
            </div>
        </div>
    </div>

    <!-- Interactive Form -->
    <form wire:submit="submit" class="space-y-6">
        <!-- Section 1: Entitas & Periode -->
        <div class="card bg-base-100 shadow-xs border border-slate-200/80 rounded-3xl p-6 sm:p-8 space-y-6">
            <div class="flex items-center gap-3 pb-5 border-b border-slate-100">
                <div class="w-10 h-10 rounded-2xl bg-primary-50 text-primary-700 border border-primary-100 flex items-center justify-center font-extrabold shadow-2xs">
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
                            <span class="badge badge-primary badge-soft font-mono font-bold text-xs px-2.5 py-1 rounded-md">
                                {{ $branch_code }}
                            </span>
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
                <div class="md:col-span-6 space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Tujuan / Instansi / Penerima <span class="text-rose-500">*</span>
                    </label>
                    <input
                        wire:model="target_code"
                        type="text"
                        placeholder="Contoh: Dinas ESDM / Bank Mandiri / Internal Memo"
                        class="input input-bordered w-full rounded-lg text-sm focus:border-primary-500 bg-slate-50/80 focus:bg-white"
                        required
                    />
                    @error('target_code') <span class="text-rose-600 text-xs block font-semibold mt-1">{{ $message }}</span> @enderror

                    <!-- Fast suggestion chips -->
                    <div class="flex flex-wrap items-center gap-1.5 pt-1">
                        <span class="text-[10px] text-slate-400 font-medium">Cepat:</span>
                        <button type="button" @click="setTarget('Internal Memo')" class="badge badge-primary badge-soft hover:bg-primary-600 hover:text-white text-[10px] font-semibold cursor-pointer py-1 px-2 rounded-md transition-all">Internal Memo</button>
                        <button type="button" @click="setTarget('Dinas ESDM')" class="badge badge-primary badge-soft hover:bg-primary-600 hover:text-white text-[10px] font-semibold cursor-pointer py-1 px-2 rounded-md transition-all">Dinas ESDM</button>
                        <button type="button" @click="setTarget('Perbankan')" class="badge badge-primary badge-soft hover:bg-primary-600 hover:text-white text-[10px] font-semibold cursor-pointer py-1 px-2 rounded-md transition-all">Perbankan</button>
                        <button type="button" @click="setTarget('Vendor / Supplier')" class="badge badge-primary badge-soft hover:bg-primary-600 hover:text-white text-[10px] font-semibold cursor-pointer py-1 px-2 rounded-md transition-all">Vendor</button>
                    </div>
                </div>

                <!-- Bulan & Tahun -->
                <div class="md:col-span-6 grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Bulan Surat <span class="text-rose-500">*</span>
                        </label>
                        <select wire:model.live="month" class="select select-bordered w-full rounded-lg text-sm text-slate-700 bg-white focus:border-primary-500">
                            @foreach($romanMonths as $num => $roman)
                                <option value="{{ $num }}">Bulan {{ $num }} ({{ $roman }})</option>
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
                            min="2020"
                            max="2099"
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
                <div class="w-10 h-10 rounded-2xl bg-primary-50 text-primary-700 border border-primary-100 flex items-center justify-center font-extrabold shadow-2xs">
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
                        Keperluan / Keterangan Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <textarea
                        wire:model="purpose"
                        rows="3"
                        placeholder="Jelaskan secara ringkas peruntukan atau isi surat keluar yang diajukan..."
                        class="textarea textarea-bordered w-full rounded-lg text-sm focus:border-primary-500 bg-slate-50/80 focus:bg-white"
                        required
                    ></textarea>
                    @error('purpose') <span class="text-rose-600 text-xs block font-semibold mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Lokasi Arsip / Link Dokumen -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Lokasi Arsip Fisik / Link Cloud <span class="text-slate-400 font-normal lowercase">(opsional)</span>
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
                    <div class="w-10 h-10 rounded-2xl bg-primary-50 text-primary-700 border border-primary-100 flex items-center justify-center font-extrabold shadow-2xs">
                        3
                    </div>
                    <div>
                        <h2 class="text-lg font-extrabold text-slate-900">Data Pemohon (Karyawan)</h2>
                        <p class="text-xs text-slate-500">Identitas penanggung jawab pengajuan nomor surat</p>
                    </div>
                </div>

                @if($isKaryawan)
                    <span class="badge badge-success badge-soft font-bold text-emerald-800 text-xs gap-1 px-3 py-1 rounded-md">
                        ✓ Terverifikasi SSO
                    </span>
                @endif
            </div>

            <!-- Admin Mode: Autocomplete Search -->
            @if(!$isKaryawan)
                <div class="relative space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-primary-700 mb-1.5">
                        🔍 Cari Karyawan dari Database Absenku SJP:
                    </label>
                    <div class="relative">
                        <input
                            wire:model.live.debounce.300ms="employeeSearch"
                            type="text"
                            placeholder="Ketik Nama Lengkap atau NIK Karyawan..."
                            class="input input-bordered w-full rounded-lg text-sm focus:border-primary-500 bg-slate-50/80 focus:bg-white"
                        />
                    </div>

                    <!-- Autocomplete dropdown -->
                    @if(!empty($employeeResults))
                        <ul class="menu bg-base-100 rounded-2xl shadow-xl border border-slate-200 absolute top-full left-0 right-0 z-40 mt-1 max-h-64 overflow-y-auto p-2 space-y-1">
                            @foreach($employeeResults as $emp)
                                <li>
                                    <button type="button" wire:click="selectEmployee({{ json_encode($emp) }})" class="flex justify-between items-center py-2.5 px-3 rounded-lg hover:bg-primary-50">
                                        <div class="text-left">
                                            <p class="font-bold text-xs text-slate-900">{{ $emp['name'] }}</p>
                                            <p class="text-[11px] text-slate-500">{{ $emp['department'] }} &bull; {{ $emp['position'] }}</p>
                                        </div>
                                        <span class="badge badge-outline badge-sm font-mono font-bold text-primary-600 border-primary-200 rounded-md">{{ $emp['branch_code'] }}</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="space-y-1.5">
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
                        class="input input-bordered w-full rounded-lg text-sm text-slate-600 bg-slate-50/80 {{ $isKaryawan ? 'cursor-not-allowed' : 'focus:bg-white focus:border-primary-500' }}"
                        {{ $isKaryawan ? 'readonly' : '' }}
                    />
                    @error('requestor_department') <span class="text-rose-600 text-xs block font-semibold mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Jabatan</label>
                    <input
                        wire:model="requestor_position"
                        type="text"
                        placeholder="Jabatan"
                        class="input input-bordered w-full rounded-lg text-sm text-slate-600 bg-slate-50/80 {{ $isKaryawan ? 'cursor-not-allowed' : 'focus:bg-white focus:border-primary-500' }}"
                        {{ $isKaryawan ? 'readonly' : '' }}
                    />
                    @error('requestor_position') <span class="text-rose-600 text-xs block font-semibold mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Email Pemohon</label>
                    <input
                        wire:model="requestor_email"
                        type="email"
                        placeholder="karyawan@selamatjayapersada.com"
                        class="input input-bordered w-full rounded-lg text-sm focus:border-primary-500 bg-slate-50/80 focus:bg-white"
                    />
                </div>

                <div class="space-y-1.5 sm:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">No. WhatsApp / Telepon</label>
                    <input
                        wire:model="requestor_phone"
                        type="text"
                        placeholder="08123456789"
                        class="input input-bordered w-full rounded-lg text-sm font-mono focus:border-primary-500 bg-slate-50/80 focus:bg-white"
                    />
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
                    <span>Terbitkan Nomor Surat Resmi</span>
                </span>
                <span wire:loading class="flex items-center justify-center gap-2">
                    <span class="loading loading-spinner loading-sm"></span>
                    <span>Memproses Registrasi...</span>
                </span>
            </button>
        </div>
    </form>

    <!-- Success Modal Popup -->
    <div class="modal {{ $showSuccessModal ? 'modal-open' : '' }} backdrop-blur-sm" role="dialog">
        <div class="modal-box max-w-lg rounded-3xl border border-slate-200/80 p-6 sm:p-8 text-center space-y-5 shadow-2xl bg-white">
            <!-- Icon -->
            <div class="w-16 h-16 rounded-2xl bg-primary-50 text-primary-600 border border-primary-200 flex items-center justify-center mx-auto shadow-2xs">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-9 h-9" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <div class="space-y-1">
                <span class="badge badge-success badge-soft font-bold text-emerald-800 uppercase tracking-wider px-3 py-1 rounded-md text-xs">Berhasil Diterbitkan</span>
                <h3 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 mt-1">Nomor Surat Siap Digunakan</h3>
                <p class="text-xs text-slate-500">Nomor registrasi telah tersimpan secara resmi di database.</p>
            </div>

            @if($createdLetter)
                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 space-y-3">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Nomor Registrasi Surat:</div>
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

                <div class="flex flex-col sm:flex-row gap-2.5 justify-center pt-2">
                    <button
                        type="button"
                        class="btn btn-primary text-white font-extrabold rounded-lg gap-2 flex-1 shadow-md shadow-primary-600/20 text-xs sm:text-sm"
                        @click="navigator.clipboard.writeText('{{ $createdLetter->reference_number }}'); copiedSuccess = true; setTimeout(() => copiedSuccess = false, 3000)"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <span x-text="copiedSuccess ? '✓ Nomor Tersalin!' : 'Salin Nomor Surat'">Salin Nomor Surat</span>
                    </button>

                    <a href="{{ route('letter.history') }}" class="btn btn-outline border-slate-200 rounded-lg font-bold flex-1 text-xs sm:text-sm hover:bg-slate-50 text-slate-700">
                        Lihat Riwayat
                    </a>
                </div>
            @endif

            <div class="modal-action justify-center pt-1 border-t border-slate-100">
                <button type="button" wire:click="createAnother" class="btn btn-ghost btn-sm text-xs font-semibold text-slate-500 hover:text-primary-600">
                    + Buat Nomor Surat Lainnya
                </button>
            </div>
        </div>
    </div>
</div>
