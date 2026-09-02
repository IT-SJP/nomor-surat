<div class="space-y-8">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-2">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Dashboard Monitoring Surat</h1>
            <p class="text-slate-500 text-xs sm:text-sm mt-1">Ikhtisar penerbitan nomor surat keluar di seluruh unit bisnis PT Selamat Jaya Persada.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <a href="{{ route('letter.request') }}" class="btn btn-primary btn-md rounded-lg text-white font-bold shadow-md shadow-primary-600/20 text-xs sm:text-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Buat Nomor Surat</span>
            </a>
            <a href="{{ route('letter.history') }}" class="btn btn-outline btn-md rounded-lg font-bold text-slate-600 hover:bg-slate-50 hover:text-primary-600 border-slate-200 text-xs sm:text-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <span>Semua Arsip</span>
            </a>
        </div>
    </div>

    <!-- 4 Key Summary Stats (KPI Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <!-- Total Surat -->
        <div class="card bg-base-100 rounded-3xl shadow-xs border border-slate-200/80 p-5 sm:p-6 card-hover-lift">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Surat Keluar</p>
                    <h3 class="text-2xl sm:text-3xl font-black text-primary-600">{{ number_format($totalLetters) }}</h3>
                    <p class="text-[11px] text-slate-500 font-medium">Akumulasi seluruh cabang</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-primary-50 text-primary-600 border border-primary-100 flex items-center justify-center shadow-2xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Bulan Ini -->
        <div class="card bg-base-100 rounded-3xl shadow-xs border border-slate-200/80 p-5 sm:p-6 card-hover-lift">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Bulan Ini</p>
                    <h3 class="text-2xl sm:text-3xl font-black text-emerald-600">{{ number_format($lettersThisMonth) }}</h3>
                    <p class="text-[11px] text-slate-500 font-medium">{{ date('F Y') }}</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center shadow-2xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Hari Ini -->
        <div class="card bg-base-100 rounded-3xl shadow-xs border border-slate-200/80 p-5 sm:p-6 card-hover-lift">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Hari Ini</p>
                    <h3 class="text-2xl sm:text-3xl font-black text-teal-600">{{ number_format($lettersToday) }}</h3>
                    <p class="text-[11px] text-slate-500 font-medium">{{ date('d M Y') }}</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 border border-teal-100 flex items-center justify-center shadow-2xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Cabang Terkoneksi -->
        <div class="card bg-base-100 rounded-3xl shadow-xs border border-slate-200/80 p-5 sm:p-6 card-hover-lift">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Cabang Terkoneksi</p>
                    <h3 class="text-2xl sm:text-3xl font-black text-indigo-600">{{ $totalBranchesCount }}</h3>
                    <p class="text-[11px] text-slate-500 font-medium">Sync Absenku SJP</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 border border-indigo-100 flex items-center justify-center shadow-2xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Company Stats Grid -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg sm:text-xl font-extrabold tracking-tight text-slate-900">Akumulasi Surat Per Cabang</h2>
                <p class="text-xs text-slate-500">Klik kartu cabang untuk memfilter arsip nomor surat cabang tersebut</p>
            </div>
            <span class="badge badge-primary badge-soft text-xs font-bold rounded-lg px-2.5 py-1">{{ count($branches) }} Cabang</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4">
            @foreach($branches as $branch)
                @php
                    $count = $branchStats[$branch['code']] ?? 0;
                @endphp
                <a href="{{ route('letter.history', ['branch' => $branch['code']]) }}"
                   class="card bg-base-100 hover:bg-primary-50/40 shadow-xs hover:border-primary-500/50 transition-all duration-200 border border-slate-200/80 rounded-2xl p-4 text-center group cursor-pointer card-hover-lift">
                    <div class="badge badge-outline font-mono font-bold text-xs group-hover:bg-primary-600 group-hover:text-white group-hover:border-primary-600 transition-colors self-center py-1 px-2 rounded-md">
                        {{ $branch['code'] }}
                    </div>
                    <div class="text-2xl font-black tracking-tight text-primary-600 mt-2">
                        {{ $count }}
                    </div>
                    <p class="text-[11px] font-semibold text-slate-600 truncate mt-1" title="{{ $branch['name'] }}">
                        {{ $branch['name'] }}
                    </p>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Recent Letters Activity Feed -->
    <div class="card bg-base-100 shadow-xs border border-slate-200/80 rounded-3xl overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h2 class="text-base sm:text-lg font-extrabold text-slate-900">Pengajuan Surat Terkini</h2>
                <p class="text-xs text-slate-500">Daftar nomor surat keluar yang baru saja diterbitkan</p>
            </div>
            <a href="{{ route('letter.history') }}" class="text-xs font-bold text-primary-600 hover:text-primary-700 flex items-center gap-1 transition-colors">
                <span>Lihat Seluruh Arsip</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>

        @if($recentLetters->isEmpty())
            <div class="text-center py-16 p-6">
                <div class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-200 text-slate-400 flex items-center justify-center text-xl mx-auto mb-3 shadow-2xs">
                    📭
                </div>
                <p class="font-bold text-sm text-slate-900">Belum ada surat yang diterbitkan</p>
                <p class="text-xs text-slate-500 mt-1">Mulai buat nomor surat resmi pertama Anda sekarang.</p>
                <a href="{{ route('letter.request') }}" class="btn btn-primary btn-sm text-white font-bold rounded-lg mt-4 shadow-md shadow-primary-600/20">
                    + Buat Nomor Pertama
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table min-w-full divide-y divide-slate-200 text-xs sm:text-sm">
                    <thead class="bg-slate-50/70">
                        <tr class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                            <th class="px-6 py-3.5 text-left">Nomor Registrasi</th>
                            <th class="px-6 py-3.5 text-left">Cabang</th>
                            <th class="px-6 py-3.5 text-left">Tujuan / Instansi</th>
                            <th class="px-6 py-3.5 text-left">Perihal Surat</th>
                            <th class="px-6 py-3.5 text-left">Pemohon</th>
                            <th class="px-6 py-3.5 text-left">Waktu Terbit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentLetters as $letter)
                            <tr class="hover:bg-slate-50/80 transition-colors group">
                                <td class="px-6 py-3.5 whitespace-nowrap">
                                    <span class="font-mono font-bold text-primary-600 text-xs sm:text-sm">{{ $letter->reference_number }}</span>
                                </td>
                                <td class="px-6 py-3.5 whitespace-nowrap">
                                    <span class="badge badge-outline badge-sm font-mono font-bold text-primary-600 border-primary-200 rounded-md">{{ $letter->branch_code }}</span>
                                </td>
                                <td class="px-6 py-3.5 whitespace-nowrap">
                                    <span class="font-semibold text-slate-700">{{ $letter->target_code }}</span>
                                </td>
                                <td class="px-6 py-3.5 max-w-xs truncate" title="{{ $letter->subject }}">
                                    <span class="font-medium text-slate-900">{{ $letter->subject }}</span>
                                </td>
                                <td class="px-6 py-3.5 whitespace-nowrap">
                                    <span class="font-semibold text-slate-800">{{ $letter->requestor_name }}</span>
                                </td>
                                <td class="px-6 py-3.5 whitespace-nowrap text-slate-500 font-mono text-xs">
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
