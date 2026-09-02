<?php

namespace App\Livewire;

use App\Models\Absen\Cabang;
use App\Models\Letter;
use App\Services\LetterNumberService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
#[Title('Riwayat Nomor Surat')]
class LetterHistory extends Component
{
    use WithPagination;

    public bool $isKaryawan = false;

    public bool $isAdmin = false;

    public string $userBranch = 'SJP';

    #[Url]
    public string $search = '';

    #[Url]
    public string $branch = '';

    #[Url]
    public string $year = '';

    #[Url]
    public string $month = '';

    public int $perPage = 15;

    public ?Letter $selectedLetter = null;

    public bool $showDetailModal = false;

    public function mount(): void
    {
        $sso = session('auth_sso', []);
        $this->isKaryawan = ($sso['role'] ?? '') === 'karyawan';
        $this->isAdmin = ($sso['role'] ?? '') === 'admin';
        $this->userBranch = $sso['branch_code'] ?? 'SJP';

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

    public function updatedYear(): void
    {
        $this->resetPage();
    }

    public function updatedMonth(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'year', 'month']);
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

    public function exportCsv(): StreamedResponse
    {
        $effectiveBranch = $this->isKaryawan ? $this->userBranch : $this->branch;

        $letters = Letter::query()
            ->search($this->search)
            ->branch($effectiveBranch)
            ->period(
                $this->year ? (int) $this->year : null,
                $this->month ? (int) $this->month : null
            )
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
                'NIK Requestor',
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
                    $item->purpose,
                    $item->archive_location ?? '-',
                    $item->requestor_name,
                    $item->requestor_nik ?? '-',
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
            ->period(
                $this->year ? (int) $this->year : null,
                $this->month ? (int) $this->month : null
            )
            ->latest('id')
            ->paginate($this->perPage);

        return view('livewire.letter-history', [
            'letters' => $letters,
            'branches' => $branches,
            'romanMonths' => LetterNumberService::ROMAN_MONTHS,
        ]);
    }
}
