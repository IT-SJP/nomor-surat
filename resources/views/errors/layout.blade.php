<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Terjadi Kesalahan') | Sistem Nomor Surat SJP Holding</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #f8fafc;
            background-image: 
                radial-gradient(circle at 15% 20%, rgba(30, 116, 253, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 85% 80%, rgba(99, 102, 241, 0.05) 0%, transparent 40%);
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px 16px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .error-card {
            background: #ffffff;
            width: 100%;
            max-width: 460px;
            border-radius: 24px;
            padding: 40px 32px;
            text-align: center;
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 
                0 20px 25px -5px rgba(15, 23, 42, 0.05),
                0 8px 10px -6px rgba(15, 23, 42, 0.02);
            animation: cardEntrance 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes cardEntrance {
            from {
                opacity: 0;
                transform: translateY(12px) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Icon Container */
        .icon-wrapper {
            width: 76px;
            height: 76px;
            border-radius: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            transition: transform 0.25s ease;
        }

        .icon-wrapper:hover {
            transform: scale(1.04);
        }

        .icon-wrapper svg {
            width: 38px;
            height: 38px;
            stroke-width: 2;
        }

        /* Badge Pills */
        .badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 14px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        /* Color Themes */
        .theme-blue .icon-wrapper {
            background-color: #eff6ff;
            color: #2563eb;
        }
        .theme-blue .badge-pill {
            background-color: #eff6ff;
            color: #1d4ed8;
            border: 1px solid rgba(37, 99, 235, 0.18);
        }

        .theme-red .icon-wrapper {
            background-color: #fef2f2;
            color: #dc2626;
        }
        .theme-red .badge-pill {
            background-color: #fef2f2;
            color: #b91c1c;
            border: 1px solid rgba(220, 38, 38, 0.18);
        }

        .theme-amber .icon-wrapper {
            background-color: #fffbeb;
            color: #d97706;
        }
        .theme-amber .badge-pill {
            background-color: #fffbeb;
            color: #b45309;
            border: 1px solid rgba(217, 119, 6, 0.18);
        }

        .theme-purple .icon-wrapper {
            background-color: #f5f3ff;
            color: #7c3aed;
        }
        .theme-purple .badge-pill {
            background-color: #f5f3ff;
            color: #6d28d9;
            border: 1px solid rgba(124, 58, 237, 0.18);
        }

        .theme-slate .icon-wrapper {
            background-color: #f1f5f9;
            color: #475569;
        }
        .theme-slate .badge-pill {
            background-color: #f1f5f9;
            color: #334155;
            border: 1px solid rgba(71, 85, 105, 0.18);
        }

        /* Typography */
        .error-title {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.3;
            letter-spacing: -0.02em;
            margin-bottom: 8px;
        }

        .error-description {
            font-size: 14.5px;
            line-height: 1.6;
            color: #64748b;
            margin-bottom: 28px;
            padding: 0 8px;
        }

        /* Action Buttons */
        .action-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 100%;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 12px 20px;
            font-size: 14.5px;
            font-weight: 600;
            border-radius: 12px;
            background-color: #1e74fd;
            color: #ffffff !important;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 4px 12px rgba(30, 116, 253, 0.25);
        }

        .btn-primary:hover {
            background-color: #1763dd;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(30, 116, 253, 0.35);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 11px 20px;
            font-size: 14px;
            font-weight: 500;
            border-radius: 12px;
            background-color: #ffffff;
            color: #475569 !important;
            text-decoration: none;
            border: 1px solid #cbd5e1;
            cursor: pointer;
            transition: all 0.18s ease;
        }

        .btn-secondary:hover {
            background-color: #f8fafc;
            color: #1e293b !important;
            border-color: #94a3b8;
        }

        .btn-secondary svg, .btn-primary svg {
            width: 16px;
            height: 16px;
            stroke-width: 2.2;
            flex-shrink: 0;
        }

        /* Card Footer / Brand */
        .card-footer {
            margin-top: 24px;
            font-size: 12px;
            color: #94a3b8;
            letter-spacing: 0.01em;
        }

        @media (max-width: 480px) {
            .error-card {
                padding: 32px 20px;
                border-radius: 20px;
            }
            .error-title {
                font-size: 20px;
            }
            .error-description {
                font-size: 13.5px;
                margin-bottom: 24px;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    @php
        $sso = session('auth_sso', []);
        $role = strtolower((string) ($sso['role'] ?? ''));
        $adminRole = strtolower((string) ($sso['admin_role'] ?? ''));
        $type = strtolower((string) ($sso['type'] ?? ''));

        $isAdmin = in_array($role, ['admin', 'administrator', 'admin cabang', 'hrd'])
            || in_array($adminRole, ['admin', 'administrator', 'admin cabang', 'hrd'])
            || in_array($type, ['admin', 'administrator', 'admin cabang', 'hrd'])
            || str_contains($role, 'admin');

        $isKaryawan = !empty($sso) && !$isAdmin;
        $portalAbsenUrl = config('services.sso.absen_url', env('ABSEN_APP_URL', 'https://absenkusjp.com'));

        if ($isAdmin) {
            $defaultHomeUrl = Route::has('dashboard') ? route('dashboard') : url('/dashboard');
            $defaultHomeLabel = 'Kembali ke Dashboard Admin';
        } elseif ($isKaryawan) {
            $defaultHomeUrl = Route::has('letter.request') ? route('letter.request') : url('/request');
            $defaultHomeLabel = 'Kembali ke Permohonan Surat';
        } elseif (!empty($sso)) {
            $defaultHomeUrl = Route::has('home') ? route('home') : url('/');
            $defaultHomeLabel = 'Kembali ke Beranda';
        } else {
            $defaultHomeUrl = $portalAbsenUrl;
            $defaultHomeLabel = 'Buka Portal Absenku SJP';
        }
    @endphp

    <main class="error-card theme-@yield('theme', 'blue')">
        <!-- Icon Wrapper -->
        <div class="icon-wrapper">
            @yield('icon')
        </div>

        <!-- Badge Code -->
        <div>
            <span class="badge-pill">
                @yield('badge', 'ERROR ' . $__env->yieldContent('code', '404'))
            </span>
        </div>

        <!-- Title & Description -->
        <h1 class="error-title">@yield('title', 'Terjadi Kesalahan')</h1>
        <p class="error-description">@yield('description', 'Halaman atau permintaan yang Anda akses tidak dapat diproses.')</p>

        <!-- Actions -->
        <div class="action-group">
            @section('actions')
                @hasSection('primary_button')
                    @yield('primary_button')
                @else
                    <a href="{{ trim($__env->yieldContent('home_url', $defaultHomeUrl)) }}" class="btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                        <span>@yield('home_label', $defaultHomeLabel)</span>
                    </a>
                @endif

                @hasSection('secondary_button')
                    @yield('secondary_button')
                @else
                    <button type="button" onclick="window.history.length > 1 ? window.history.back() : window.location.href='{{ $defaultHomeUrl }}'" class="btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"/>
                            <polyline points="12 19 5 12 12 5"/>
                        </svg>
                        <span>Halaman Sebelumnya</span>
                    </button>
                @endif
            @show
        </div>
    </main>

    <footer class="card-footer">
        Sistem Nomor Surat SJP Holding &bull; &copy; {{ date('Y') }}
    </footer>

    @if(request()->routeIs('preview.errors') && Route::has('preview.errors'))
        <aside style="position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background: rgba(15, 23, 42, 0.92); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.12); padding: 6px 10px; border-radius: 9999px; display: flex; align-items: center; gap: 4px; box-shadow: 0 12px 30px rgba(0,0,0,0.3); z-index: 99999; max-width: calc(100vw - 32px); overflow-x: auto;">
            <span style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; padding: 0 8px; white-space: nowrap;">
                Preview
            </span>
            @foreach([401, 403, 404, 405, 413, 419, 429, 500, 502, 503] as $errCode)
                @php $isActive = (int) request()->route('code', 404) === $errCode; @endphp
                <a href="{{ route('preview.errors', $errCode) }}" style="padding: 4px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600; text-decoration: none; white-space: nowrap; transition: all 0.15s ease; {{ $isActive ? 'background: #1e74fd; color: #ffffff; box-shadow: 0 2px 8px rgba(30,116,253,0.4);' : 'color: #cbd5e1; background: transparent;' }}">
                    {{ $errCode }}
                </a>
            @endforeach
        </aside>
    @endif
</body>
</html>
