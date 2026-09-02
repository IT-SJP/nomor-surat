<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SsoAuthController extends Controller
{
    /**
     * Handle incoming SSO authentication handoff from Absenku SJP.
     */
    public function verify(Request $request): RedirectResponse|Response
    {
        $token = $request->query('token');
        $signature = $request->query('sig');

        if (! $token || ! $signature) {
            return response()->view('errors.access-denied', [
                'reason' => 'Parameter autentikasi SSO tidak lengkap.',
            ], 403);
        }

        $secret = config('services.sso.secret', env('SSO_SECRET_KEY', 'sjp-holding-secret-sso-key-2026'));
        $expectedSignature = hash_hmac('sha256', (string) $token, (string) $secret);

        if (! hash_equals($expectedSignature, (string) $signature)) {
            return response()->view('errors.access-denied', [
                'reason' => 'Tanda tangan digital (signature) SSO tidak valid atau telah dimodifikasi.',
            ], 403);
        }

        $decodedJson = base64_decode((string) $token, true);
        if (! $decodedJson) {
            return response()->view('errors.access-denied', [
                'reason' => 'Format payload token SSO tidak valid.',
            ], 403);
        }

        $payload = json_decode($decodedJson, true);
        if (! is_array($payload) || empty($payload['role'])) {
            return response()->view('errors.access-denied', [
                'reason' => 'Struktur data token SSO tidak sesuai standar.',
            ], 403);
        }

        // Validate expiration timestamp if present
        if (isset($payload['exp']) && (int) $payload['exp'] < now()->timestamp) {
            return response()->view('errors.access-denied', [
                'reason' => 'Sesi tautan SSO telah kedaluwarsa. Silakan klik kembali tombol nomor surat di portal Absenku SJP.',
            ], 403);
        }

        // Resolve or auto-create branch in local database
        $rawBranchCode = (string) ($payload['raw_branch_code'] ?? $payload['branch_code'] ?? '');
        $localBranch = null;
        if (! empty($rawBranchCode)) {
            $localBranch = Branch::where('hr_code', $rawBranchCode)
                ->orWhere('branch_code', $rawBranchCode)
                ->first();

            if (! $localBranch) {
                $localBranch = Branch::create([
                    'hr_code' => $rawBranchCode,
                    'branch_code' => $payload['branch_code'] ?? null,
                    'name' => $payload['branch_name'] ?? "Cabang {$rawBranchCode}",
                    'is_active' => true,
                ]);
            }
        }

        $effectiveBranchCode = $localBranch?->branch_code ?? $payload['branch_code'] ?? null;
        $effectiveBranchName = $localBranch?->name ?? $payload['branch_name'] ?? 'PT SJP Group';

        // Store authenticated SSO profile in session
        session([
            'auth_sso' => [
                'type' => $payload['type'] ?? $payload['role'],
                'role' => $payload['role'], // 'admin' | 'karyawan'
                'nik' => $payload['nik'] ?? null,
                'name' => $payload['name'] ?? 'Pengguna Absenku',
                'email' => $payload['email'] ?? null,
                'branch_code' => $effectiveBranchCode,
                'raw_branch_code' => $rawBranchCode,
                'branch_name' => $effectiveBranchName,
                'department_name' => $payload['department_name'] ?? 'SJP Group',
                'position_name' => $payload['position_name'] ?? 'Karyawan',
                'authenticated_at' => now()->toIso8601String(),
            ],
        ]);

        session()->regenerate();

        if ($payload['role'] === 'karyawan') {
            return redirect()->route('letter.request')->with('status', 'Selamat datang, '.($payload['name'] ?? 'Karyawan').'!');
        }

        return redirect()->route('dashboard')->with('status', 'Login Admin Absenku SJP berhasil!');
    }

    /**
     * Terminate SSO session and return to Absenku SJP portal.
     */
    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('auth_sso');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $absenUrl = config('services.sso.absen_url', env('ABSEN_APP_URL', 'http://localhost:8000'));

        return redirect()->away($absenUrl);
    }
}
