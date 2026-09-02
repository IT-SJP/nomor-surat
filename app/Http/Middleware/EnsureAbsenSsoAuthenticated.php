<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAbsenSsoAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('auth_sso')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated SSO. Akses hanya diizinkan melalui portal Absenku SJP.',
                ], 403);
            }

            return response()->view('errors.access-denied', [
                'reason' => 'Sesi tidak ditemukan. Anda harus login terlebih dahulu di aplikasi Absenku SJP.',
            ], 403);
        }

        return $next($request);
    }
}
