<div class="space-y-8">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Dashboard Monitoring Surat</h1>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <!-- Total Surat -->
        <div class="card bg-base-100 dark:bg-slate-900 rounded-3xl shadow-xs border border-slate-200/80 dark:border-slate-800 p-5 sm:p-6 card-hover-lift">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Nomor Surat Keluar</p>
                    <h3 class="text-2xl sm:text-3xl font-black text-primary-600 dark:text-primary-400">{{ number_format($totalLetters) }}</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                        {{ $isAdminCabang ? ($adminBranchCode ? "Cabang {$adminBranchCode}" : $adminBranchName) : 'Seluruh cabang' }}
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400 border border-primary-100 dark:border-primary-900/50 flex items-center justify-center shadow-2xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Bulan Ini -->
        <div class="card bg-base-100 dark:bg-slate-900 rounded-3xl shadow-xs border border-slate-200/80 dark:border-slate-800 p-5 sm:p-6 card-hover-lift">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Bulan Ini</p>
                    <h3 class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ number_format($lettersThisMonth) }}</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">{{ date('F Y') }}</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/50 flex items-center justify-center shadow-2xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Hari Ini -->
        <div class="card bg-base-100 dark:bg-slate-900 rounded-3xl shadow-xs border border-slate-200/80 dark:border-slate-800 p-5 sm:p-6 card-hover-lift">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Hari Ini</p>
                    <h3 class="text-2xl sm:text-3xl font-black text-teal-600 dark:text-teal-400">{{ number_format($lettersToday) }}</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">{{ date('d M Y') }}</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-teal-50 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400 border border-teal-100 dark:border-teal-900/50 flex items-center justify-center shadow-2xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Cabang Terkoneksi -->
        <div class="card bg-base-100 dark:bg-slate-900 rounded-3xl shadow-xs border border-slate-200/80 dark:border-slate-800 p-5 sm:p-6 card-hover-lift">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                        {{ $isAdminCabang ? 'Cabang Anda' : 'Cabang Tersedia' }}
                    </p>
                    <h3 class="text-2xl sm:text-3xl font-black text-indigo-600 dark:text-indigo-400">
                        {{ $isAdminCabang ? $adminBranchCode : $totalBranchesCount }}
                    </h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium truncate max-w-[150px]" title="{{ $isAdminCabang ? $adminBranchName : 'Terdaftar dalam sistem' }}">
                        {{ $isAdminCabang ? $adminBranchName : 'Terdaftar dalam sistem' }}
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/50 flex items-center justify-center shadow-2xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    @if(! $isAdminCabang)
        <!-- Company Stats Grid -->
        <div class="space-y-4">
            <h2 class="text-lg sm:text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">Akumulasi Surat Per Cabang</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4">
                @foreach($branches as $branch)
                    @php
                        $count = $branchStats[$branch['code']] ?? 0;
                    @endphp
                    <a href="{{ route('letter.history', ['branch' => $branch['code']]) }}"
                       wire:navigate
                       class="card bg-base-100 dark:bg-slate-900 hover:bg-primary-50/40 dark:hover:bg-primary-950/30 shadow-xs hover:border-primary-500/50 dark:hover:border-primary-500/50 transition-all duration-200 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 text-center group cursor-pointer card-hover-lift">
                        <div class="badge badge-outline font-mono font-bold text-xs group-hover:bg-primary-600 group-hover:text-white group-hover:border-primary-600 transition-colors self-center py-1 px-2 rounded-md">
                            {{ $branch['code'] }}
                        </div>
                        <div class="text-2xl font-black tracking-tight text-primary-600 dark:text-primary-400 mt-2">
                            {{ $count }}
                        </div>
                        <p class="text-[11px] font-semibold text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-slate-200 truncate mt-1" title="{{ $branch['name'] }}">
                            {{ $branch['name'] }}
                        </p>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Recent Letters Activity Feed -->
    <div class="card bg-base-100 dark:bg-slate-900 shadow-xs border border-slate-200/80 dark:border-slate-800 rounded-3xl overflow-hidden">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h2 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white">Pengajuan Surat Terkini</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Daftar nomor surat keluar yang baru saja diterbitkan</p>
            </div>
            <a href="{{ route('letter.history') }}" wire:navigate class="text-xs font-bold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 flex items-center gap-1 transition-colors">
                <span>Lihat Seluruh Arsip</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>

        @if($recentLetters->isEmpty())
            <div class="text-center py-16 p-6">
                <div class="w-12 h-12 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-slate-400 dark:text-slate-500 flex items-center justify-center text-xl mx-auto mb-3 shadow-2xs">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-inbox-off"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M8 4h10a2 2 0 0 1 2 2v10m-.593 3.422a2 2 0 0 1 -1.407 .578h-12a2 2 0 0 1 -2 -2v-12c0 -.554 .225 -1.056 .59 -1.418" /><path d="M4 13h3l3 3h4l.987 -.987m2.013 -2.013h3" /><path d="M3 3l18 18" /></svg>
                </div>
                <p class="font-bold text-sm text-slate-900 dark:text-white">Belum ada nomor surat yang diterbitkan</p>
                <a href="{{ route('letter.request') }}" wire:navigate class="btn btn-primary btn-sm text-white font-bold rounded-lg mt-4 shadow-md shadow-primary-600/20">
                    + Buat Nomor Surat
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-xs sm:text-sm">
                    <thead class="bg-slate-50/70 dark:bg-slate-800/70">
                        <tr class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            <th class="px-6 py-3.5 text-left">Nomor Registrasi</th>
                            <th class="px-6 py-3.5 text-left">Cabang</th>
                            <th class="px-6 py-3.5 text-left">Tujuan / Instansi</th>
                            <th class="px-6 py-3.5 text-left">Perihal Surat</th>
                            <th class="px-6 py-3.5 text-left">Pemohon</th>
                            <th class="px-6 py-3.5 text-left">Waktu Terbit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @foreach($recentLetters as $letter)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors group">
                                <td class="px-6 py-3.5 whitespace-nowrap">
                                    <span class="font-mono font-bold text-primary-600 dark:text-primary-400 text-xs sm:text-sm">{{ $letter->reference_number }}</span>
                                </td>
                                <td class="px-6 py-3.5 whitespace-nowrap">
                                    <span class="badge badge-outline badge-sm font-mono font-bold text-primary-600 dark:text-primary-400 border-primary-200 dark:border-primary-800/80 rounded-md">{{ $letter->branch_code }}</span>
                                </td>
                                <td class="px-6 py-3.5 whitespace-nowrap">
                                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $letter->target_code }}</span>
                                </td>
                                <td class="px-6 py-3.5 max-w-xs truncate" title="{{ $letter->subject }}">
                                    <span class="font-medium text-slate-900 dark:text-slate-100">{{ $letter->subject }}</span>
                                </td>
                                <td class="px-6 py-3.5 whitespace-nowrap">
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $letter->requestor_name }}</span>
                                </td>
                                <td class="px-6 py-3.5 whitespace-nowrap text-slate-500 dark:text-slate-400 font-mono text-xs">
                                    {{ $letter->created_at->translatedFormat('d M Y, H:i') }} WIB
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
