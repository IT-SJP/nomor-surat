<?php

namespace App\Livewire;

use App\Models\Letter;
use App\Models\LetterTarget;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Daftar & Pengaturan Tujuan Surat')]
class TargetManagement extends Component
{
    use WithPagination;

    public bool $isAdmin = false;

    public string $search = '';

    public string $statusFilter = 'all'; // 'all', 'active', 'inactive'

    // Form Modal State (Admin only)
    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public ?int $editingTargetId = null;

    public string $code = '';

    public string $name = '';

    public string $description = '';

    public bool $is_active = true;

    public function mount(): void
    {
        $sso = session('auth_sso', []);
        $this->isAdmin = ($sso['role'] ?? '') === 'admin';
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        if (! $this->isAdmin) {
            return;
        }

        $this->resetValidation();
        $this->code = '';
        $this->name = '';
        $this->description = '';
        $this->is_active = true;
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetValidation();
    }

    public function createTarget(): void
    {
        if (! $this->isAdmin) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Akses Ditolak',
                'message' => 'Hanya admin yang memiliki akses untuk menambah tujuan surat.',
            ]);

            return;
        }

        $this->code = strtoupper(trim($this->code));

        $validated = $this->validate([
            'code' => 'required|string|min:2|max:30|unique:letter_targets,code',
            'name' => 'required|string|min:2|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ], [
            'code.required' => 'Kode tujuan baku wajib diisi.',
            'code.unique' => 'Kode tujuan ini sudah digunakan.',
            'name.required' => 'Nama tujuan surat wajib diisi.',
        ]);

        $created = LetterTarget::create([
            'code' => $this->code,
            'name' => trim($validated['name']),
            'description' => ! empty($validated['description']) ? trim($validated['description']) : null,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        $this->showCreateModal = false;
        session()->flash('status', "Tujuan baku '{$created->code} - {$created->name}' berhasil ditambahkan.");

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Tujuan Berhasil Ditambahkan',
            'message' => "Tujuan baku {$created->code} ({$created->name}) berhasil disimpan.",
        ]);
    }

    public function openEditModal(int $targetId): void
    {
        if (! $this->isAdmin) {
            return;
        }

        /** @var LetterTarget $target */
        $target = LetterTarget::findOrFail($targetId);

        $this->resetValidation();
        $this->editingTargetId = $target->id;
        $this->code = $target->code;
        $this->name = $target->name;
        $this->description = (string) ($target->description ?? '');
        $this->is_active = $target->is_active;
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingTargetId = null;
        $this->resetValidation();
    }

    public function updateTarget(): void
    {
        if (! $this->isAdmin || ! $this->editingTargetId) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Akses Ditolak',
                'message' => 'Hanya admin yang memiliki akses untuk mengubah tujuan surat.',
            ]);

            return;
        }

        /** @var LetterTarget $target */
        $target = LetterTarget::findOrFail($this->editingTargetId);

        $this->code = strtoupper(trim($this->code));

        $validated = $this->validate([
            'code' => 'required|string|min:2|max:30|unique:letter_targets,code,'.$target->id,
            'name' => 'required|string|min:2|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ], [
            'code.required' => 'Kode tujuan baku wajib diisi.',
            'code.unique' => 'Kode tujuan ini sudah digunakan oleh tujuan lain.',
            'name.required' => 'Nama tujuan surat wajib diisi.',
        ]);

        $target->update([
            'code' => $this->code,
            'name' => trim($validated['name']),
            'description' => ! empty($validated['description']) ? trim($validated['description']) : null,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        $this->showEditModal = false;
        $this->editingTargetId = null;

        session()->flash('status', "Tujuan baku '{$target->code}' berhasil diperbarui.");

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Tujuan Diperbarui',
            'message' => "Tujuan baku {$target->code} ({$target->name}) berhasil diperbarui.",
        ]);
    }

    public function toggleActive(int $targetId): void
    {
        if (! $this->isAdmin) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Akses Ditolak',
                'message' => 'Hanya admin yang dapat mengaktifkan atau menonaktifkan tujuan.',
            ]);

            return;
        }

        /** @var LetterTarget $target */
        $target = LetterTarget::findOrFail($targetId);
        $target->update(['is_active' => ! $target->is_active]);

        $statusText = $target->is_active ? 'diaktifkan' : 'dinonaktifkan';
        session()->flash('status', "Tujuan {$target->code} ({$target->name}) berhasil {$statusText}.");

        $this->dispatch('toast', [
            'type' => $target->is_active ? 'success' : 'warning',
            'title' => 'Status Tujuan Diubah',
            'message' => "Tujuan {$target->code} berhasil {$statusText}.",
        ]);
    }

    public function deleteTarget(int $targetId): void
    {
        if (! $this->isAdmin) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Akses Ditolak',
                'message' => 'Hanya admin yang dapat menghapus tujuan surat.',
            ]);

            return;
        }

        /** @var LetterTarget $target */
        $target = LetterTarget::findOrFail($targetId);

        // Periksa apakah kode atau nama tujuan ini sudah pernah digunakan dalam nomor surat
        $operator = Letter::query()->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $lettersCount = Letter::where(function ($query) use ($target, $operator) {
            $query->where('target_code', $target->code)
                ->orWhere('target_code', $target->name)
                ->orWhere('reference_number', $operator, "%/{$target->code}/%");
        })->count();

        if ($lettersCount > 0) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Tidak Dapat Dihapus',
                'message' => "Tujuan {$target->code} ({$target->name}) tidak dapat dihapus karena sudah tercatat dalam {$lettersCount} arsip nomor surat. Silakan nonaktifkan tujuan sebagai gantinya.",
            ]);

            return;
        }

        $code = $target->code;
        $name = $target->name;
        $target->delete();

        session()->flash('status', "Tujuan {$code} ({$name}) berhasil dihapus dari sistem.");

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Tujuan Dihapus',
            'message' => "Tujuan {$code} berhasil dihapus.",
        ]);
    }

    public function render(): View
    {
        $query = LetterTarget::query();

        if (! empty(trim($this->search))) {
            $query->search(trim($this->search));
        }

        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        $targets = $query
            ->orderBy('is_active', 'desc')
            ->orderBy('code', 'asc')
            ->paginate(15);

        $counts = [
            'all' => LetterTarget::count(),
            'active' => LetterTarget::where('is_active', true)->count(),
            'inactive' => LetterTarget::where('is_active', false)->count(),
        ];

        return view('livewire.target-management', [
            'targets' => $targets,
            'counts' => $counts,
        ]);
    }
}
