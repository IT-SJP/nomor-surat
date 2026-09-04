<?php

use App\Http\Controllers\SsoAuthController;
use App\Livewire\BranchManagement;
use App\Livewire\DashboardAdmin;
use App\Livewire\LetterHistory;
use App\Livewire\LetterRequestForm;
use App\Livewire\TargetManagement;
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

    Route::get('/dashboard', DashboardAdmin::class)->name('dashboard');
    Route::get('/branches', BranchManagement::class)->name('branch.management');
    Route::get('/targets', TargetManagement::class)->name('target.management');
    Route::get('/request', LetterRequestForm::class)->name('letter.request');
    Route::get('/history', LetterHistory::class)->name('letter.history');
});
