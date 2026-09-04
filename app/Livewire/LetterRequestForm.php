<?php

namespace App\Livewire;

use App\Models\Absen\Cabang;
use App\Models\Absen\Karyawan;
use App\Models\Branch;
use App\Models\Letter;
use App\Models\LetterTarget;
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

    /** @var array<int, array{id: string, name: string, branch_id: int|string|null, branch_code: string, branch_name: string, email: string|null, phone: string|null, position: string|null, department?: string|null}> */
    public array $employeeResults = [];

    /** @var array{id: string, name: string, branch_id: int|string|null, branch_code: string, branch_name: string, email: string|null, phone: string|null, position: string|null, department?: string|null}|null */
    public ?array $selectedEmployee = null;

    public bool $isBranchLocked = false;

    public bool $isEmailLocked = false;

    public bool $isPhoneLocked = false;

    public bool $isDepartmentLocked = false;

    public bool $isPositionLocked = false;

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

    #[Validate('nullable|string|max:1000')]
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
            $this->requestor_email = (string) ($sso['email'] ?? '');
            $this->requestor_phone = (string) ($sso['phone'] ?? $sso['no_hp'] ?? '');

            // Fallback sinkronisasi dari database Absenku SJP jika email / no_hp belum terisi di session
            if ((empty($this->requestor_email) || empty($this->requestor_phone)) && ! empty($sso['nik'])) {
                try {
                    $karyawanRecord = Karyawan::where('nik', $sso['nik'])->first();
                    if ($karyawanRecord) {
                        if (empty($this->requestor_email) && ! empty($karyawanRecord->email)) {
                            $this->requestor_email = (string) $karyawanRecord->email;
                            session()->put('auth_sso.email', $this->requestor_email);
                        }
                        if (empty($this->requestor_phone) && ! empty($karyawanRecord->no_hp)) {
                            $this->requestor_phone = (string) $karyawanRecord->no_hp;
                            session()->put('auth_sso.phone', $this->requestor_phone);
                        }
                    }
                } catch (\Throwable $e) {
                    // Abaikan jika koneksi absen_db offline/tidak tersedia
                }
            }

            // Resolve branch_code from local Branch table using raw hr_code
            $rawHrCode = (string) ($sso['raw_branch_code'] ?? $sso['branch_code'] ?? '');
            $localBranch = Branch::where('hr_code', $rawHrCode)->first();
            $this->branch_code = $localBranch?->branch_code ?? (string) ($sso['branch_code'] ?? '');
            $this->branch_name = $localBranch?->name ?? (string) ($sso['branch_name'] ?? "Cabang {$rawHrCode}");
            $this->isBranchLocked = true;

            // Kunci input jika datanya didapat langsung dari profil karyawan, atau izinkan edit manual jika kosong
            $this->isEmailLocked = ! empty(trim($this->requestor_email));
            $this->isPhoneLocked = ! empty(trim($this->requestor_phone));
            $this->isDepartmentLocked = ! empty(trim($this->requestor_department));
            $this->isPositionLocked = ! empty(trim($this->requestor_position));
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
            } else {
                $this->employeeResults = Karyawan::searchEmployees('', 8)->toArray();
            }
        }
    }

    public function updatedEmployeeSearch(string $value): void
    {
        if ($this->isKaryawan) {
            return;
        }

        $query = trim($value);
        if ($query !== '') {
            $this->employeeResults = Karyawan::searchEmployees($query, 12)->toArray();
        } else {
            $this->employeeResults = Karyawan::searchEmployees('', 8)->toArray();
        }
    }

    /**
     * @param  array{id: string, name: string, branch_id: int|string|null, branch_code: string, branch_name: string, email: string|null, phone: string|null, position: string|null, department?: string|null}  $employee
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

        $this->isEmailLocked = ! empty(trim($this->requestor_email));
        $this->isPhoneLocked = ! empty(trim($this->requestor_phone));
        $this->isDepartmentLocked = ! empty(trim($this->requestor_department));
        $this->isPositionLocked = ! empty(trim($this->requestor_position));

        $this->employeeSearch = '';
    }

    public function clearSelectedEmployee(): void
    {
        if ($this->isKaryawan) {
            return;
        }

        $this->selectedEmployee = null;
        $this->employeeSearch = '';
        $this->employeeResults = Karyawan::searchEmployees('', 8)->toArray();
        $this->requestor_name = '';
        $this->requestor_department = '';
        $this->requestor_position = '';
        $this->requestor_email = '';
        $this->requestor_phone = '';
        $this->isBranchLocked = false;
        $this->isEmailLocked = false;
        $this->isPhoneLocked = false;
        $this->isDepartmentLocked = false;
        $this->isPositionLocked = false;
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
            'purpose' => $this->purpose ?: null,
            'archive_location' => $this->archive_location,
            'requestor_department' => $this->requestor_department,
            'requestor_position' => $this->requestor_position,
            'requestor_name' => $this->requestor_name,
            'requestor_email' => $this->requestor_email,
            'requestor_phone' => $this->requestor_phone,
        ]);

        $this->showSuccessModal = true;

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Nomor Surat Terbit!',
            'message' => "Nomor registrasi {$this->createdLetter->reference_number} berhasil dibuat.",
        ]);
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

    public function closeSuccessModal(): void
    {
        $this->showSuccessModal = false;
    }

    public function updatedTargetCode(): void
    {
        $this->resetValidation('target_code');
    }

    public function updatedSubject(): void
    {
        $this->resetValidation('subject');
    }

    public function updatedRequestorName(): void
    {
        $this->resetValidation('requestor_name');
    }

    public function selectTarget(string $codeOrFormatted, ?string $name = null): void
    {
        if ($name) {
            $this->target_code = "{$codeOrFormatted} - {$name}";
        } elseif (str_contains($codeOrFormatted, ' - ')) {
            $this->target_code = $codeOrFormatted;
        } else {
            $target = LetterTarget::where('code', $codeOrFormatted)->first();
            $this->target_code = $target ? "{$target->code} - {$target->name}" : $codeOrFormatted;
        }

        $this->resetValidation('target_code');
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'target_code.required' => 'Tujuan / instansi penerima surat wajib diisi.',
            'target_code.min' => 'Tujuan / instansi penerima surat minimal 2 karakter.',
            'target_code.max' => 'Tujuan / instansi penerima surat maksimal 100 karakter.',
            'branch_code.required' => 'Cabang surat wajib dipilih.',
            'subject.required' => 'Perihal surat wajib diisi.',
            'subject.min' => 'Perihal surat minimal 3 karakter.',
            'requestor_name.required' => 'Nama pemohon surat wajib diisi.',
            'month.required' => 'Bulan surat wajib dipilih.',
            'year.required' => 'Tahun surat wajib dipilih.',
            'requestor_email.email' => 'Format email pemohon tidak valid.',
        ];
    }

    public function render(LetterNumberService $service): View
    {
        /** @var Collection<int, array{id: int|string|null, code: string, name: string}> $branches */
        $branches = Cabang::getActiveBranches();

        $previewNumber = $this->branch_code && $this->month && $this->year
            ? $service->previewNextNumber($this->branch_code, $this->month, $this->year, $this->target_code)
            : '-';

        $matchedTarget = LetterTarget::findMatching($this->target_code);

        $targetQuery = LetterTarget::active()->orderBy('name');
        $trimmedSearch = trim($this->target_code);

        if ($trimmedSearch !== '') {
            $search = str_contains($trimmedSearch, '-') ? trim(explode('-', $trimmedSearch, 2)[0]) : $trimmedSearch;
            $operator = LetterTarget::query()->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $filtered = (clone $targetQuery)->where(function ($q) use ($search, $operator) {
                $q->where('code', $operator, "%{$search}%")
                    ->orWhere('name', $operator, "%{$search}%");
            })->get();

            $standardTargets = $filtered->isNotEmpty() ? $filtered : $targetQuery->get();
        } else {
            $standardTargets = $targetQuery->get();
        }

        return view('livewire.letter-request-form', [
            'branches' => $branches,
            'previewNumber' => $previewNumber,
            'matchedTarget' => $matchedTarget,
            'standardTargets' => $standardTargets,
            'romanMonths' => LetterNumberService::ROMAN_MONTHS,
            'monthNames' => LetterNumberService::MONTH_NAMES,
        ]);
    }
}
