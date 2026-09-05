<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200 dark:border-slate-800">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                {{ $isAdmin ? 'Pengaturan Master Tujuan Surat' : 'Daftar Tujuan Surat Resmi' }}
            </h1>
        </div>

        @if($isAdmin)
            <div class="shrink-0">
                <button
                    type="button"
                    wire:click="openCreateModal"
                    class="btn btn-primary text-white font-bold text-xs sm:text-sm rounded-xl shadow-md shadow-primary-600/20 px-4 py-2.5 h-auto flex items-center gap-2 cursor-pointer transition-all hover:shadow-lg hover:shadow-primary-600/30"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Tujuan Baru</span>
                </button>
            </div>
        @endif
    </div>

    <!-- Rule Explanation Card -->
    <div class="bg-gradient-to-r from-primary-900 via-emerald-950 to-slate-900 text-white rounded-3xl p-5 sm:p-6 shadow-md border border-primary-500/20 relative overflow-hidden">
        <div class="absolute -right-12 -top-12 w-40 h-40 bg-primary-500/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="space-y-1.5 max-w-2xl">
                <div class="flex items-center gap-2">
                    <span class="badge badge-emerald badge-sm font-mono font-bold bg-emerald-500/20 text-emerald-300 border-emerald-500/30">Pedoman Format Nomor Surat</span>
                </div>
                <h3 class="text-sm sm:text-base font-extrabold text-white">
                    Format: <span class="text-emerald-300 font-mono">[No]/[Tujuan]/[Cabang]/[Bulan]/[Tahun]</span>
                </h3>
                <p class="text-xs text-slate-300 leading-relaxed">
                    • <strong>Tujuan Baku (Terdaftar):</strong> Kode disematkan ke nomor surat, misal: <span class="font-mono text-emerald-300 bg-white/10 px-1.5 py-0.5 rounded">001/IM/SJP/IX/2026</span><br>
                    • <strong>Di Luar Tujuan Baku:</strong> Otomatis menggunakan format ringkas tanpa kode tujuan, misal: <span class="font-mono text-emerald-300 bg-white/10 px-1.5 py-0.5 rounded">001/SJP/IX/2026</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <!-- Search Input -->
        <div class="relative flex-1 max-w-md">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none z-10 text-slate-400 dark:text-slate-500">
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
                wire:model.live.debounce.250ms="search"
                type="text"
                placeholder="Cari kode atau nama tujuan..."
                class="input input-bordered w-full rounded-xl text-xs sm:text-sm pl-10 pr-9 focus:border-primary-500 bg-slate-50/80 dark:bg-slate-800/80 focus:bg-white dark:focus:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all shadow-2xs"
            />
            @if(!empty($search))
                <button
                    type="button"
                    wire:click="$set('search', '')"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 cursor-pointer"
                    title="Hapus pencarian"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            @endif
        </div>

        <!-- Filter Status Buttons -->
        <div class="inline-flex rounded-xl p-1 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shrink-0 self-start sm:self-auto">
            <button
                type="button"
                wire:click="$set('statusFilter', 'all')"
                class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all cursor-pointer {{ $statusFilter === 'all' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-2xs' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}"
            >
                Semua ({{ $counts['all'] }})
            </button>
            <button
                type="button"
                wire:click="$set('statusFilter', 'active')"
                class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all cursor-pointer {{ $statusFilter === 'active' ? 'bg-white dark:bg-slate-700 text-emerald-700 dark:text-emerald-400 shadow-2xs' : 'text-slate-500 dark:text-slate-400 hover:text-emerald-700 dark:hover:text-emerald-400' }}"
            >
                Aktif ({{ $counts['active'] }})
            </button>
            <button
                type="button"
                wire:click="$set('statusFilter', 'inactive')"
                class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all cursor-pointer {{ $statusFilter === 'inactive' ? 'bg-white dark:bg-slate-700 text-rose-700 dark:text-rose-400 shadow-2xs' : 'text-slate-500 dark:text-slate-400 hover:text-rose-700 dark:hover:text-rose-400' }}"
            >
                Nonaktif ({{ $counts['inactive'] }})
            </button>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card bg-base-100 dark:bg-slate-900 shadow-xs border border-slate-200/80 dark:border-slate-800 rounded-3xl overflow-hidden relative">
        <!-- Async Table Loading Bar -->
        <div wire:loading wire:target="search, statusFilter, toggleActive, deleteTarget, gotoPage, nextPage, previousPage" class="h-1 w-full bg-gradient-to-r from-emerald-500 via-primary-500 to-teal-400 animate-pulse absolute top-0 left-0 right-0 z-20"></div>

        <div class="overflow-x-auto">
            <table class="table min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-xs sm:text-sm">
                <thead class="bg-slate-50/70 dark:bg-slate-800/70">
                    <tr class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="px-6 py-4 w-12 text-center">No</th>
                        <th class="px-6 py-4 text-left w-32">Kode Baku</th>
                        <th class="px-6 py-4 text-left">Nama Tujuan Surat</th>
                        <th class="px-6 py-4 text-left">Keterangan</th>
                        <th class="px-6 py-4 text-center w-28">Status</th>
                        @if($isAdmin)
                            <th class="px-6 py-4 text-center w-28">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody wire:loading.class="opacity-50" wire:target="search, statusFilter, toggleActive, deleteTarget, gotoPage, nextPage, previousPage" class="divide-y divide-slate-100 dark:divide-slate-800/80 transition-opacity duration-150">
                    @forelse ($targets as $index => $target)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors group" wire:key="target-{{ $target->id }}">
                            <td class="px-6 py-4 text-center text-slate-400 dark:text-slate-500 font-mono font-medium">
                                {{ $targets->firstItem() + $index }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="badge badge-primary badge-outline font-mono font-extrabold text-xs px-2.5 py-1 rounded-md">
                                    {{ $target->code }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm">
                                    {{ $target->name }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs text-slate-500 dark:text-slate-400 max-w-xs sm:max-w-md truncate">
                                    {{ $target->description ?: '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if($isAdmin)
                                    <!-- Admin Switch Toggle -->
                                    <div class="inline-flex items-center justify-center gap-2">
                                        <button
                                            type="button"
                                            role="switch"
                                            aria-checked="{{ $target->is_active ? 'true' : 'false' }}"
                                            wire:loading.attr="disabled"
                                            wire:target="toggleActive({{ $target->id }})"
                                            @click="
                                                if ({{ $target->is_active ? 'true' : 'false' }}) {
                                                    window.confirmAction({
                                                        title: 'Nonaktifkan Tujuan?',
                                                        text: 'Tujuan {{ addslashes($target->name) }} ({{ $target->code }}) tidak akan direkomendasikan pada formulir.',
                                                        icon: 'warning',
                                                        confirmButtonText: 'Ya, Nonaktifkan',
                                                        cancelButtonText: 'Batal'
                                                    }).then((res) => {
                                                        if (res.isConfirmed) {
                                                            $wire.toggleActive({{ $target->id }});
                                                        }
                                                    });
                                                } else {
                                                    $wire.toggleActive({{ $target->id }});
                                                }
                                            "
                                            class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none {{ $target->is_active ? 'bg-primary-600' : 'bg-slate-300 dark:bg-slate-700' }} disabled:opacity-50"
                                            title="Klik untuk {{ $target->is_active ? 'menonaktifkan' : 'mengaktifkan' }} status"
                                        >
                                            <span
                                                aria-hidden="true"
                                                class="pointer-events-none inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow-sm transition-transform duration-200 ease-in-out m-[3px] {{ $target->is_active ? 'translate-x-4' : 'translate-x-0' }}"
                                            ></span>
                                        </button>
                                        <span class="text-[11px] font-bold w-12 text-left select-none {{ $target->is_active ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500' }}">
                                            {{ $target->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>
                                @else
                                    <!-- Read-only Badge for Karyawan -->
                                    <span class="badge {{ $target->is_active ? 'badge-success badge-soft text-emerald-700 dark:text-emerald-300 dark:bg-emerald-950/40' : 'badge-ghost text-slate-400 dark:text-slate-500 dark:bg-slate-800' }} badge-sm font-semibold rounded-md">
                                        {{ $target->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                @endif
                            </td>
                            @if($isAdmin)
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1">
                                        <!-- Edit Button -->
                                        <button
                                            type="button"
                                            wire:click="openEditModal({{ $target->id }})"
                                            class="btn btn-ghost btn-sm btn-square rounded-md text-slate-400 dark:text-slate-500 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-950/40 transition-colors cursor-pointer"
                                            title="Edit Tujuan"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>

                                        <!-- Delete Button -->
                                        <button
                                            type="button"
                                            @click="
                                                window.confirmAction({
                                                    title: 'Hapus Tujuan Surat?',
                                                    text: 'Apakah Anda yakin ingin menghapus tujuan {{ addslashes($target->name) }} ({{ $target->code }})?',
                                                    icon: 'warning',
                                                    confirmButtonText: 'Ya, Hapus',
                                                    cancelButtonText: 'Batal',
                                                    confirmButtonColor: '#dc2626'
                                                }).then((res) => {
                                                    if (res.isConfirmed) {
                                                        $wire.deleteTarget({{ $target->id }});
                                                    }
                                                });
                                            "
                                            class="btn btn-ghost btn-sm btn-square rounded-md text-slate-400 dark:text-slate-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40 transition-colors cursor-pointer"
                                            title="Hapus Tujuan"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isAdmin ? 6 : 5 }}" class="text-center py-16 p-6">
                                <div class="w-12 h-12 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-slate-400 dark:text-slate-500 flex items-center justify-center text-xl mx-auto mb-3 shadow-2xs">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <p class="font-bold text-sm text-slate-900 dark:text-white">Tidak ada tujuan surat ditemukan</p>
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Coba ubah kata kunci pencarian atau filter status Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($targets->total() > 0)
            <x-pagination-footer :items="$targets" label="tujuan" />
        @endif
    </div>

    @if($isAdmin)
        <!-- ========================================== -->
        <!-- CREATE MODAL (Admin Only)                  -->
        <!-- ========================================== -->
        <div class="modal {{ $showCreateModal ? 'modal-open' : '' }} z-[100] backdrop-blur-sm bg-slate-900/40 dark:bg-slate-950/60" role="dialog">
            <div class="modal-box max-w-lg rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 sm:p-7 space-y-5 shadow-2xl bg-white dark:bg-slate-900 relative">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <h3 class="text-lg font-extrabold text-slate-900 dark:text-white">Tambah Tujuan Surat Baku</h3>
                    </div>
                    <button
                        type="button"
                        wire:click="closeCreateModal"
                        class="btn btn-ghost btn-sm btn-square rounded-md text-red-400 hover:text-red-600 dark:hover:text-red-300 hover:bg-red-100 dark:hover:bg-red-950/50 transition-colors cursor-pointer"
                        title="Tutup Modal"
                        aria-label="Tutup"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="createTarget" class="space-y-4">
                    <!-- Kode Tujuan -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            Kode Singkatan Baku <span class="text-rose-500">*</span>
                        </label>
                        <input
                            wire:model="code"
                            type="text"
                            placeholder="Contoh: KODE"
                            class="input input-bordered w-full rounded-xl text-sm font-mono uppercase focus:border-primary-500 bg-slate-50/80 dark:bg-slate-800/80 focus:bg-white dark:focus:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500"
                            required
                        />
                        <span class="text-[11px] text-slate-400 dark:text-slate-500 block">Kode ini akan muncul di nomor surat: [Nomor]/<strong>[KODE]</strong>/[Cabang]/[Bulan]/[Tahun]</span>
                        @error('code') <span class="text-rose-600 text-xs block font-semibold mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Nama Tujuan -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            Nama Tujuan / Instansi <span class="text-rose-500">*</span>
                        </label>
                        <input
                            wire:model="name"
                            type="text"
                            placeholder="Masukkan nama tujuan surat..."
                            class="input input-bordered w-full rounded-xl text-sm focus:border-primary-500 bg-slate-50/80 dark:bg-slate-800/80 focus:bg-white dark:focus:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500"
                            required
                        />
                        @error('name') <span class="text-rose-600 text-xs block font-semibold mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Keterangan -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            Keterangan / Peruntukan <span class="text-slate-400 dark:text-slate-500 font-normal lowercase">(opsional)</span>
                        </label>
                        <textarea
                            wire:model="description"
                            rows="2"
                            placeholder="Deskripsi singkat jenis surat atau instansi tujuan..."
                            class="textarea textarea-bordered w-full rounded-xl text-sm focus:border-primary-500 bg-slate-50/80 dark:bg-slate-800/80 focus:bg-white dark:focus:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500"
                        ></textarea>
                        @error('description') <span class="text-rose-600 text-xs block font-semibold mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Status Aktif -->
                    <div class="pt-1">
                        <label class="cursor-pointer label justify-start gap-3 py-0">
                            <input
                                wire:model="is_active"
                                type="checkbox"
                                class="toggle toggle-primary toggle-sm cursor-pointer"
                            />
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Aktifkan tujuan ini segera</span>
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button
                            type="button"
                            wire:click="closeCreateModal"
                            class="btn btn-ghost btn-sm rounded-md text-xs text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="btn btn-primary btn-sm rounded-md text-white font-bold text-xs px-4 cursor-pointer shadow-sm shadow-primary-600/20"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="createTarget">Simpan Tujuan</span>
                            <span wire:loading wire:target="createTarget" class="loading loading-spinner loading-xs"></span>
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-backdrop bg-transparent" wire:click="closeCreateModal">
                <button class="cursor-default">close</button>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- EDIT MODAL (Admin Only)                    -->
        <!-- ========================================== -->
        <div class="modal {{ $showEditModal ? 'modal-open' : '' }} z-[100] backdrop-blur-sm bg-slate-900/40 dark:bg-slate-950/60" role="dialog">
            <div class="modal-box max-w-lg rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 sm:p-7 space-y-5 shadow-2xl bg-white dark:bg-slate-900 relative">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <h3 class="text-lg font-extrabold text-slate-900 dark:text-white">Ubah Tujuan Surat Baku</h3>
                    </div>
                    <button
                        type="button"
                        wire:click="closeEditModal"
                        class="btn btn-ghost btn-sm btn-square rounded-md text-red-400 hover:text-red-600 dark:hover:text-red-300 hover:bg-red-100 dark:hover:bg-red-950/50 transition-colors cursor-pointer"
                        title="Tutup Modal"
                        aria-label="Tutup"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="updateTarget" class="space-y-4">
                    <!-- Kode Tujuan -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            Kode Singkatan Baku <span class="text-rose-500">*</span>
                        </label>
                        <input
                            wire:model="code"
                            type="text"
                            placeholder="Contoh: KODE"
                            class="input input-bordered w-full rounded-xl text-sm font-mono uppercase focus:border-primary-500 bg-slate-50/80 dark:bg-slate-800/80 focus:bg-white dark:focus:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500"
                            required
                        />
                        @error('code') <span class="text-rose-600 text-xs block font-semibold mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Nama Tujuan -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            Nama Tujuan / Instansi <span class="text-rose-500">*</span>
                        </label>
                        <input
                            wire:model="name"
                            type="text"
                            placeholder="Masukkan nama tujuan surat..."
                            class="input input-bordered w-full rounded-xl text-sm focus:border-primary-500 bg-slate-50/80 dark:bg-slate-800/80 focus:bg-white dark:focus:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500"
                            required
                        />
                        @error('name') <span class="text-rose-600 text-xs block font-semibold mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Keterangan -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            Keterangan / Peruntukan <span class="text-slate-400 dark:text-slate-500 font-normal lowercase">(opsional)</span>
                        </label>
                        <textarea
                            wire:model="description"
                            rows="2"
                            placeholder="Deskripsi singkat jenis surat atau instansi tujuan..."
                            class="textarea textarea-bordered w-full rounded-xl text-sm focus:border-primary-500 bg-slate-50/80 dark:bg-slate-800/80 focus:bg-white dark:focus:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500"
                        ></textarea>
                        @error('description') <span class="text-rose-600 text-xs block font-semibold mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Status Aktif -->
                    <div class="pt-1">
                        <label class="cursor-pointer label justify-start gap-3 py-0">
                            <input
                                wire:model="is_active"
                                type="checkbox"
                                class="toggle toggle-primary toggle-sm cursor-pointer"
                            />
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Status Aktif</span>
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button
                            type="button"
                            wire:click="closeEditModal"
                            class="btn btn-ghost btn-sm rounded-md text-xs text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="btn btn-primary btn-sm rounded-md text-white font-bold text-xs px-4 cursor-pointer shadow-sm shadow-primary-600/20"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="updateTarget">Perbarui Tujuan</span>
                            <span wire:loading wire:target="updateTarget" class="loading loading-spinner loading-xs"></span>
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-backdrop bg-transparent" wire:click="closeEditModal">
                <button class="cursor-default">close</button>
            </div>
        </div>
    @endif
</div>
