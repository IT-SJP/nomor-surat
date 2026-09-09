<?php

namespace App\Livewire;

use App\Models\Absen\Cabang;
use App\Models\Letter;
use App\Services\LetterImportService;
use App\Services\LetterNumberService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
#[Title('Riwayat Nomor Surat')]
class LetterHistory extends Component
{
    use WithFileUploads;
    use WithPagination;

    public bool $isKaryawan = false;

    public bool $isAdmin = false;

    public bool $isAdminCabang = false;

    public string $adminBranchCode = '';

    public string $adminBranchHrCode = '';

    public string $adminBranchName = '';

    public string $userBranch = 'SJP';

    public string $userBranchName = 'PT Selamat Jaya Persada';

    #[Url]
    public string $search = '';

    #[Url]
    public string $branch = '';

    #[Url]
    public string $date = '';

    #[Url]
    public ?int $open = null;

    public int $perPage = 15;

    public ?Letter $selectedLetter = null;

    public bool $showDetailModal = false;

    public bool $showImportModal = false;

    /** @var mixed */
    public $csvFile = null;

    /** @var array<string, mixed> */
    public array $importResult = [];

    public function mount(): void
    {
        $sso = session('auth_sso', []);
        $role = strtolower((string) ($sso['role'] ?? ''));
        $adminRole = strtolower((string) ($sso['admin_role'] ?? ''));
        $type = strtolower((string) ($sso['type'] ?? ''));
        $position = strtolower((string) ($sso['position_name'] ?? ''));

        $this->isKaryawan = $role === 'karyawan' || $type === 'karyawan';
        $this->isAdmin = in_array($role, ['admin', 'administrator', 'admin cabang', 'hrd'])
            || in_array($adminRole, ['admin', 'administrator', 'admin cabang', 'hrd'])
            || in_array($type, ['admin', 'administrator', 'admin cabang', 'hrd'])
            || str_contains($role, 'admin');

        $this->isAdminCabang = $adminRole === 'admin cabang'
            || $role === 'admin cabang'
            || $type === 'admin cabang'
            || str_contains($position, 'admin cabang');

        $this->userBranch = (string) ($sso['branch_code'] ?? 'SJP');
        $this->userBranchName = (string) ($sso['branch_name'] ?? ($sso['branch_code'] ?? 'PT Selamat Jaya Persada'));

        if ($this->isAdminCabang) {
            $this->adminBranchHrCode = (string) ($sso['raw_branch_code'] ?? $sso['branch_code'] ?? '');
            $this->adminBranchCode = (string) ($sso['branch_code'] ?? '');
            $this->adminBranchName = (string) ($sso['branch_name'] ?? '');
            $this->branch = $this->adminBranchCode;
        } elseif ($this->isKaryawan) {
            // Strictly enforce and lock to Karyawan's branch
            $this->branch = $this->userBranch;
        }

        $openId = $this->open ?: request()->integer('open');
        if ($openId) {
            $this->viewLetter($openId);
        }
    }

    public function updatedOpen(): void
    {
        if ($this->open) {
            $this->viewLetter($this->open);
        } else {
            $this->closeDetailModal();
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedBranch(): void
    {
        if ($this->isAdminCabang) {
            $this->branch = $this->adminBranchCode;
        } elseif ($this->isKaryawan) {
            $this->branch = $this->userBranch;
        }
        $this->resetPage();
    }

    public function updatedDate(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'date']);
        if ($this->isAdminCabang) {
            $this->branch = $this->adminBranchCode;
        } elseif ($this->isKaryawan) {
            $this->branch = $this->userBranch;
        } else {
            $this->reset('branch');
        }
        $this->resetPage();
    }

    public function viewLetter(int $id): void
    {
        $query = Letter::query()->with(['parent', 'subLetters'])->where('id', $id);
        if ($this->isAdminCabang) {
            $query->where('branch_code', $this->adminBranchCode);
        } elseif ($this->isKaryawan) {
            $query->where('branch_code', $this->userBranch);
        }

        $this->selectedLetter = $query->first();
        if ($this->selectedLetter) {
            $this->showDetailModal = true;
            $this->open = $this->selectedLetter->id;
        } else {
            $this->open = null;
        }
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedLetter = null;
        $this->open = null;
    }

    public function addSubLetter(LetterNumberService $service): void
    {
        if (! $this->selectedLetter) {
            return;
        }

        // Sub-nomor selalu dibuat untuk surat induk
        $parent = $this->selectedLetter->parent_id ? $this->selectedLetter->parent : $this->selectedLetter;
        if (! $parent) {
            return;
        }

        $subLetter = $service->createNextSubLetter($parent);
        $this->selectedLetter = $parent->fresh(['parent', 'subLetters']);

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Sub-Nomor Ditambahkan!',
            'message' => "Sub-nomor registrasi {$subLetter->reference_number} berhasil dibuat.",
        ]);
    }

    public function deleteLetter(int $id): void
    {
        $query = Letter::query()->where('id', $id);
        if ($this->isAdminCabang) {
            $query->where('branch_code', $this->adminBranchCode);
        } elseif ($this->isKaryawan) {
            $query->where('branch_code', $this->userBranch);
        }

        /** @var Letter|null $letter */
        $letter = $query->first();
        if (! $letter) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Gagal',
                'message' => 'Nomor surat tidak ditemukan atau Anda tidak memiliki akses.',
            ]);

            return;
        }

        $refNumber = $letter->reference_number;
        $subCount = $letter->subLetters()->count();

        DB::transaction(function () use ($letter) {
            $letter->subLetters()->delete();
            $letter->delete();
        });

        if ($this->selectedLetter && $this->selectedLetter->id === $id) {
            $this->closeDetailModal();
        }

        $message = $subCount > 0
            ? "Nomor surat {$refNumber} beserta {$subCount} sub-nomor surat berhasil dibatalkan dan dihapus."
            : "Nomor surat {$refNumber} berhasil dibatalkan dan dihapus.";

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Nomor Surat Dibatalkan',
            'message' => $message,
        ]);
    }

    public function deleteSubLetter(int $subId): void
    {
        $query = Letter::query()->whereNotNull('parent_id')->where('id', $subId);
        if ($this->isAdminCabang) {
            $query->where('branch_code', $this->adminBranchCode);
        } elseif ($this->isKaryawan) {
            $query->where('branch_code', $this->userBranch);
        }

        /** @var Letter|null $subLetter */
        $subLetter = $query->first();
        if (! $subLetter) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Gagal',
                'message' => 'Sub-nomor surat tidak ditemukan atau Anda tidak memiliki akses.',
            ]);

            return;
        }

        $parentId = $subLetter->parent_id;

        // Validasi: Pembatalan sub-nomor harus dimulai dari angka terbesar/terakhir yang ditambahkan
        $latestSub = Letter::where('parent_id', $parentId)->orderByDesc('sub_number')->first();
        if ($latestSub && $subLetter->id !== $latestSub->id) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Urutan Tidak Sesuai',
                'message' => 'Pembatalan sub-nomor harus dimulai dari angka terbesar/terakhir yang ditambahkan.',
            ]);

            return;
        }

        $refNumber = $subLetter->reference_number;
        $subLetter->delete();

        if ($this->selectedLetter) {
            if ($this->selectedLetter->id === $parentId) {
                $this->selectedLetter->refresh();
                $this->selectedLetter->load(['parent', 'subLetters']);
            } elseif ($this->selectedLetter->id === $subId) {
                $this->closeDetailModal();
            }
        }

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Sub-Nomor Dibatalkan',
            'message' => "Sub-nomor surat {$refNumber} berhasil dibatalkan dan dihapus.",
        ]);
    }

    public function openImportModal(): void
    {
        if (! $this->isAdmin) {
            abort(403, 'Hanya administrator yang dapat mengimpor file CSV.');
        }

        $this->reset(['csvFile', 'importResult']);
        $this->resetValidation('csvFile');
        $this->showImportModal = true;
    }

    public function closeImportModal(): void
    {
        $this->showImportModal = false;
        $this->reset(['csvFile', 'importResult']);
        $this->resetValidation('csvFile');
    }

    public function importCsv(LetterImportService $service): void
    {
        if (! $this->isAdmin) {
            abort(403, 'Hanya administrator yang dapat mengimpor file CSV.');
        }

        $this->validate([
            'csvFile' => 'required|file|mimes:csv,txt|max:10240',
        ], [
            'csvFile.required' => 'Pilih file CSV yang ingin diimport.',
            'csvFile.file' => 'File tidak valid.',
            'csvFile.mimes' => 'Format file harus berupa CSV (.csv) atau TXT (.txt).',
            'csvFile.max' => 'Ukuran file maksimal adalah 10 MB.',
        ]);

        $path = $this->csvFile->getRealPath();

        $allowedBranch = $this->isAdminCabang
            ? array_values(array_unique(array_filter([$this->adminBranchCode, $this->adminBranchHrCode])))
            : null;

        $result = $service->importFromPath($path, false, $allowedBranch);
        $this->importResult = $result;

        $this->dispatch('toast', [
            'type' => $result['success'] ? 'success' : 'warning',
            'title' => 'Import CSV Selesai',
            'message' => "Berhasil mengimpor {$result['imported_count']} nomor surat.",
        ]);

        $this->resetPage();
        $this->reset('csvFile');
    }

    public function exportCsv(): StreamedResponse
    {
        $effectiveBranch = $this->isAdminCabang
            ? $this->adminBranchCode
            : ($this->isKaryawan ? $this->userBranch : $this->branch);

        $letters = Letter::query()
            ->search($this->search)
            ->branch($effectiveBranch)
            ->date($this->date)
            ->latest('id')
            ->get();

        $filename = 'riwayat-surat-'.($effectiveBranch ?: 'all').'-'.date('Y-m-d-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($letters) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            fputcsv($handle, [
                'No',
                'Nomor Surat',
                'Kode Cabang',
                'Kode Tujuan',
                'Bulan Romawi',
                'Tahun',
                'Perihal',
                'Tujuan',
                'Letak Arsip',
                'Nama Requestor',
                'Email',
                'No Telepon',
                'Waktu Input',
            ]);

            foreach ($letters as $index => $item) {
                fputcsv($handle, [
                    $index + 1,
                    $item->reference_number,
                    $item->branch_code,
                    $item->target_code,
                    $item->month_roman,
                    $item->year,
                    $item->subject,
                    $item->purpose ?: '-',
                    $item->archive_location ?? '-',
                    $item->requestor_name,
                    $item->requestor_email ?? '-',
                    $item->requestor_phone ?? '-',
                    $item->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i'),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    public function render(): View
    {
        /** @var Collection<int, array{id: int|string|null, code: string, name: string}> $branches */
        $branches = Cabang::getActiveBranches();

        if ($this->isAdminCabang) {
            $effectiveBranch = $this->adminBranchCode;
            $branches = $branches->filter(function ($b) {
                return (isset($b['code']) && strtoupper($b['code']) === strtoupper($this->adminBranchCode))
                    || (isset($b['hr_code']) && strtoupper($b['hr_code']) === strtoupper($this->adminBranchHrCode));
            });
            if ($branches->isEmpty() && $this->adminBranchCode) {
                $branches = collect([[
                    'id' => null,
                    'code' => $this->adminBranchCode,
                    'name' => $this->adminBranchName ?: $this->adminBranchCode,
                ]]);
            }
        } elseif ($this->isKaryawan) {
            $effectiveBranch = $this->userBranch;
        } else {
            $effectiveBranch = $this->branch;
        }

        $letters = Letter::query()
            ->whereNull('parent_id')
            ->with('subLetters')
            ->withCount('subLetters')
            ->when($this->search, function ($q) {
                $q->where(function ($subQ) {
                    $subQ->search($this->search)
                        ->orWhereHas('subLetters', function ($childQ) {
                            $childQ->search($this->search);
                        });
                });
            })
            ->branch($effectiveBranch)
            ->date($this->date)
            ->latest('id')
            ->paginate($this->perPage);

        return view('livewire.letter-history', [
            'letters' => $letters,
            'branches' => $branches,
            'userBranchName' => $this->userBranchName,
            'isAdminCabang' => $this->isAdminCabang,
            'adminBranchCode' => $this->adminBranchCode,
            'adminBranchName' => $this->adminBranchName,
        ]);
    }
}
