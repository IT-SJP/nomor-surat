<div class="space-y-6">
    <!-- Header & Action Row -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                {{ $isKaryawan ? "Riwayat Nomor Surat ({$userBranch})" : 'Riwayat Seluruh Nomor Surat' }}
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                {{ $isKaryawan ? "Daftar arsip nomor surat resmi untuk cabang {$userBranch}." : 'Daftar arsip nomor surat resmi seluruh anak perusahaan PT Selamat Jaya Persada.' }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <button
                type="button"
                wire:click="exportCsv"
                class="btn btn-outline btn-md rounded-lg gap-2 font-bold text-slate-600 hover:bg-slate-50 hover:text-primary-600 border-slate-200 text-xs sm:text-sm"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Unduh CSV</span>
            </button>

            <a href="{{ route('letter.request') }}" class="btn btn-primary btn-md rounded-lg text-white font-bold gap-2 shadow-md shadow-primary-600/20 text-xs sm:text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Buat Nomor Surat</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="card bg-base-100 shadow-xs border border-slate-200/80 rounded-3xl p-6 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
            <!-- Real-time Search Input -->
            <div class="lg:col-span-5 space-y-1">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                    Cari Nomor / Perihal / Pemohon
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none z-10 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        placeholder="Ketik nomor surat, perihal, atau nama..."
                        class="input input-bordered w-full pl-10 rounded-lg text-sm focus:border-primary-500 bg-slate-50/80 focus:bg-white"
                    />
                </div>
            </div>

            <!-- Branch Filter -->
            <div class="lg:col-span-3 space-y-1">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Cabang</label>
                @if($isKaryawan)
                    <div class="input input-bordered w-full flex items-center justify-between bg-slate-50 text-xs font-bold text-primary-600 rounded-lg cursor-not-allowed border-slate-200">
                        <span>📍 {{ $userBranch }}</span>
                        <span class="badge badge-ghost badge-xs text-slate-500">Terkunci</span>
                    </div>
                @else
                    <select wire:model.live="branch" class="select select-bordered w-full rounded-lg text-sm text-slate-700 bg-white focus:border-primary-500">
                        <option value="">Semua Cabang SJP</option>
                        @foreach($branches as $b)
                            <option value="{{ $b['code'] }}">{{ $b['code'] }} &mdash; {{ $b['name'] }}</option>
                        @endforeach
                    </select>
                @endif
            </div>

            <!-- Month Filter -->
            <div class="lg:col-span-2 space-y-1">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Bulan</label>
                <select wire:model.live="month" class="select select-bordered w-full rounded-lg text-sm text-slate-700 bg-white focus:border-primary-500">
                    <option value="">Semua Bulan</option>
                    @foreach($romanMonths as $num => $roman)
                        <option value="{{ $num }}">Bulan {{ $num }} ({{ $roman }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Year Filter -->
            <div class="lg:col-span-2 space-y-1">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Tahun</label>
                <input
                    wire:model.live="year"
                    type="number"
                    placeholder="Semua Tahun"
                    class="input input-bordered w-full rounded-lg text-sm font-mono focus:border-primary-500 bg-slate-50/80 focus:bg-white"
                />
            </div>
        </div>

        @if($search || ($isAdmin && $branch) || $year || $month)
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <span class="badge badge-primary badge-xs rounded-full"></span>
                    <span>Filter pencarian aktif</span>
                </div>
                <button type="button" wire:click="resetFilters" class="btn btn-ghost btn-xs text-rose-600 font-bold hover:bg-rose-50">
                    ✕ Reset Filter
                </button>
            </div>
        @endif
    </div>

    <!-- Letter Table Card -->
    <div class="card bg-base-100 shadow-xs border border-slate-200/80 rounded-3xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table min-w-full divide-y divide-slate-200 text-xs sm:text-sm">
                <thead class="bg-slate-50/70">
                    <tr class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-4 w-12 text-center">No</th>
                        <th class="px-6 py-4 text-left">Nomor Surat Resmi</th>
                        <th class="px-6 py-4 text-left">Cabang</th>
                        <th class="px-6 py-4 text-left">Perihal / Keperluan</th>
                        <th class="px-6 py-4 text-left">Penerima / Instansi</th>
                        <th class="px-6 py-4 text-left">Pemohon</th>
                        <th class="px-6 py-4 w-16 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($letters as $letter)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4 text-center font-mono text-slate-400 font-semibold">
                                {{ $loop->iteration + ($letters->currentPage() - 1) * $letters->perPage() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-mono font-bold text-primary-600 text-xs sm:text-sm tracking-wide select-all">
                                    {{ $letter->reference_number }}
                                </div>
                                <span class="text-[10px] text-slate-400 font-medium font-mono">
                                    {{ $letter->created_at->format('d/m/Y • H:i') }} WIB
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="badge badge-outline badge-sm font-mono font-bold text-primary-600 border-primary-200 rounded-md">
                                    {{ $letter->branch_code }}
                                </span>
                            </td>
                            <td class="px-6 py-4 max-w-xs">
                                <p class="font-bold text-slate-900 line-clamp-1 text-xs sm:text-sm">{{ $letter->subject }}</p>
                                <p class="text-[11px] text-slate-500 line-clamp-1 mt-0.5">{{ $letter->purpose }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-semibold text-slate-700">{{ $letter->target_code }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="font-bold text-slate-900 leading-tight text-xs sm:text-sm">{{ $letter->requestor_name }}</p>
                                @if($letter->requestor_department || $letter->requestor_position)
                                    <p class="text-[10px] text-slate-500 mt-0.5">{{ $letter->requestor_department }} • {{ $letter->requestor_position }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <button
                                    type="button"
                                    wire:click="viewLetter({{ $letter->id }})"
                                    class="btn btn-square btn-primary btn-soft btn-sm rounded-md"
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
                            <td colspan="7" class="text-center py-16 text-slate-400">
                                <div class="flex flex-col items-center gap-2.5 max-w-sm mx-auto">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-200 text-slate-400 flex items-center justify-center text-xl shadow-2xs">
                                        📭
                                    </div>
                                    <div>
                                        <p class="font-bold text-sm text-slate-900">Belum ada arsip nomor surat</p>
                                        <p class="text-xs text-slate-500 mt-0.5">Tidak ada data surat yang sesuai dengan kriteria filter saat ini.</p>
                                    </div>
                                    <a href="{{ route('letter.request') }}" class="btn btn-primary btn-sm text-white font-bold rounded-lg mt-2 shadow-md shadow-primary-600/20">
                                        + Buat Nomor Surat Baru
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($letters->hasPages())
            <div class="p-4 border-t border-slate-100 bg-white flex justify-center">
                {{ $letters->links() }}
            </div>
        @endif
    </div>

    <!-- Detail Modal -->
    <div class="modal {{ $showDetailModal ? 'modal-open' : '' }} backdrop-blur-sm" role="dialog">
        <div class="modal-box max-w-lg rounded-3xl border border-slate-200/80 p-6 sm:p-7 space-y-5 shadow-2xl bg-white" x-data="{ copiedDetail: false }">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="badge badge-primary badge-soft badge-sm font-bold rounded-md">Arsip Resmi</span>
                    <h3 class="font-extrabold text-base sm:text-lg text-slate-900">Detail Nomor Surat</h3>
                </div>
                <button type="button" wire:click="closeDetailModal" class="btn btn-ghost btn-xs btn-circle text-slate-400 hover:text-slate-600">✕</button>
            </div>

            @if($selectedLetter)
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nomor Registrasi:</span>
                        <button
                            type="button"
                            class="btn btn-primary btn-xs text-white font-bold rounded-md shadow-2xs"
                            @click="navigator.clipboard.writeText('{{ $selectedLetter->reference_number }}'); copiedDetail = true; setTimeout(() => copiedDetail = false, 2500)"
                        >
                            <span x-text="copiedDetail ? '✓ Tersalin' : 'Salin Nomor'">Salin Nomor</span>
                        </button>
                    </div>
                    <div class="font-mono font-black text-xl sm:text-2xl text-primary-600 break-all select-all">
                        {{ $selectedLetter->reference_number }}
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="bg-slate-50/60 p-3 rounded-2xl border border-slate-200/80">
                        <p class="text-slate-400 font-semibold text-[10px] uppercase">Cabang / Entitas</p>
                        <p class="font-bold mt-0.5 text-slate-900">{{ $selectedLetter->branch_code }} &mdash; {{ $selectedLetter->branch_name ?? 'SJP Group' }}</p>
                    </div>
                    <div class="bg-slate-50/60 p-3 rounded-2xl border border-slate-200/80">
                        <p class="text-slate-400 font-semibold text-[10px] uppercase">Tujuan / Instansi</p>
                        <p class="font-bold mt-0.5 text-slate-900">{{ $selectedLetter->target_code }}</p>
                    </div>
                    <div class="bg-slate-50/60 p-3 rounded-2xl border border-slate-200/80 sm:col-span-2">
                        <p class="text-slate-400 font-semibold text-[10px] uppercase">Perihal Surat</p>
                        <p class="font-bold mt-0.5 text-sm text-slate-900">{{ $selectedLetter->subject }}</p>
                    </div>
                    <div class="bg-slate-50/60 p-3 rounded-2xl border border-slate-200/80 sm:col-span-2">
                        <p class="text-slate-400 font-semibold text-[10px] uppercase">Keperluan / Keterangan</p>
                        <p class="mt-0.5 text-slate-700 whitespace-pre-wrap leading-relaxed">{{ $selectedLetter->purpose }}</p>
                    </div>
                    <div class="bg-slate-50/60 p-3 rounded-2xl border border-slate-200/80">
                        <p class="text-slate-400 font-semibold text-[10px] uppercase">Pemohon (Karyawan)</p>
                        <p class="font-bold mt-0.5 text-slate-900">{{ $selectedLetter->requestor_name }}</p>
                        @if($selectedLetter->requestor_department)
                            <p class="text-[10px] text-slate-500">{{ $selectedLetter->requestor_department }}</p>
                        @endif
                    </div>
                    <div class="bg-slate-50/60 p-3 rounded-2xl border border-slate-200/80">
                        <p class="text-slate-400 font-semibold text-[10px] uppercase">Waktu Diterbitkan</p>
                        <p class="font-bold mt-0.5 text-slate-900 font-mono">{{ $selectedLetter->created_at->translatedFormat('d F Y, H:i') }} WIB</p>
                    </div>
                </div>
            @endif

            <div class="modal-action justify-end pt-2 border-t border-slate-100">
                <button type="button" wire:click="closeDetailModal" class="btn btn-ghost btn-sm text-xs font-semibold text-slate-500">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
