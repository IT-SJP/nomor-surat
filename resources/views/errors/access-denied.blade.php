<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="emerald">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Terbatas - Sistem Nomor Surat SJP Holding</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200 flex items-center justify-center p-4 antialiased text-base-content">
    <div class="card bg-base-100 shadow-2xl border border-base-300 max-w-md w-full p-6 sm:p-8 text-center">
        <!-- Brand / Lock Icon -->
        <div class="flex justify-center mb-4">
            <div class="w-16 h-16 rounded-2xl bg-emerald-600/10 text-emerald-600 border border-emerald-600/20 flex items-center justify-center shadow-inner">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
        </div>

        <div class="inline-block mx-auto mb-2">
            <span class="badge badge-error badge-outline font-bold text-xs uppercase tracking-wider">Akses Terkunci (403)</span>
        </div>

        <h1 class="text-2xl font-bold tracking-tight text-base-content mt-1">Portal Nomor Surat Internal</h1>
        <p class="text-xs text-base-content/60 font-medium mt-1">PT Selamat Jaya Persada (SJP Holding)</p>

        <div class="divider my-4"></div>

        <div class="bg-base-200/60 rounded-xl p-4 text-xs text-base-content/80 text-left space-y-2 border border-base-300/50">
            <p class="font-semibold text-error flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Autentikasi Diperlukan</span>
            </p>
            <p>
                {{ $reason ?? 'Aplikasi ini bukan untuk publik dan hanya dapat diakses oleh Karyawan atau Admin resmi yang telah login di portal Absenku SJP.' }}
            </p>
            <p class="text-[11px] text-base-content/60">
                👉 Silakan buka aplikasi <strong>Absenku SJP</strong>, lalu klik menu <strong>Nomor Surat</strong> pada navigasi dashboard Anda.
            </p>
        </div>

        <div class="mt-6 flex flex-col gap-2">
            <a href="{{ config('services.sso.absen_url', env('ABSEN_APP_URL', 'http://localhost:8000')) }}" class="btn btn-primary text-white font-bold w-full shadow-md shadow-emerald-500/20">
                <span>🔐 Buka Portal Absenku SJP</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
            </a>
        </div>
    </div>
</body>
</html>
