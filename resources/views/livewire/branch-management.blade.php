<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Pengaturan Cabang & Kode Surat</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola data cabang yang tersinkron dari Absenku dan sesuaikan kode surat resmi masing-masing.</p>
        </div>
    </div>

    <!-- Branch Table Card -->
    <div class="card bg-base-100 shadow-xs border border-slate-200/80 rounded-3xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table min-w-full divide-y divide-slate-200 text-xs sm:text-sm">
                <thead class="bg-slate-50/70">
                    <tr class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-4 w-12 text-center">No</th>
                        <th class="px-6 py-4 text-left">Nama Entitas / Cabang</th>
                        <th class="px-6 py-4 text-left">Kode Asli (HRIS)</th>
                        <th class="px-6 py-4 text-left">Kode Surat Resmi</th>
                        <th class="px-6 py-4 text-left">Status Surat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($branches as $index => $branch)
                        <tr class="hover:bg-slate-50/80 transition-colors group" wire:key="branch-{{ $branch->id }}">
                            <td class="px-6 py-4 text-center text-slate-400 font-mono font-medium">
                                {{ $branches->firstItem() + $index }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900 text-xs sm:text-sm">{{ $branch->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="badge badge-ghost font-mono text-xs rounded-md text-slate-600">{{ $branch->hr_code }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div x-data="{ 
                                    isEditing: false, 
                                    code: '{{ $branch->branch_code }}',
                                    save() {
                                        if(this.code.trim() !== '' && this.code !== '{{ $branch->branch_code }}') {
                                            $wire.updateBranchCode({{ $branch->id }}, this.code);
                                        }
                                        this.isEditing = false;
                                    }
                                }" class="inline-flex items-center gap-2">
                                    
                                    <div x-show="!isEditing" @click="isEditing = true; $nextTick(() => $refs.input.focus())" class="cursor-pointer inline-flex items-center gap-1.5 py-1 px-2.5 rounded-lg hover:bg-slate-100 border {{ empty($branch->branch_code) ? 'border-dashed border-amber-300 bg-amber-50/50' : 'border-transparent hover:border-slate-200' }} transition-all group">
                                        @if(!empty($branch->branch_code))
                                            <span class="font-mono font-bold text-primary-600 text-xs sm:text-sm">{{ $branch->branch_code }}</span>
                                        @else
                                            <span class="text-xs font-semibold text-amber-600 italic flex items-center gap-1">
                                                <span>Belum diset</span>
                                                <span class="text-[10px] text-amber-500">(Klik untuk atur)</span>
                                            </span>
                                        @endif
                                        <button type="button" class="btn btn-ghost btn-xs btn-square text-slate-400 group-hover:text-primary-600 transition-colors" title="Edit Kode">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0" style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                                            class="input input-xs input-bordered w-28 font-mono font-bold text-primary-600 rounded-lg focus:border-primary-500 bg-white"
                                        />
                                        <button type="button" @click="save()" class="btn btn-xs btn-primary btn-square rounded-lg text-white font-bold" title="Simpan">
                                            ✓
                                        </button>
                                    </div>
                                    
                                </div>
                                @error('code')
                                    <span class="text-rose-600 text-[10px] block mt-0.5 font-semibold">{{ $message }}</span>
                                @enderror
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <label class="cursor-pointer label justify-start gap-2.5 py-0">
                                    <input type="checkbox" class="toggle toggle-primary toggle-sm" 
                                        wire:change="toggleActive({{ $branch->id }})" 
                                        {{ $branch->is_active ? 'checked' : '' }} />
                                    <span class="label-text text-xs font-bold {{ $branch->is_active ? 'text-primary-600' : 'text-slate-400' }}">
                                        {{ $branch->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </label>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-16 text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-200 text-slate-400 flex items-center justify-center text-xl mb-2 shadow-2xs">
                                        🏢
                                    </div>
                                    <p class="font-bold text-sm text-slate-900">Belum ada data cabang</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Data cabang akan otomatis tersinkron dari Absenku SJP.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($branches->hasPages())
            <div class="p-4 border-t border-slate-100 bg-white flex justify-center">
                {{ $branches->links() }}
            </div>
        @endif
    </div>
</div>
