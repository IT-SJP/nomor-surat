@php
    $sso = session('auth_sso', []);
    $isAdmin = ($sso['role'] ?? '') === 'admin';
    $userName = $sso['name'] ?? 'Pengguna SJP';
    $userDept = $sso['department_name'] ?? null;
    $userPos = $sso['position_name'] ?? null;
    $userBranch = $sso['branch_code'] ?? 'SJP';
    $userBranchName = $sso['branch_name'] ?? 'PT Selamat Jaya Persada';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($title) ? $title . ' — ' : '' }} Sistem Nomor Surat | SJP Holding</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet" />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>
    <script>
        // Init theme immediately to prevent theme flashing
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    @livewireStyles
</head>
<body class="min-h-full bg-slate-50 text-slate-800 font-sans antialiased flex flex-col lg:flex-row selection:bg-primary-600 selection:text-white">
    <!-- ========================================== -->
    <!-- 1. DESKTOP SIDEBAR (Static w-64)           -->
    <!-- ========================================== -->
    <aside class="hidden lg:flex w-64 bg-white border-r border-slate-200 flex-col justify-between p-4 sticky top-0 h-screen shrink-0 z-30 shadow-xs">
        <!-- Top Section -->
        <div class="flex flex-col flex-1 overflow-y-auto">
            <!-- Brand Logo -->
            <div class="flex items-center justify-center px-4 py-2 mb-5 shrink-0">
                <img src="{{ asset('assets/img/sjp_horizontal.png') }}" alt="SJP Holding" class="h-10 w-auto object-contain">
            </div>

            <!-- Navigation Links -->
            <nav class="space-y-1.5 flex-1 px-1">
                @if($isAdmin)
                    <a href="{{ route('dashboard') }}" wire:navigate class="{{ request()->routeIs('dashboard') ? 'bg-primary-600 text-white shadow-lg shadow-primary-600/20' : 'text-slate-500 hover:bg-slate-50 hover:text-primary-600' }} group flex items-center px-3.5 py-2.5 text-sm font-bold rounded-lg transition-all duration-200">
                        <svg class="mr-3 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span>Dashboard</span>
                    </a>
                @endif

                <a href="{{ route('letter.request') }}" wire:navigate class="{{ request()->routeIs('letter.request') ? 'bg-primary-600 text-white shadow-lg shadow-primary-600/20' : 'text-slate-500 hover:bg-slate-50 hover:text-primary-600' }} group flex items-center px-3.5 py-2.5 text-sm font-bold rounded-lg transition-all duration-200">
                    <svg class="mr-3 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Buat Nomor Surat</span>
                </a>

                <a href="{{ route('letter.history') }}" wire:navigate class="{{ request()->routeIs('letter.history') ? 'bg-primary-600 text-white shadow-lg shadow-primary-600/20' : 'text-slate-500 hover:bg-slate-50 hover:text-primary-600' }} group flex items-center px-3.5 py-2.5 text-sm font-bold rounded-lg transition-all duration-200">
                    <svg class="mr-3 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>Riwayat Nomor Surat</span>
                </a>

                @if ($isAdmin)
                    <a href="{{ route('branch.management') }}" wire:navigate class="{{ request()->routeIs('branch.management') ? 'bg-primary-600 text-white shadow-lg shadow-primary-600/20' : 'text-slate-500 hover:bg-slate-50 hover:text-primary-600' }} group flex items-center px-3.5 py-2.5 text-sm font-bold rounded-lg transition-all duration-200">
                        <svg class="mr-3 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <span>Pengaturan Cabang</span>
                    </a>
                @endif
            </nav>
        </div>

        <!-- Bottom Section: Kembali ke Absenku -->
        <div class="pt-3 border-t border-slate-200 px-1 shrink-0 mt-3">
            <a href="{{ route('sso.logout') }}" class="group flex items-center px-3.5 py-2.5 text-sm font-bold rounded-lg text-white bg-red-600 hover:bg-red-700 shadow-lg shadow-red-600/20 active:scale-[0.98] transition-all duration-200">
                <svg class="mr-3 h-5 w-5 shrink-0 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                <span>Kembali Absenku SJP</span>
            </a>
        </div>
    </aside>

    <!-- ========================================== -->
    <!-- 2. MOBILE TOP NAVBAR (Visible on < lg)     -->
    <!-- ========================================== -->
    <header class="lg:hidden flex items-center justify-between bg-white border-b border-slate-200 px-4 py-2.5 sticky top-0 z-40 shadow-xs">
        <div class="flex items-center gap-2.5">
            <img src="{{ asset('assets/img/sjp_horizontal.png') }}" alt="SJP Holding" class="h-7 w-auto object-contain">
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('sso.logout') }}" class="btn btn-ghost btn-square btn-sm rounded-md text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors" title="Kembali Absenku SJP">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
            </a>
        </div>
    </header>

    <!-- ========================================== -->
    <!-- 3. MAIN CONTENT CONTAINER                  -->
    <!-- ========================================== -->
    <div class="flex-1 flex flex-col min-h-screen">
        <main class="flex-1 focus:outline-none p-4 sm:p-6 md:p-8 lg:p-10 pb-28 lg:pb-10">
            @if (session('status'))
                <div class="alert alert-success alert-soft mb-6 rounded-2xl border border-emerald-200/80 p-4 shadow-xs flex items-center gap-3 animate-in fade-in slide-in-from-top-4 duration-300">
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="font-bold text-sm text-emerald-900">{{ session('status') }}</span>
                </div>
                <script>
                    (function() {
                        const showMsg = () => { if (window.showToast) window.showToast('success', @json(session('status'))); };
                        if (document.readyState === 'loading') {
                            document.addEventListener('DOMContentLoaded', showMsg, { once: true });
                        } else {
                            setTimeout(showMsg, 100);
                        }
                    })();
                </script>
            @endif

            @if (session('error'))
                <div class="alert alert-error alert-soft mb-6 rounded-2xl border border-red-200/80 p-4 shadow-xs flex items-center gap-3 animate-in fade-in slide-in-from-top-4 duration-300">
                    <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-bold text-sm text-red-900">{{ session('error') }}</span>
                </div>
                <script>
                    (function() {
                        const showMsg = () => { if (window.showToast) window.showToast('error', @json(session('error'))); };
                        if (document.readyState === 'loading') {
                            document.addEventListener('DOMContentLoaded', showMsg, { once: true });
                        } else {
                            setTimeout(showMsg, 100);
                        }
                    })();
                </script>
            @endif

            {{ $slot }}
        </main>
    </div>

    <!-- ========================================== -->
    <!-- 4. MOBILE BOTTOM DOCK (Hybrid)             -->
    <!-- ========================================== -->
    <nav class="lg:hidden dock dock-bottom fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-lg border-t border-slate-200 shadow-2xl py-1 px-2 safe-area-pb">
        @if($isAdmin)
            <a href="{{ route('dashboard') }}" wire:navigate class="{{ request()->routeIs('dashboard') ? 'dock-active text-primary-600 font-bold' : 'text-slate-400' }} flex flex-col items-center py-1 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="dock-label text-[10px] mt-0.5 font-medium">Dashboard</span>
            </a>
        @endif

        <a href="{{ route('letter.request') }}" wire:navigate class="{{ request()->routeIs('letter.request') ? 'dock-active text-primary-600 font-bold' : 'text-slate-400' }} flex flex-col items-center py-1 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span class="dock-label text-[10px] mt-0.5 font-medium">Nomor Surat</span>
        </a>

        <a href="{{ route('letter.history') }}" wire:navigate class="{{ request()->routeIs('letter.history') ? 'dock-active text-primary-600 font-bold' : 'text-slate-400' }} flex flex-col items-center py-1 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span class="dock-label text-[10px] mt-0.5 font-medium">Riwayat</span>
        </a>

        @if ($isAdmin)
            <a href="{{ route('branch.management') }}" wire:navigate class="{{ request()->routeIs('branch.management') ? 'dock-active text-primary-600 font-bold' : 'text-slate-400' }} flex flex-col items-center py-1 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="dock-label text-[10px] mt-0.5 font-medium">Cabang</span>
            </a>
        @endif
    </nav>

    <!-- ========================================== -->
    <!-- 5. FLOATING ACTION BUTTON (Theme Controller)-->
    <!-- ========================================== -->
    <div class="fixed bottom-20 right-4 lg:bottom-6 lg:right-6 z-50">
        <div class="tooltip tooltip-left" data-tip="Ganti Tema">
            <label class="btn btn-circle btn-primary shadow-xl shadow-primary-600/30 border-none swap swap-rotate text-white hover:scale-110 active:scale-95 transition-all duration-200 cursor-pointer">
                <input type="checkbox" class="theme-controller" value="forest" />

                <!-- Sun icon (shown in dark/forest mode) -->
                <svg class="swap-on h-5 w-5 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M5.64,17l-.71.71a1,1,0,0,0,0,1.41,1,1,0,0,0,1.41,0l.71-.71A1,1,0,0,0,5.64,17ZM5,12a1,1,0,0,0-1-1H3a1,1,0,0,0,0,2H4A1,1,0,0,0,5,12Zm7-7a1,1,0,0,0,1-1V3a1,1,0,0,0-2,0V4A1,1,0,0,0,12,5ZM5.64,7.05a1,1,0,0,0,.7.29,1,1,0,0,0,.71-.29,1,1,0,0,0,0-1.41l-.71-.71A1,1,0,0,0,4.93,6.34Zm12,.29a1,1,0,0,0,.7-.29l.71-.71a1,1,0,1,0-1.41-1.41L17,5.64a1,1,0,0,0,0,1.41A1,1,0,0,0,17.66,7.34ZM21,11H20a1,1,0,0,0,0,2h1a1,1,0,0,0,0-2Zm-9,8a1,1,0,0,0-1,1v1a1,1,0,0,0,2,0V20A1,1,0,0,0,12,19ZM18.36,17A1,1,0,0,0,17,18.36l.71.71a1,1,0,0,0,1.41,0,1,1,0,0,0,0-1.41ZM12,6.5A5.5,5.5,0,1,0,17.5,12,5.51,5.51,0,0,0,12,6.5Zm0,9A3.5,3.5,0,1,1,15.5,12,3.5,3.5,0,0,1,12,15.5Z" />
                </svg>

                <!-- Moon icon (shown in light mode) -->
                <svg class="swap-off h-5 w-5 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M21.64,13a1,1,0,0,0-1.05-.14,8.05,8.05,0,0,1-3.37.73A8.15,8.15,0,0,1,9.08,5.49a8.59,8.59,0,0,1,.25-2A1,1,0,0,0,8,2.36,10.14,10.14,0,1,0,22,14.05,1,1,0,0,0,21.64,13Zm-9.5,6.69A8.14,8.14,0,0,1,7.08,5.22v.27A10.15,10.15,0,0,0,17.22,15.63a9.79,9.79,0,0,0,2.1-.22A8.11,8.11,0,0,1,12.14,19.73Z" />
                </svg>
            </label>
        </div>
    </div>

    @livewireScripts
    <script>
        // Sync and handle theme toggle persistence
        function syncThemeController() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            const toggles = document.querySelectorAll('.theme-controller');
            toggles.forEach(toggle => {
                toggle.checked = (savedTheme === 'forest' || savedTheme === 'dark');
            });
        }

        syncThemeController();

        document.addEventListener('change', (e) => {
            if (e.target && e.target.classList.contains('theme-controller')) {
                const theme = e.target.checked ? 'forest' : 'light';
                document.documentElement.setAttribute('data-theme', theme);
                localStorage.setItem('theme', theme);
                document.querySelectorAll('.theme-controller').forEach(ctrl => {
                    ctrl.checked = e.target.checked;
                });
            }
        });

        document.addEventListener('livewire:navigated', syncThemeController);
    </script>
</body>
</html>
