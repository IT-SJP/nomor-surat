<?php

namespace App\Livewire;

use App\Models\Absen\Cabang;
use App\Models\Absen\Karyawan;
use App\Models\Letter;
use App\Services\LetterNumberService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Pengajuan Nomor Surat')]
class LetterRequestForm extends Component
{
    // SSO Context
    public bool $isKaryawan = false;

    public bool $isAdmin = false;

    // Employee Search / Autocomplete (Admin mode)
    public string $employeeSearch = '';

    /** @var array<int, array{id: int|string, nik: string, name: string, branch_id: int|string|null, branch_code: string, branch_name: string, email: string|null, phone: string|null, position: string|null}> */
    public array $employeeResults = [];

    /** @var array{id: int|string, nik: string, name: string, branch_id: int|string|null, branch_code: string, branch_name: string, email: string|null, phone: string|null, position: string|null}|null */
    public ?array $selectedEmployee = null;

    public bool $isBranchLocked = false;

    // Form inputs
    #[Validate('required|string|max:30')]
    public string $branch_code = 'SJP';

    public string $branch_name = '';

    #[Validate('required|string|min:2|max:100')]
    public string $target_code = '';

    #[Validate('required|integer|between:1,12')]
    public int $month;

    #[Validate('required|integer|min:2020|max:2099')]
    public int $year;

    #[Validate('required|string|min:3|max:255')]
    public string $subject = '';

    #[Validate('required|string|min:3')]
    public string $purpose = '';

    #[Validate('nullable|string|max:255')]
    public string $archive_location = '';

    #[Validate('nullable|string|max:100')]
    public string $requestor_department = '';

    #[Validate('nullable|string|max:100')]
    public string $requestor_position = '';

    #[Validate('required|string|min:2|max:255')]
    public string $requestor_name = '';

    #[Validate('nullable|email|max:255')]
    public string $requestor_email = '';

    #[Validate('nullable|string|max:50')]
    public string $requestor_phone = '';

    // Success State Modal
    public ?Letter $createdLetter = null;

    public bool $showSuccessModal = false;

    public function mount(?string $karyawan_nik = null): void
    {
        $this->month = (int) date('n');
        $this->year = (int) date('Y');

        $sso = session('auth_sso', []);
        $this->isKaryawan = ($sso['role'] ?? '') === 'karyawan';
        $this->isAdmin = ($sso['role'] ?? '') === 'admin';

        if ($this->isKaryawan) {
            // Auto-lock branch and requestor fields to logged-in Karyawan
            $this->requestor_name = $sso['name'] ?? '';
            $this->requestor_department = $sso['department_name'] ?? '';
            $this->requestor_position = $sso['position_name'] ?? '';

            // Resolve branch_code from local Branch table using raw hr_code
            $rawHrCode = (string) ($sso['raw_branch_code'] ?? $sso['branch_code'] ?? '');
            $localBranch = Branch::where('hr_code', $rawHrCode)->first();
            $this->branch_code = $localBranch?->branch_code ?? (string) ($sso['branch_code'] ?? '');
            $this->branch_name = $localBranch?->name ?? (string) ($sso['branch_name'] ?? "Cabang {$rawHrCode}");
            $this->isBranchLocked = true;
        } else {
            // Admin mode default
            $activeBranches = Cabang::getActiveBranches();
            if ($activeBranches->isNotEmpty()) {
                $this->branch_code = $activeBranches->first()['code'] ?? '';
                $this->branch_name = $activeBranches->first()['name'] ?? '';
            }

            if ($karyawan_nik) {
                $employees = Karyawan::searchEmployees($karyawan_nik, 1);
                if ($employees->isNotEmpty()) {
                    $this->selectEmployee($employees->first());
                }
            }
        }
    }

    public function updatedEmployeeSearch(string $value): void
    {
        if ($this->isKaryawan) {
            return;
        }

        if (strlen(trim($value)) >= 2) {
            $this->employeeResults = Karyawan::searchEmployees($value, 8)->toArray();
        } else {
            $this->employeeResults = [];
        }
    }

    /**
     * @param  array{id: int|string, nik: string, name: string, branch_id: int|string|null, branch_code: string, branch_name: string, email: string|null, phone: string|null, position: string|null}  $employee
     */
    public function selectEmployee(array $employee): void
    {
        if ($this->isKaryawan) {
            return;
        }

        $this->selectedEmployee = $employee;
        $this->requestor_name = $employee['name'];
        $this->requestor_department = $employee['department'] ?? '';
        $this->requestor_position = $employee['position'] ?? '';
        $this->requestor_email = $employee['email'] ?? '';
        $this->requestor_phone = $employee['phone'] ?? '';

        if (! empty($employee['branch_code'])) {
            $this->branch_code = $employee['branch_code'];
            $this->branch_name = $employee['branch_name'];
            $this->isBranchLocked = true;
        }

        $this->employeeSearch = '';
        $this->employeeResults = [];
    }

    public function clearSelectedEmployee(): void
    {
        if ($this->isKaryawan) {
            return;
        }

        $this->selectedEmployee = null;
        $this->isBranchLocked = false;
        $this->requestor_nik = '';
    }

    public function updatedBranchCode(string $code): void
    {
        if ($this->isKaryawan) {
            return;
        }

        $branches = Cabang::getActiveBranches();
        $matched = $branches->firstWhere('code', $code);
        if ($matched) {
            $this->branch_name = $matched['name'];
        }
    }

    public function submit(LetterNumberService $service): void
    {
        if (empty($this->branch_code)) {
            $this->addError('branch_code', 'Kode surat resmi untuk cabang ini belum diset. Harap atur kode surat pada menu Pengaturan Cabang terlebih dahulu.');

            return;
        }

        $this->validate();

        $this->createdLetter = $service->createLetter([
            'branch_code' => $this->branch_code,
            'branch_name' => $this->branch_name,
            'target_code' => $this->target_code,
            'month' => $this->month,
            'year' => $this->year,
            'subject' => $this->subject,
            'purpose' => $this->purpose,
            'archive_location' => $this->archive_location,
            'requestor_department' => $this->requestor_department,
            'requestor_position' => $this->requestor_position,
            'requestor_name' => $this->requestor_name,
            'requestor_email' => $this->requestor_email,
            'requestor_phone' => $this->requestor_phone,
        ]);

        $this->showSuccessModal = true;
    }

    public function createAnother(): void
    {
        $this->showSuccessModal = false;
        $this->createdLetter = null;
        $this->target_code = '';
        $this->subject = '';
        $this->purpose = '';
        $this->archive_location = '';
    }

    public function render(LetterNumberService $service): View
    {
        /** @var Collection<int, array{id: int|string|null, code: string, name: string}> $branches */
        $branches = Cabang::getActiveBranches();

        $previewNumber = $this->branch_code && $this->month && $this->year
            ? $service->previewNextNumber($this->branch_code, $this->month, $this->year)
            : '-';

        return view('livewire.letter-request-form', [
            'branches' => $branches,
            'previewNumber' => $previewNumber,
            'romanMonths' => LetterNumberService::ROMAN_MONTHS,
        ]);
    }
}
