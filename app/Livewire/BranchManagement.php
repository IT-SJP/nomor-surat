<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Letter;
use App\Services\LetterNumberService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Pengaturan Cabang')]
class BranchManagement extends Component
{
    use WithPagination;

    public int $perPage = 15;

    public bool $canManageBranches = true;

    public bool $isAdminCabang = false;

    public ?string $adminBranchHrCode = null;

    public ?string $adminBranchCode = null;

    public ?string $adminBranchName = null;

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function mount(): void
    {
        $sso = session('auth_sso', []);
        $role = strtolower((string) ($sso['role'] ?? ''));
        $adminRole = strtolower((string) ($sso['admin_role'] ?? ''));
        $type = strtolower((string) ($sso['type'] ?? ''));
        $position = strtolower((string) ($sso['position_name'] ?? ''));

        $isAdmin = in_array($role, ['admin', 'administrator', 'admin cabang', 'hrd'])
            || in_array($adminRole, ['admin', 'administrator', 'admin cabang', 'hrd'])
            || in_array($type, ['admin', 'administrator', 'admin cabang', 'hrd'])
            || str_contains($role, 'admin');

        if (! $isAdmin) {
            $this->redirectRoute('dashboard', navigate: true);

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
            $this->canManageBranches = true; // Admin cabang tetap bisa edit dan hapus cabangnya sendiri
        } else {
            $this->canManageBranches = $this->checkCanManageBranches($sso);
        }
    }

    /**
     * Determine if current SSO user has permissions to modify branch settings.
     * Hanya role 'administrator' dan 'hrd' yang dapat mengatur cabang.
     * Role lain (seperti 'admin cabang', 'owner', 'staff it', 'HR Payroll', dll) hanya dapat melihat saja (read-only).
     */
    protected function checkCanManageBranches(array $sso): bool
    {
        $allowedRoles = ['administrator', 'hrd'];

        $adminRole = strtolower((string) ($sso['admin_role'] ?? ''));
        if (in_array($adminRole, $allowedRoles, true)) {
            return true;
        }

        $role = strtolower((string) ($sso['role'] ?? ''));
        if (in_array($role, $allowedRoles, true)) {
            return true;
        }

        $type = strtolower((string) ($sso['type'] ?? ''));
        if (in_array($type, $allowedRoles, true)) {
            return true;
        }

        // Jika session role adalah 'admin' umum dan belum ada admin_role, cek langsung ke database absen_db
        if (($role === 'admin' || $type === 'admin') && empty($adminRole)) {
            $email = $sso['email'] ?? null;
            $userId = $sso['id'] ?? null;

            if ($email || $userId) {
                try {
                    $userQuery = DB::connection('absen_db')->table('users');
                    if ($userId) {
                        $userQuery->where('users.id', $userId);
                    } elseif ($email) {
                        $userQuery->where('users.email', $email);
                    }
                    $dbRole = $userQuery
                        ->join('model_has_roles', DB::raw('users.id::varchar'), '=', 'model_has_roles.model_id')
                        ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                        ->where('model_has_roles.model_type', 'App\\Models\\User')
                        ->value('roles.name');

                    if ($dbRole) {
                        $resolvedRole = strtolower($dbRole);
                        session(['auth_sso.admin_role' => $resolvedRole]);

                        return in_array($resolvedRole, $allowedRoles, true);
                    }
                } catch (\Throwable $e) {
                    // Fallback jika koneksi absen_db tidak tersedia
                }
            }
        }

        return false;
    }

    /**
     * Cek apakah cabang yang sedang diakses adalah cabang milik admin cabang.
     */
    protected function isOwnBranch(Branch $branch): bool
    {
        if (! empty($this->adminBranchHrCode) && $branch->hr_code === $this->adminBranchHrCode) {
            return true;
        }

        if (! empty($this->adminBranchCode) && $branch->branch_code === $this->adminBranchCode) {
            return true;
        }

        if (! empty($this->adminBranchName) && strcasecmp($branch->name, $this->adminBranchName) === 0) {
            return true;
        }

        return false;
    }

    public function toggleActive(int $branchId): void
    {
        if (! $this->canManageBranches) {
            return;
        }

        /** @var Branch $branch */
        $branch = Branch::findOrFail($branchId);

        // Jika admin cabang, pastikan hanya dapat mengubah cabang miliknya sendiri
        if ($this->isAdminCabang && ! $this->isOwnBranch($branch)) {
            return;
        }

        $branch->update(['is_active' => ! $branch->is_active]);

        $statusText = $branch->is_active ? 'diaktifkan' : 'dinonaktifkan';
        session()->flash('status', "Status cabang {$branch->name} berhasil {$statusText}.");

        $this->dispatch('toast', [
            'type' => $branch->is_active ? 'success' : 'warning',
            'title' => 'Status Cabang',
            'message' => "Cabang {$branch->name} berhasil {$statusText}.",
        ]);
    }

    public function updateBranchCode(int $branchId, string $newCode): void
    {
        if (! $this->canManageBranches) {
            return;
        }

        /** @var Branch $branch */
        $branch = Branch::findOrFail($branchId);

        // Jika admin cabang, pastikan hanya dapat mengubah cabang miliknya sendiri
        if ($this->isAdminCabang && ! $this->isOwnBranch($branch)) {
            return;
        }

        $this->resetValidation();

        $cleanNewCode = strtoupper(trim($newCode));

        if ($cleanNewCode === '') {
            $errorMsg = 'Kode surat resmi cabang wajib diisi.';
            $this->addError('code_'.$branchId, $errorMsg);
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Validasi Gagal',
                'message' => $errorMsg,
            ]);

            return;
        }

        if (strlen($cleanNewCode) > 50) {
            $errorMsg = 'Kode surat resmi cabang maksimal 50 karakter.';
            $this->addError('code_'.$branchId, $errorMsg);
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Validasi Gagal',
                'message' => $errorMsg,
            ]);

            return;
        }

        // Cek duplikasi terhadap cabang lain
        $existing = Branch::where('branch_code', $cleanNewCode)
            ->where('id', '!=', $branch->id)
            ->first();

        if ($existing) {
            $errorMsg = "Kode surat '{$cleanNewCode}' sudah digunakan oleh cabang {$existing->name}.";
            $this->addError('code_'.$branchId, $errorMsg);
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Kode Sudah Digunakan',
                'message' => $errorMsg,
            ]);

            return;
        }

        $oldCode = $branch->branch_code;

        DB::transaction(function () use ($branch, $cleanNewCode, $oldCode) {
            $branch->update(['branch_code' => $cleanNewCode]);

            // Cascade update all existing letters belonging to this branch
            $letters = Letter::query()
                ->where('branch_id', $branch->id)
                ->when($oldCode, fn ($q) => $q->orWhere('branch_code', $oldCode))
                ->when($branch->hr_code, fn ($q) => $q->orWhere('branch_code', $branch->hr_code))
                ->get();

            foreach ($letters as $letter) {
                $newRef = LetterNumberService::regenerateReferenceNumber($letter, $cleanNewCode);
                $letter->update([
                    'branch_id' => $branch->id,
                    'branch_code' => $cleanNewCode,
                    'branch_name' => $branch->name,
                    'reference_number' => $newRef,
                ]);
            }
        });

        session()->flash('status', 'Kode surat cabang berhasil diperbarui.');

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Kode Surat Diperbarui',
            'message' => "Kode surat {$branch->name} berhasil diubah menjadi '{$cleanNewCode}'.",
        ]);
    }

    public function deleteBranch(int $branchId): void
    {
        if (! $this->canManageBranches) {
            return;
        }

        /** @var Branch $branch */
        $branch = Branch::findOrFail($branchId);

        // Jika admin cabang, pastikan hanya dapat menghapus cabang miliknya sendiri
        if ($this->isAdminCabang && ! $this->isOwnBranch($branch)) {
            return;
        }

        // Validasi apakah cabang sudah memiliki surat keluar yang pernah diterbitkan
        $lettersCount = Letter::where('branch_id', $branch->id)
            ->orWhere('branch_code', $branch->branch_code)
            ->orWhere('branch_code', $branch->hr_code)
            ->count();

        if ($lettersCount > 0) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Tidak Dapat Dihapus',
                'message' => "Cabang {$branch->name} tidak dapat dihapus karena sudah memiliki {$lettersCount} arsip nomor surat. Silakan nonaktifkan cabang sebagai gantinya.",
            ]);

            return;
        }

        $branchName = $branch->name;
        $branch->delete();

        session()->flash('status', "Cabang {$branchName} berhasil dihapus dari sistem nomor surat.");

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Cabang Dihapus',
            'message' => "Cabang {$branchName} berhasil dihapus dari sistem nomor surat (data di database Absenku tetap aman).",
        ]);
    }

    public function render(): View
    {
        $query = Branch::query();

        // Khusus admin cabang, yang muncul di tabel hanya cabang mereka sendiri
        if ($this->isAdminCabang) {
            $query->where(function ($q) {
                if (! empty($this->adminBranchHrCode)) {
                    $q->where('hr_code', $this->adminBranchHrCode);
                }
                if (! empty($this->adminBranchCode)) {
                    $q->orWhere('branch_code', $this->adminBranchCode);
                }
                if (! empty($this->adminBranchName)) {
                    $operator = $q->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
                    $q->orWhere('name', $operator, '%'.$this->adminBranchName.'%');
                }
            });
        }

        $branches = $query
            ->orderBy('is_active', 'desc')
            ->orderBy('name', 'asc')
            ->paginate($this->perPage);

        return view('livewire.branch-management', [
            'branches' => $branches,
        ]);
    }
}
