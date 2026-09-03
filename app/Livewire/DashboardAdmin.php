<?php

namespace App\Livewire;

use App\Models\Absen\Cabang;
use App\Models\Letter;
use App\Services\LetterNumberService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard Statistik Surat')]
class DashboardAdmin extends Component
{
    public function mount(): void
    {
        $sso = session('auth_sso', []);
        if (($sso['role'] ?? '') === 'karyawan') {
            $this->redirectRoute('letter.request', navigate: true);
        }
    }

    public function render(LetterNumberService $service): View
    {
        $branches = Cabang::getActiveBranches();
        $branchStats = $service->getBranchStats($branches);

        $totalLetters = Letter::count();
        $lettersThisMonth = Letter::where('year', date('Y'))
            ->where('month', date('n'))
            ->count();
        $lettersToday = Letter::whereDate('created_at', today())->count();
        $totalBranchesCount = $branches->count();

        $recentLetters = Letter::query()
            ->latest('id')
            ->limit(8)
            ->get();

        return view('livewire.dashboard-admin', [
            'branches' => $branches,
            'branchStats' => $branchStats,
            'totalLetters' => $totalLetters,
            'lettersThisMonth' => $lettersThisMonth,
            'lettersToday' => $lettersToday,
            'totalBranchesCount' => $totalBranchesCount,
            'recentLetters' => $recentLetters,
        ]);
    }
}
