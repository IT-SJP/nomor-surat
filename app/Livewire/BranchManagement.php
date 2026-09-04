<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Letter;
use Illuminate\Contracts\View\View;
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

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function mount(): void
    {
        $sso = session('auth_sso', []);
        if (($sso['role'] ?? '') !== 'admin') {
            $this->redirectRoute('dashboard', navigate: true);
        }
    }

    public function toggleActive(int $branchId): void
    {
        /** @var Branch $branch */
        $branch = Branch::findOrFail($branchId);
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
        /** @var Branch $branch */
        $branch = Branch::findOrFail($branchId);

        $validated = validator(['code' => $newCode], [
            'code' => 'required|string|max:50|unique:branches,branch_code,'.$branch->id,
        ])->validate();

        $branch->update(['branch_code' => $validated['code']]);

        session()->flash('status', 'Kode surat cabang berhasil diperbarui.');

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Kode Surat Diperbarui',
            'message' => "Kode surat {$branch->name} berhasil diubah menjadi '{$validated['code']}'.",
        ]);
    }

    public function deleteBranch(int $branchId): void
    {
        /** @var Branch $branch */
        $branch = Branch::findOrFail($branchId);

        // Validasi apakah cabang sudah memiliki surat keluar yang pernah diterbitkan
        $lettersCount = Letter::where('branch_code', $branch->branch_code)
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
        $branches = Branch::query()
            ->orderBy('is_active', 'desc')
            ->orderBy('name', 'asc')
            ->paginate($this->perPage);

        return view('livewire.branch-management', [
            'branches' => $branches,
        ]);
    }
}
