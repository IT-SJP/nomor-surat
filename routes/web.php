<?php

use App\Http\Controllers\SsoAuthController;
use App\Livewire\BranchManagement;
use App\Livewire\DashboardStats;
use App\Livewire\LetterHistory;
use App\Livewire\LetterRequestForm;
use Illuminate\Support\Facades\Route;

// SSO Authentication Handshake Routes (Unprotected)
Route::get('/sso/verify', [SsoAuthController::class, 'verify'])->name('sso.verify');
Route::get('/sso/logout', [SsoAuthController::class, 'logout'])->name('sso.logout');

// Protected Routes (Requires Active Absenku SJP SSO Session)
Route::middleware('absen.auth')->group(function () {
    Route::get('/', function () {
        $sso = session('auth_sso', []);
        if (($sso['role'] ?? '') === 'karyawan') {
            return redirect()->route('letter.request');
        }

        return redirect()->route('dashboard');
    })->name('home');

    Route::get('/dashboard', DashboardStats::class)->name('dashboard');
    Route::get('/branches', BranchManagement::class)->name('branch.management');
    Route::get('/request', LetterRequestForm::class)->name('letter.request');
    Route::get('/history', LetterHistory::class)->name('letter.history');

    // Indonesian aliases
    Route::get('/pengajuan', LetterRequestForm::class)->name('letter.pengajuan');
    Route::get('/riwayat', LetterHistory::class)->name('letter.riwayat');
});
