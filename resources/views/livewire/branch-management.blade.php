<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200 dark:border-slate-800">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Pengaturan Cabang & Kode Surat</h1>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                {{ $isAdminCabang ? 'Kelola data dan kode surat resmi untuk entitas cabang.' : 'Daftar data cabang dan kode surat resmi PT Selamat Jaya Persada Holding.' }}
            </p>
        </div>
    </div>

    <!-- Branch Table Card -->
    <div class="card bg-base-100 dark:bg-slate-900 shadow-xs border border-slate-200/80 dark:border-slate-800 rounded-3xl overflow-hidden relative">
        <!-- Async Table Loading Bar -->
        <div wire:loading wire:target="toggleActive, updateBranchCode, deleteBranch, gotoPage, nextPage, previousPage" class="h-1 w-full bg-gradient-to-r from-emerald-500 via-primary-500 to-teal-400 animate-pulse absolute top-0 left-0 right-0 z-20"></div>

        <div class="overflow-x-auto">
            <table class="table min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-xs sm:text-sm">
                <thead class="bg-slate-50/70 dark:bg-slate-800/70">
                    <tr class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="px-6 py-4 w-12 text-center">No</th>
                        <th class="px-6 py-4 text-left">Nama Entitas / Cabang</th>
                        <th class="px-6 py-4 text-left">Kode Cabang</th>
                        <th class="px-6 py-4 text-left">Kode Surat Resmi</th>
                        <th class="px-6 py-4 text-left">Status Cabang</th>
                        @if($canManageBranches)
                            <th class="px-6 py-4 text-center w-24">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody wire:loading.class="opacity-50" wire:target="toggleActive, updateBranchCode, deleteBranch, gotoPage, nextPage, previousPage" class="divide-y divide-slate-100 dark:divide-slate-800/80 transition-opacity duration-150">
                    @forelse ($branches as $index => $branch)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors group" wire:key="branch-{{ $branch->id }}">
                            <td class="px-6 py-4 text-center text-slate-400 dark:text-slate-500 font-mono font-medium">
                                {{ $branches->firstItem() + $index }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm">{{ $branch->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="badge badge-ghost dark:bg-slate-800 font-mono text-xs rounded-md text-slate-600 dark:text-slate-300">{{ $branch->hr_code }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($canManageBranches)
                                    <div x-data="{ 
                                        isEditing: false, 
                                        code: '{{ $branch->branch_code }}',
                                        save() {
                                            if(this.code.trim() !== '' && this.code.trim() !== '{{ $branch->branch_code }}') {
                                                $wire.updateBranchCode({{ $branch->id }}, this.code.trim());
                                            }
                                            this.isEditing = false;
                                        }
                                    }" class="inline-flex items-center gap-2">
                                        
                                        <div x-show="!isEditing" @click="isEditing = true; $nextTick(() => $refs.input.focus())" class="cursor-pointer inline-flex items-center gap-1.5 py-1 px-2.5 rounded-md hover:bg-slate-100 dark:hover:bg-slate-800 border {{ empty($branch->branch_code) ? 'border-dashed border-amber-300 dark:border-amber-700 bg-amber-50/50 dark:bg-amber-950/30' : 'border-transparent hover:border-slate-200 dark:hover:border-slate-700' }} transition-all group">
                                            @if(!empty($branch->branch_code))
                                                <span class="font-mono font-bold text-primary-600 dark:text-primary-400 text-xs sm:text-sm">{{ $branch->branch_code }}</span>
                                            @else
                                                <span class="text-xs font-semibold text-amber-600 dark:text-amber-400 italic flex items-center gap-1">
                                                    <span>Belum diset</span>
                                                    <span class="text-[10px] text-amber-500 dark:text-amber-400">(Klik untuk atur)</span>
                                                </span>
                                            @endif
                                            <button type="button" class="btn btn-ghost btn-xs btn-square text-slate-400 dark:text-slate-500 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors" title="Edit Kode">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </button>
                                        </div>

                                        <div x-show="isEditing" class="flex items-center gap-1" style="display: none;">
                                            <input 
                                                x-ref="input"
                                                x-model="code"
                                                @keydown.enter="save()"
                                                @keydown.escape="isEditing = false; code = '{{ $branch->branch_code }}'"
                                                @blur="save()"
                                                type="text" 
                                                class="input input-xs input-bordered w-28 font-mono font-bold text-primary-600 dark:text-primary-400 rounded-sm focus:border-primary-500 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 mr-2"
                                            />
                                            <button type="button" @click="save()" class="btn btn-xs btn-primary btn-square rounded-sm text-white font-bold cursor-pointer" title="Simpan">
                                                ✓
                                            </button>
                                        </div>
                                    </div>
                                    @error('code_'.$branch->id)
                                        <span class="text-red-600 text-[10px] block mt-0.5 font-semibold">{{ $message }}</span>
                                    @enderror
                                @else
                                    <div class="inline-flex items-center gap-1.5 py-1">
                                        @if(!empty($branch->branch_code))
                                            <span class="font-mono font-bold text-primary-600 dark:text-primary-400 text-xs sm:text-sm">{{ $branch->branch_code }}</span>
                                        @else
                                            <span class="text-xs font-medium text-slate-400 dark:text-slate-500 italic">Belum diset</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($canManageBranches)
                                    <div class="inline-flex items-center justify-start gap-2">
                                        <button
                                            type="button"
                                            role="switch"
                                            aria-checked="{{ $branch->is_active ? 'true' : 'false' }}"
                                            wire:loading.attr="disabled"
                                            wire:target="toggleActive({{ $branch->id }})"
                                            @click="
                                                if ({{ $branch->is_active ? 'true' : 'false' }}) {
                                                    window.confirmAction({
                                                        title: 'Nonaktifkan Cabang?',
                                                        text: 'Cabang {{ addslashes($branch->name) }} tidak akan tersedia dalam pemilihan nomor surat.',
                                                        icon: 'warning',
                                                        confirmButtonText: 'Ya, Nonaktifkan',
                                                        cancelButtonText: 'Batal'
                                                    }).then((res) => {
                                                        if (res.isConfirmed) {
                                                            $wire.toggleActive({{ $branch->id }});
                                                        }
                                                    });
                                                } else {
                                                    $wire.toggleActive({{ $branch->id }});
                                                }
                                            "
                                            class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none {{ $branch->is_active ? 'bg-primary-600' : 'bg-slate-300 dark:bg-slate-700' }} disabled:opacity-50"
                                            title="Klik untuk {{ $branch->is_active ? 'menonaktifkan' : 'mengaktifkan' }} status"
                                        >
                                            <span
                                                aria-hidden="true"
                                                class="pointer-events-none inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow-sm transition-transform duration-200 ease-in-out m-[3px] {{ $branch->is_active ? 'translate-x-4' : 'translate-x-0' }}"
                                            ></span>
                                        </button>
                                        <span class="text-[11px] font-bold w-12 text-left select-none {{ $branch->is_active ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400 dark:text-slate-500' }}">
                                            {{ $branch->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold {{ $branch->is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $branch->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        {{ $branch->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                @endif
                            </td>
                            @if($canManageBranches)
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button type="button" 
                                        @click.prevent="
                                            window.confirmAction({
                                                title: 'Hapus Cabang?',
                                                text: 'Hapus cabang {{ addslashes($branch->name) }} dari sistem nomor surat?',
                                                icon: 'warning',
                                                confirmButtonText: 'Ya, Hapus',
                                                cancelButtonText: 'Batal',
                                                confirmButtonColor: '#dc2626'
                                            }).then((res) => {
                                                if (res.isConfirmed) {
                                                    $wire.deleteBranch({{ $branch->id }});
                                                }
                                            });
                                        "
                                        class="btn btn-ghost btn-sm btn-square rounded-md text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40 transition-colors"
                                        title="Hapus Cabang dari Nomor Surat">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canManageBranches ? 6 : 5 }}" class="text-center py-16 p-6">
                                <div class="w-12 h-12 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-slate-400 dark:text-slate-500 flex items-center justify-center text-xl mx-auto mb-3 shadow-2xs">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-inbox-off"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M8 4h10a2 2 0 0 1 2 2v10m-.593 3.422a2 2 0 0 1 -1.407 .578h-12a2 2 0 0 1 -2 -2v-12c0 -.554 .225 -1.056 .59 -1.418" /><path d="M4 13h3l3 3h4l.987 -.987m2.013 -2.013h3" /><path d="M3 3l18 18" /></svg>
                                </div>
                                <p class="font-bold text-sm text-slate-900 dark:text-white">Belum ada data cabang</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($branches->total() > 0)
            <x-pagination-footer :items="$branches" label="cabang" />
        @endif
    </div>
</div>
