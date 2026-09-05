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
#[Title('Dashboard Admin')]
class DashboardAdmin extends Component
{
    public bool $isAdminCabang = false;

    public string $adminBranchCode = '';

    public string $adminBranchHrCode = '';

    public string $adminBranchName = '';

    public function mount(): void
    {
        $sso = session('auth_sso', []);
        $role = strtolower((string) ($sso['role'] ?? ''));
        $adminRole = strtolower((string) ($sso['admin_role'] ?? ''));
        $type = strtolower((string) ($sso['type'] ?? ''));
        $position = strtolower((string) ($sso['position_name'] ?? ''));

        if ($role === 'karyawan' || $type === 'karyawan') {
            $this->redirectRoute('letter.request', navigate: true);

            return;
        }

        $this->isAdminCabang = $adminRole === 'admin cabang'
            || $role === 'admin cabang'
            || $type === 'admin cabang'
            || str_contains($position, 'admin cabang');

        if ($this->isAdminCabang) {
            $this->adminBranchHrCode = (string) ($sso['raw_branch_code'] ?? $sso['branch_code'] ?? '');
            $this->adminBranchCode = (string) ($sso['branch_code'] ?? '');
            $this->adminBranchName = (string) ($sso['branch_name'] ?? '');
        }
    }

    public function render(LetterNumberService $service): View
    {
        $branches = Cabang::getActiveBranches();

        if ($this->isAdminCabang) {
            $branchCodes = array_values(array_unique(array_filter([$this->adminBranchCode, $this->adminBranchHrCode])));

            // Filter branch list to only their branch
            $branches = $branches->filter(function ($b) use ($branchCodes) {
                return (isset($b['code']) && in_array(strtoupper($b['code']), $branchCodes, true))
                    || (isset($b['hr_code']) && in_array(strtoupper($b['hr_code']), $branchCodes, true));
            });

            if ($branches->isEmpty() && $this->adminBranchCode) {
                $branches = collect([[
                    'id' => null,
                    'code' => $this->adminBranchCode,
                    'name' => $this->adminBranchName ?: $this->adminBranchCode,
                ]]);
            }

            $branchStats = $service->getBranchStats($branches);

            $totalLetters = Letter::whereIn('branch_code', $branchCodes)->count();
            $lettersThisMonth = Letter::whereIn('branch_code', $branchCodes)
                ->where('year', date('Y'))
                ->where('month', date('n'))
                ->count();
            $lettersToday = Letter::whereIn('branch_code', $branchCodes)
                ->whereDate('created_at', today())
                ->count();
            $totalBranchesCount = 1;

            $recentLetters = Letter::query()
                ->whereIn('branch_code', $branchCodes)
                ->latest('id')
                ->limit(8)
                ->get();
        } else {
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
        }

        return view('livewire.dashboard-admin', [
            'branches' => $branches,
            'branchStats' => $branchStats,
            'totalLetters' => $totalLetters,
            'lettersThisMonth' => $lettersThisMonth,
            'lettersToday' => $lettersToday,
            'totalBranchesCount' => $totalBranchesCount,
            'recentLetters' => $recentLetters,
            'isAdminCabang' => $this->isAdminCabang,
            'adminBranchCode' => $this->adminBranchCode,
            'adminBranchName' => $this->adminBranchName,
        ]);
    }
}
