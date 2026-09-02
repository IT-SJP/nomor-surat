<?php

namespace App\Livewire;

use App\Models\Branch;
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

    public function mount(): void
    {
        $sso = session('auth_sso', []);
        if (($sso['role'] ?? '') !== 'admin') {
            $this->redirectRoute('dashboard', navigate: true);
        }
    }

    public function toggleActive($branchId): void
    {
        $branch = Branch::findOrFail($branchId);
        $branch->update(['is_active' => ! $branch->is_active]);

        session()->flash('status', 'Status cabang berhasil diperbarui.');
    }

    public function updateBranchCode($branchId, $newCode): void
    {
        $branch = Branch::findOrFail($branchId);

        $validated = validator(['code' => $newCode], [
            'code' => 'required|string|max:50|unique:branches,branch_code,'.$branch->id,
        ])->validate();

        $branch->update(['branch_code' => $validated['code']]);

        session()->flash('status', 'Kode surat cabang berhasil diperbarui.');
    }

    public function render(): View
    {
        $branches = Branch::query()
            ->orderBy('is_active', 'desc')
            ->orderBy('name', 'asc')
            ->paginate(15);

        return view('livewire.branch-management', [
            'branches' => $branches,
        ]);
    }
}
