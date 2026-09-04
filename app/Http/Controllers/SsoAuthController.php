<?php

namespace App\Http\Controllers;

use App\Models\Absen\Cabang;
use App\Models\Absen\Karyawan;
use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

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

        $secret = config('services.sso.secret', env('SSO_SECRET_KEY', 'sjp-holding-secret-sso-key'));
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

        // 1. Identifikasi kode unik cabang asli dari data Absenku SJP (absen_db.cabang)
        $rawBranchCode = (string) ($payload['raw_branch_code'] ?? $payload['branch_code'] ?? '');
        $hrisCabang = null;

        try {
            // Cocokkan langsung ke kode_cabang HRIS
            if (! empty($rawBranchCode)) {
                $hrisCabang = Cabang::where('kode_cabang', $rawBranchCode)->first();
            }

            // Jika belum ditemukan (misal alias 'SJP' atau kode_cabang kosong pada admin)
            if (! $hrisCabang) {
                $branchNameHint = (string) ($payload['branch_name'] ?? '');
                if (strtoupper($rawBranchCode) === 'SJP' || str_contains(strtoupper($branchNameHint), 'SELAMAT JAYA PERSADA')) {
                    $hrisCabang = Cabang::where('kode_cabang', 'CBNG0001')
                        ->orWhere('nama_cabang', 'ILIKE', '%SELAMAT JAYA PERSADA%')
                        ->first();
                } elseif (! empty($rawBranchCode)) {
                    $hrisCabang = Cabang::where('nama_cabang', 'ILIKE', '%'.$rawBranchCode.'%')->first();
                }
            }

            // Fallback default untuk admin jika masih belum terpetakan
            if (! $hrisCabang && ($payload['role'] ?? '') === 'admin') {
                $hrisCabang = Cabang::where('kode_cabang', 'CBNG0001')->first();
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal query cabang absen_db: '.$e->getMessage());
        }

        // Tentukan kode HRIS resmi dan nama cabang resmi
        $officialHrCode = $hrisCabang?->kode_cabang ?? (! empty($rawBranchCode) ? $rawBranchCode : 'CBNG0001');
        $officialBranchName = $hrisCabang?->nama_cabang ?? ($payload['branch_name'] ?? "Cabang {$officialHrCode}");

        // 2. Cari atau daftarkan ke tabel lokal branches agar tidak ada duplikasi data
        $localBranch = Branch::where('hr_code', $officialHrCode)->first();

        if (! $localBranch) {
            // Cek apakah ada record cabang dengan nama yang sama persis
            $localBranch = Branch::where('name', $officialBranchName)->first();

            if ($localBranch) {
                $localBranch->update([
                    'hr_code' => $officialHrCode,
                    'name' => $officialBranchName,
                ]);
            } else {
                $initialBranchCode = $payload['branch_code'] ?? null;
                if ($initialBranchCode && Branch::where('branch_code', $initialBranchCode)->exists()) {
                    $initialBranchCode = null;
                }

                $localBranch = Branch::create([
                    'hr_code' => $officialHrCode,
                    'branch_code' => $initialBranchCode,
                    'name' => $officialBranchName,
                    'is_active' => (bool) ($hrisCabang?->status ?? true),
                ]);
            }
        }

        $effectiveBranchCode = $localBranch->branch_code ?? $payload['branch_code'] ?? null;
        $effectiveBranchName = $localBranch->name;

        $email = $payload['email'] ?? null;
        $phone = $payload['phone'] ?? $payload['no_hp'] ?? null;

        // Jika email atau nomor telepon belum terisi tapi terdapat NIK karyawan, sinkronkan dari database Absenku SJP
        if ((empty($email) || empty($phone)) && ! empty($payload['nik'])) {
            try {
                $karyawanRecord = Karyawan::where('nik', $payload['nik'])->first();
                if ($karyawanRecord) {
                    $email = $email ?: $karyawanRecord->email;
                    $phone = $phone ?: $karyawanRecord->no_hp;
                }
            } catch (\Throwable $e) {
                Log::warning('Gagal sinkron data kontak karyawan dari absen_db: '.$e->getMessage());
            }
        }

        // Store authenticated SSO profile in session
        session([
            'auth_sso' => [
                'type' => $payload['type'] ?? $payload['role'],
                'role' => $payload['role'], // 'admin' | 'karyawan'
                'nik' => $payload['nik'] ?? null,
                'name' => $payload['name'] ?? 'Pengguna Absenku',
                'email' => $email,
                'phone' => $phone,
                'branch_code' => $effectiveBranchCode,
                'raw_branch_code' => $officialHrCode,
                'branch_name' => $effectiveBranchName,
                'department_name' => $payload['department_name'] ?? 'SJP Group',
                'position_name' => $payload['position_name'] ?? 'Karyawan',
                'authenticated_at' => now()->toIso8601String(),
            ],
        ]);

        session()->regenerate();

        if ($payload['role'] === 'karyawan') {
            return redirect()->route('letter.request');
        }

        return redirect()->route('dashboard');
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
