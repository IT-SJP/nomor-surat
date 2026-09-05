<div class="space-y-6">
    <!-- Header & Action Row -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-200 dark:border-slate-800">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                {{ $isKaryawan ? "Riwayat Nomor Surat ({$userBranch})" : 'Riwayat Seluruh Nomor Surat' }}
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                @if($isAdminCabang)
                    Daftar riwayat nomor surat resmi khusus cabang {{ $adminBranchCode ? "{$adminBranchName}" : $adminBranchName }}.
                @elseif($isKaryawan)
                    Daftar riwayat nomor surat yang telah diterbitkan untuk cabang {{ $userBranchName }}.
                @else
                    Kelola dan pantau seluruh riwayat nomor surat yang telah diterbitkan pada holding SJP.
                @endif
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto shrink-0">
            @if($isAdmin)
                <button
                    type="button"
                    wire:click="openImportModal"
                    class="btn btn-outline btn-md rounded-lg gap-2 font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-primary-600 dark:hover:text-primary-400 border-slate-200 dark:border-slate-700 text-xs sm:text-sm cursor-pointer flex-1 sm:flex-initial justify-center px-2.5 sm:px-4"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span>Import CSV</span>
                </button>
            @endif

            <button
                type="button"
                wire:click="exportCsv"
                @click="window.showToast('info', 'Sedang memproses dan mengunduh berkas CSV...', 'Download CSV')"
                class="btn btn-primary btn-md rounded-lg text-white font-bold gap-2 shadow-md shadow-primary-600/20 text-xs sm:text-sm flex-1 sm:flex-initial justify-center px-2.5 sm:px-4 whitespace-nowrap cursor-pointer"
            >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Download CSV</span>
            </button>
        </div>
    </div>

    <!-- Filter & Search Card (Collapsible Accordion on Mobile) -->
    <div 
        x-data="{ 
            isOpen: {{ ($date || ($isAdmin && ! $isAdminCabang && $branch)) ? 'true' : 'false' }},
            activeCount: 0,
            updateCount() {
                let count = 0;
                if ($wire.date) count++;
                if ({{ ($isAdmin && ! $isAdminCabang) ? 'true' : 'false' }} && $wire.branch) count++;
                this.activeCount = count;
            }
        }"
        x-init="updateCount(); $watch('$wire.date', () => updateCount()); $watch('$wire.branch', () => updateCount())"
        class="card bg-base-100 dark:bg-slate-900 shadow-xs border border-slate-200/80 dark:border-slate-800 rounded-2xl sm:rounded-3xl p-4 sm:p-6 space-y-3 sm:space-y-4 transition-all"
    >
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-3.5 sm:gap-4 items-end">
            <!-- Real-time Search Input (Always visible on mobile & desktop) -->
            <div class="order-1 lg:order-3 lg:col-span-5 space-y-1">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Cari Nomor / Pemohon
                    </label>
                    <!-- Mobile Filter Accordion Toggle Button -->
                    <button 
                        type="button" 
                        @click="isOpen = !isOpen" 
                        class="lg:hidden inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold transition-colors cursor-pointer mb-1"
                        :class="isOpen || activeCount > 0 ? 'bg-primary-50 dark:bg-primary-950/60 text-primary-600 dark:text-primary-400 border border-primary-200 dark:border-primary-800/60' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 border border-transparent'"
                        title="Buka/Tutup Filter Tambahan"
                    >
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <span>Filter</span>
                        <span x-show="activeCount > 0" x-cloak class="badge badge-primary badge-xs font-bold text-white rounded-full px-1" x-text="activeCount"></span>
                        <svg class="w-3 h-3 shrink-0 transition-transform duration-200" :class="isOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </div>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none z-10 text-slate-400">
                        <span wire:loading.remove wire:target="search">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <span wire:loading wire:target="search">
                            <span class="loading loading-spinner loading-xs text-primary-600 dark:text-primary-400"></span>
                        </span>
                    </span>
                    <input
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        placeholder="Ketik nomor surat, perihal, atau nama pemohon..."
                        class="input input-bordered w-full pl-10 rounded-lg text-sm focus:border-primary-500 bg-slate-50/80 dark:bg-slate-800/80 focus:bg-white dark:focus:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500"
                    />
                </div>
            </div>

            <!-- Date Filter (Flatpickr) -->
            <div 
                :class="isOpen ? 'block' : 'hidden lg:block'" 
                class="order-2 lg:order-1 lg:col-span-4 space-y-1 transition-all"
            >
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Tanggal Surat</label>
                <div 
                    wire:ignore
                    x-data="{
                        picker: null,
                        init() {
                            this.picker = flatpickr(this.$refs.dateInput, {
                                locale: window.flatpickrIndonesian || 'id',
                                dateFormat: 'Y-m-d',
                                altInput: true,
                                altFormat: 'd F Y',
                                allowInput: false,
                                disableMobile: true,
                                defaultDate: @this.date || null,
                                onChange: (selectedDates, dateStr) => {
                                    @this.set('date', dateStr);
                                }
                            });

                            this.$watch('$wire.date', (value) => {
                                if (!value && this.picker) {
                                    this.picker.clear(false);
                                } else if (value && this.picker && this.picker.input.value !== value) {
                                    this.picker.setDate(value, false);
                                }
                            });
                        },
                        clear() {
                            if (this.picker) {
                                this.picker.clear();
                            }
                            @this.set('date', '');
                        }
                    }"
                    class="relative"
                >
                    <input
                        x-ref="dateInput"
                        type="text"
                        placeholder="Pilih Tanggal Surat..."
                        class="input input-bordered w-full rounded-lg text-sm text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 focus:border-primary-500 cursor-pointer pr-9"
                    />
                    <button 
                        type="button" 
                        x-show="$wire.date" 
                        x-cloak 
                        @click.stop="clear()" 
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-red-500 z-10 cursor-pointer transition-colors"
                        title="Hapus Tanggal"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Branch Filter -->
            <div 
                :class="isOpen ? 'block' : 'hidden lg:block'" 
                class="order-3 lg:order-2 lg:col-span-3 space-y-1 transition-all"
            >
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Cabang</label>
                @if($isKaryawan || $isAdminCabang)
                    <div class="input input-bordered w-full flex items-center justify-between bg-slate-50 dark:bg-slate-800/60 text-xs font-bold text-primary-600 dark:text-primary-400 rounded-lg cursor-not-allowed border-slate-200 dark:border-slate-700">
                        <span class="truncate">{{ $isAdminCabang ? ($adminBranchCode ? "{$adminBranchCode} — {$adminBranchName}" : $adminBranchName) : "{$userBranch} — {$userBranchName}" }}</span>
                    </div>
                @else
                    <select wire:model.live="branch" class="select select-bordered w-full rounded-lg text-sm text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 focus:border-primary-500">
                        <option value="">Semua Cabang SJP</option>
                        @foreach($branches as $b)
                            <option value="{{ $b['code'] }}">{{ $b['code'] }} &mdash; {{ $b['name'] }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
        </div>

        @if($search || ($isAdmin && ! $isAdminCabang && $branch) || $date)
            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                    <span class="badge badge-primary badge-xs rounded-full"></span>
                    <span>Filter pencarian aktif</span>
                </div>
                <button type="button" wire:click="resetFilters" class="btn btn-ghost btn-xs text-red-600 dark:text-red-400 font-bold hover:bg-red-50 dark:hover:bg-red-950/40">
                    ✕ Reset Filter
                </button>
            </div>
        @endif
    </div>

    <!-- Letter Table Card -->
    <div class="card bg-base-100 dark:bg-slate-900 shadow-xs border border-slate-200/80 dark:border-slate-800 rounded-3xl overflow-hidden relative">
        <!-- Async Table Loading Shimmer Bar -->
        <div wire:loading wire:target="search, branch, date, gotoPage, nextPage, previousPage" class="h-1 w-full bg-gradient-to-r from-emerald-500 via-primary-500 to-teal-400 animate-pulse absolute top-0 left-0 right-0 z-20"></div>

        <div class="overflow-x-auto">
            <table class="table min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-xs sm:text-sm">
                <thead class="bg-slate-50/70 dark:bg-slate-800/70">
                    <tr class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="px-6 py-4 w-12 text-center">No</th>
                        <th class="px-6 py-4 text-left">Nomor Surat Resmi</th>
                        <th class="px-6 py-4 text-left">Cabang</th>
                        <th class="px-6 py-4 text-left">Perihal / Keperluan</th>
                        <th class="px-6 py-4 text-left">Penerima / Instansi</th>
                        <th class="px-6 py-4 text-left">Pemohon</th>
                        <th class="px-6 py-4 w-16 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody wire:loading.class="opacity-40 pointer-events-none" wire:target="search, branch, date, gotoPage, nextPage, previousPage" class="divide-y divide-slate-100 dark:divide-slate-800/80 transition-opacity duration-150">
                    @forelse($letters as $letter)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors group">
                            <td class="px-6 py-4 text-center font-mono text-slate-400 dark:text-slate-500 font-semibold">
                                {{ $loop->iteration + ($letters->currentPage() - 1) * $letters->perPage() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button
                                    type="button"
                                    class="font-mono font-bold text-primary-600 dark:text-primary-400 text-xs sm:text-sm tracking-wide select-all text-left hover:underline cursor-pointer flex items-center gap-1.5 group/copy"
                                    title="Klik untuk menyalin nomor surat"
                                    @click="window.copyToClipboard('{{ $letter->reference_number }}', 'Nomor Surat')"
                                >
                                    <span>{{ $letter->reference_number }}</span>
                                    <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 group-hover/copy:text-primary-600 dark:group-hover/copy:text-primary-400 opacity-0 group-hover/copy:opacity-100 transition-opacity shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium font-mono block">
                                    {{ $letter->created_at->format('d/m/Y • H:i') }} WIB
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="badge badge-outline badge-sm font-mono font-bold text-primary-600 dark:text-primary-400 border-primary-200 dark:border-primary-800/60 rounded-md">
                                    {{ $letter->branch_code }}
                                </span>
                            </td>
                            <td class="px-6 py-4 max-w-xs">
                                <p class="font-bold text-slate-900 dark:text-white line-clamp-1 text-xs sm:text-sm">{{ $letter->subject }}</p>
                                @if($letter->purpose)
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-1 mt-0.5">{{ $letter->purpose }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $letter->target_code }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="font-bold text-slate-900 dark:text-white leading-tight text-xs sm:text-sm">{{ $letter->requestor_name }}</p>
                                @if($letter->requestor_department || $letter->requestor_position)
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $letter->requestor_department }} • {{ $letter->requestor_position }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <button
                                    type="button"
                                    wire:click="viewLetter({{ $letter->id }})"
                                    class="btn btn-square btn-primary btn-soft dark:bg-primary-950/50 dark:text-primary-300 dark:hover:bg-primary-900/60 btn-sm rounded-md"
                                    title="Lihat Detail Surat"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-16 p-6">
                                <div class="w-12 h-12 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-slate-400 dark:text-slate-500 flex items-center justify-center text-xl mx-auto mb-3 shadow-2xs">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-inbox-off"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M8 4h10a2 2 0 0 1 2 2v10m-.593 3.422a2 2 0 0 1 -1.407 .578h-12a2 2 0 0 1 -2 -2v-12c0 -.554 .225 -1.056 .59 -1.418" /><path d="M4 13h3l3 3h4l.987 -.987m2.013 -2.013h3" /><path d="M3 3l18 18" /></svg>
                                </div>
                                <p class="font-bold text-sm text-slate-900 dark:text-white">Belum ada nomor surat yang diterbitkan</p>
                                <a href="{{ route('letter.request') }}" wire:navigate class="btn btn-primary btn-sm text-white font-bold rounded-lg mt-4 shadow-md shadow-primary-600/20">
                                    + Buat Nomor Surat
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($letters->total() > 0)
            <x-pagination-footer :items="$letters" label="surat" />
        @endif
    </div>

    <!-- Detail Modal -->
    <div class="modal {{ $showDetailModal ? 'modal-open' : '' }} z-[100] backdrop-blur-md bg-slate-900/40 dark:bg-slate-950/60" role="dialog">
        <div class="modal-box max-w-lg rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 sm:p-7 space-y-5 shadow-2xl bg-white dark:bg-slate-900" x-data="{ copiedDetail: false }">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <h3 class="font-extrabold text-base sm:text-lg text-slate-900 dark:text-white">Detail Nomor Surat</h3>
                </div>
                <button type="button" wire:click="closeDetailModal" class="btn btn-ghost btn-sm btn-square rounded-md text-red-400 hover:text-red-600 dark:hover:text-red-300 hover:bg-red-100 dark:hover:bg-red-950/50 transition-colors" title="Tutup Modal" aria-label="Tutup">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            @if($selectedLetter)
                <div class="bg-slate-50 dark:bg-slate-800/60 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Nomor Surat:</span>
                        <button
                            type="button"
                            class="btn btn-primary btn-xs text-white font-bold rounded-md shadow-2xs cursor-pointer"
                            @click="window.copyToClipboard('{{ $selectedLetter->reference_number }}', 'Nomor Surat'); copiedDetail = true; setTimeout(() => copiedDetail = false, 2500)"
                        >
                            <span x-text="copiedDetail ? '✓ Tersalin' : 'Salin Nomor'">Salin Nomor</span>
                        </button>
                    </div>
                    <div class="font-mono font-black text-xl sm:text-2xl text-primary-600 dark:text-primary-400 break-all select-all">
                        {{ $selectedLetter->reference_number }}
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="bg-slate-50/60 dark:bg-slate-800/40 p-3 rounded-2xl border border-slate-200/80 dark:border-slate-800">
                        <p class="text-slate-400 dark:text-slate-500 font-semibold text-[10px] uppercase">Cabang / Entitas</p>
                        <p class="font-bold mt-0.5 text-slate-900 dark:text-white">{{ $selectedLetter->branch_name ?? 'SJP Group' }}</p>
                    </div>
                    <div class="bg-slate-50/60 dark:bg-slate-800/40 p-3 rounded-2xl border border-slate-200/80 dark:border-slate-800">
                        <p class="text-slate-400 dark:text-slate-500 font-semibold text-[10px] uppercase">Tujuan / Instansi</p>
                        <p class="font-bold mt-0.5 text-slate-900 dark:text-white">{{ $selectedLetter->target_code }}</p>
                    </div>
                    <div class="bg-slate-50/60 dark:bg-slate-800/40 p-3 rounded-2xl border border-slate-200/80 dark:border-slate-800 sm:col-span-2">
                        <p class="text-slate-400 dark:text-slate-500 font-semibold text-[10px] uppercase">Perihal Surat</p>
                        <p class="font-bold mt-0.5 text-sm text-slate-900 dark:text-white">{{ $selectedLetter->subject }}</p>
                    </div>
                    <div class="bg-slate-50/60 dark:bg-slate-800/40 p-3 rounded-2xl border border-slate-200/80 dark:border-slate-800 sm:col-span-2">
                        <p class="text-slate-400 dark:text-slate-500 font-semibold text-[10px] uppercase">Keperluan / Keterangan</p>
                        <p class="mt-0.5 text-slate-700 dark:text-slate-300 whitespace-pre-wrap leading-relaxed">{{ $selectedLetter->purpose ?: '-' }}</p>
                    </div>
                    <div class="bg-slate-50/60 dark:bg-slate-800/40 p-3 rounded-2xl border border-slate-200/80 dark:border-slate-800">
                        <p class="text-slate-400 dark:text-slate-500 font-semibold text-[10px] uppercase">Pemohon (Karyawan)</p>
                        <p class="font-bold mt-0.5 text-slate-900 dark:text-white">{{ $selectedLetter->requestor_name }}</p>
                        @if($selectedLetter->requestor_department)
                            <p class="text-[10px] text-slate-500 dark:text-slate-400">{{ $selectedLetter->requestor_department }}</p>
                        @endif
                    </div>
                    <div class="bg-slate-50/60 dark:bg-slate-800/40 p-3 rounded-2xl border border-slate-200/80 dark:border-slate-800">
                        <p class="text-slate-400 dark:text-slate-500 font-semibold text-[10px] uppercase">Waktu Diterbitkan</p>
                        <p class="font-bold mt-0.5 text-slate-900 dark:text-white font-mono">{{ $selectedLetter->created_at->translatedFormat('d F Y, H:i') }} WIB</p>
                    </div>
                </div>
            @endif
        </div>
        <div class="modal-backdrop bg-transparent" wire:click="closeDetailModal">
            <button class="cursor-default">close</button>
        </div>
    </div>

    {{-- Modal Import CSV (Admin Only) --}}
    @if($isAdmin)
        <div class="modal {{ $showImportModal ? 'modal-open' : '' }} backdrop-blur-xs bg-slate-900/40" role="dialog">
            <div class="modal-box bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 max-w-lg shadow-2xl transition-all">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2.5">
                        <div>
                            <h3 class="text-lg font-extrabold text-base text-slate-900 dark:text-white">Import Riwayat Nomor Surat</h3>
                        </div>
                    </div>
                    <button type="button" wire:click="closeImportModal" class="btn btn-ghost btn-sm btn-square rounded-md text-red-400 hover:text-red-600 dark:hover:text-red-300 hover:bg-red-100 dark:hover:bg-red-950/50 transition-colors" title="Tutup Modal" aria-label="Tutup">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="importCsv" class="mt-4 space-y-4">
                    <!-- Info Box Format -->
                    <div class="bg-primary-50/60 dark:bg-primary-950/30 border border-primary-100 dark:border-primary-900/50 rounded-2xl p-3.5 text-xs text-slate-600 dark:text-slate-300 space-y-1.5">
                        @if($isAdminCabang)
                            <div class="flex items-center gap-1.5 text-primary-800 dark:text-primary-300 font-bold mb-1">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span>Import Khusus Cabang: {{ $adminBranchCode ? "{$adminBranchName}" : $adminBranchName }}</span>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                Sebagai Admin Cabang, nomor surat yang diimpor akan otomatis ditetapkan ke cabang <strong>{{ $adminBranchName }}</strong>. Baris data untuk cabang lain akan ditolak demi keamanan data.
                            </p>
                        @endif
                        <p class="font-bold text-primary-800 dark:text-primary-300 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Pedoman Kolom Header CSV:
                        </p>
                        <p class="text-[11px] font-mono leading-relaxed text-slate-500 dark:text-slate-400 bg-white/70 dark:bg-slate-900/70 p-2 rounded-xl border border-primary-200/50 dark:border-primary-800/40 select-all">
                            No, Timestamp, Nomor Surat, Kode Perusahaan, Kode Tujuan, Bulan, Tahun, Perihal, Tujuan, Letak Arsip, Requestor
                        </p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                            Nomor surat akan digenerate otomatis dengan format: <p><strong class="text-primary-700 dark:text-primary-300">[No]/[Tujuan]/[Cabang]/[Bulan]/[Tahun]</strong></p>
                        </p>
                    </div>

                    <!-- File Input Area -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                            Pilih File CSV (.csv, .txt, maks. 10MB)
                        </label>
                        <input 
                            type="file" 
                            wire:model="csvFile" 
                            accept=".csv,.txt"
                            class="file-input file-input-bordered file-input-primary w-full rounded-xl bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-xs"
                        />
                        @error('csvFile')
                            <p class="text-xs font-semibold text-red-500 mt-1">{{ $message }}</p>
                        @enderror

                        <div wire:loading wire:target="csvFile" class="text-xs text-primary-600 dark:text-primary-400 flex items-center gap-2 pt-1">
                            <span class="loading loading-spinner loading-xs"></span>
                            <span>Mengunggah berkas ke server...</span>
                        </div>
                    </div>

                    <!-- Import Result Summary (if any) -->
                    @if(!empty($importResult))
                        <div class="p-3.5 rounded-2xl {{ ($importResult['success'] ?? false) ? 'bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-emerald-900 dark:text-emerald-200' : 'bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 text-amber-900 dark:text-amber-200' }} text-xs space-y-1">
                            <p class="font-bold flex items-center gap-1.5">
                                @if($importResult['success'] ?? false)
                                    <span>✓ Berhasil mengimpor {{ $importResult['imported_count'] }} nomor surat!</span>
                                @else
                                    <span>Perhatian: {{ $importResult['imported_count'] }} diimpor, {{ $importResult['skipped_count'] }} dilewati.</span>
                                @endif
                            </p>
                            @if(!empty($importResult['errors']))
                                <div class="max-h-28 overflow-y-auto text-[11px] text-red-600 dark:text-red-400 mt-1 pl-2 border-l-2 border-red-300">
                                    @foreach(array_slice($importResult['errors'], 0, 5) as $err)
                                        <p>{{ $err }}</p>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Modal Actions -->
                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button 
                            type="button" 
                            wire:click="closeImportModal" 
                            class="btn btn-ghost btn-sm rounded-md text-xs font-bold text-slate-500 dark:text-slate-400"
                        >
                            Tutup
                        </button>
                        <button 
                            type="submit" 
                            wire:loading.attr="disabled"
                            wire:target="importCsv, csvFile"
                            class="btn btn-primary btn-sm rounded-md text-white text-xs font-bold shadow-md shadow-primary-600/20 px-4 flex items-center gap-1.5"
                        >
                            <span wire:loading.remove wire:target="importCsv">Mulai Import</span>
                            <span wire:loading wire:target="importCsv" class="flex items-center gap-1.5">
                                <span class="loading loading-spinner loading-xs"></span>
                                <span>Memproses data...</span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-backdrop bg-transparent" wire:click="closeImportModal">
                <button class="cursor-default">close</button>
            </div>
        </div>
    @endif
</div>
