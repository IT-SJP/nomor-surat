<?php

namespace App\Livewire;

use App\Models\Absen\Cabang;
use App\Models\Letter;
use App\Services\LetterImportService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
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

    public string $userBranch = 'SJP';

    public string $userBranchName = 'PT Selamat Jaya Persada';

    #[Url]
    public string $search = '';

    #[Url]
    public string $branch = '';

    #[Url]
    public string $date = '';

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
        $this->isKaryawan = ($sso['role'] ?? '') === 'karyawan';
        $this->isAdmin = ($sso['role'] ?? '') === 'admin';
        $this->userBranch = $sso['branch_code'] ?? 'SJP';
        $this->userBranchName = $sso['branch_name'] ?? ($sso['branch_code'] ?? 'PT Selamat Jaya Persada');

        if ($this->isKaryawan) {
            // Strictly enforce and lock to Karyawan's branch
            $this->branch = $this->userBranch;
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedBranch(): void
    {
        if ($this->isKaryawan) {
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
        if ($this->isAdmin) {
            $this->reset('branch');
        } else {
            $this->branch = $this->userBranch;
        }
        $this->resetPage();
    }

    public function viewLetter(int $id): void
    {
        $query = Letter::query()->where('id', $id);
        if ($this->isKaryawan) {
            $query->where('branch_code', $this->userBranch);
        }

        $this->selectedLetter = $query->first();
        if ($this->selectedLetter) {
            $this->showDetailModal = true;
        }
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedLetter = null;
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
        $result = $service->importFromPath($path, false);
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
        $effectiveBranch = $this->isKaryawan ? $this->userBranch : $this->branch;

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
                    $item->created_at->format('d/m/Y H:i'),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    public function render(): View
    {
        /** @var Collection<int, array{id: int|string|null, code: string, name: string}> $branches */
        $branches = Cabang::getActiveBranches();

        $effectiveBranch = $this->isKaryawan ? $this->userBranch : $this->branch;

        $letters = Letter::query()
            ->search($this->search)
            ->branch($effectiveBranch)
            ->date($this->date)
            ->latest('id')
            ->paginate($this->perPage);

        return view('livewire.letter-history', [
            'letters' => $letters,
            'branches' => $branches,
            'userBranchName' => $this->userBranchName,
        ]);
    }
}
