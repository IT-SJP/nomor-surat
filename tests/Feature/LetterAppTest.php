<?php

use App\Livewire\BranchManagement;
use App\Livewire\DashboardAdmin;
use App\Livewire\LetterHistory;
use App\Livewire\LetterRequestForm;
use App\Models\Absen\Cabang;
use App\Models\Branch;
use App\Models\Letter;
use App\Services\LetterNumberService;
use Database\Seeders\LetterTargetSeeder;
use Livewire\Livewire;

beforeEach(function () {
    (new LetterTargetSeeder)->run();

    $this->withSession([
        'auth_sso' => [
            'type' => 'admin',
            'role' => 'admin',
            'name' => 'Admin SJP',
            'branch_code' => 'SJP',
            'branch_name' => 'PT Selamat Jaya Persada',
        ],
    ]);
});

test('dashboard page renders successfully with stats', function () {
    Letter::factory()->create([
        'branch_code' => 'SJP',
        'subject' => 'Test Surat 1',
    ]);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Dashboard Monitoring Surat')
        ->assertSee('SJP');
});

test('letter request form page renders successfully', function () {
    $this->get(route('letter.request'))
        ->assertOk()
        ->assertSee('Buat Nomor Surat Keluar');
});

test('can create a new letter via livewire component with custom target and standard target', function () {
    // Custom non-standard target
    Livewire::test(LetterRequestForm::class)
        ->set('branch_code', 'SJP')
        ->set('target_code', 'IJTM')
        ->set('month', 1)
        ->set('year', 2026)
        ->set('subject', 'Pengadaan Laptop Staff')
        ->set('purpose', 'Untuk kebutuhan operasional IT')
        ->set('requestor_name', 'Ahmad Dani')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('showSuccessModal', true);

    $this->assertDatabaseHas('letters', [
        'reference_number' => '001/SJP/I/2026',
        'requestor_name' => 'Ahmad Dani',
        'target_code' => 'IJTM',
    ]);

    // Standard target (IM)
    Livewire::test(LetterRequestForm::class)
        ->set('branch_code', 'SJP')
        ->set('target_code', 'IM')
        ->set('month', 1)
        ->set('year', 2026)
        ->set('subject', 'Memo Internal Divisi')
        ->set('purpose', 'Koordinasi')
        ->set('requestor_name', 'Ahmad Dani')
        ->call('submit')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('letters', [
        'reference_number' => '002/IM/SJP/I/2026',
        'target_code' => 'IM',
    ]);
});

test('selectTarget sets target_code to formatted code and name and submits successfully', function () {
    Livewire::test(LetterRequestForm::class)
        ->set('branch_code', 'SJP')
        ->call('selectTarget', 'EXT', 'Eksternal / Instansi Luar')
        ->assertSet('target_code', 'EXT - Eksternal / Instansi Luar')
        ->set('month', 9)
        ->set('year', 2026)
        ->set('subject', 'Surat Eksternal Bank')
        ->set('requestor_name', 'Budi Santoso')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('showSuccessModal', true);

    $this->assertDatabaseHas('letters', [
        'reference_number' => '001/EXT/SJP/IX/2026',
        'target_code' => 'EXT - Eksternal / Instansi Luar',
    ]);
});

test('validates required fields on letter request form', function () {
    Livewire::test(LetterRequestForm::class)
        ->set('target_code', '')
        ->set('subject', '')
        ->set('purpose', '')
        ->set('requestor_name', '')
        ->call('submit')
        ->assertHasErrors(['target_code', 'subject', 'requestor_name'])
        ->assertHasNoErrors(['purpose']);
});

test('clears validation error immediately when selectTarget is called and displays Indonesian messages', function () {
    Livewire::test(LetterRequestForm::class)
        ->set('target_code', '')
        ->call('submit')
        ->assertHasErrors(['target_code' => 'required'])
        ->assertSee('Tujuan / instansi penerima surat wajib diisi.')
        ->assertDontSee('validation.required')
        ->call('selectTarget', 'ND', 'Nota Dinas')
        ->assertHasNoErrors(['target_code'])
        ->assertDontSee('Tujuan / instansi penerima surat wajib diisi.');
});

test('employee selection does not change or lock publishing branch in admin mode', function () {
    $employeeMock = [
        'id' => 'mock-hash-id-123',
        'name' => 'Dewi Sartika',
        'branch_id' => 2,
        'branch_code' => 'CSI',
        'branch_name' => 'PT CSI Group',
        'email' => 'dewi@csi.com',
        'department' => 'Finance Dept',
        'position' => 'Finance Staff',
    ];

    Livewire::test(LetterRequestForm::class)
        ->set('branch_code', 'SJP')
        ->call('selectEmployee', $employeeMock)
        ->assertSet('requestor_name', 'Dewi Sartika')
        ->assertSet('requestor_department', 'Finance Dept')
        ->assertSet('requestor_position', 'Finance Staff')
        ->assertSet('branch_code', 'SJP')
        ->assertSet('isBranchLocked', false);
});

test('admin can search employees in realtime and select an employee with select2 style without exposing NIK', function () {
    $component = Livewire::test(LetterRequestForm::class)
        ->set('employeeSearch', 'Karim')
        ->assertSet('isKaryawan', false);

    expect($component->get('employeeResults'))->not()->toBeEmpty();
    expect($component->get('employeeResults')[0]['name'])->toContain('Karim');
    expect($component->get('employeeResults')[0])->not()->toHaveKey('nik');
    expect($component->get('employeeResults')[0]['id'])->not()->toBe('1771012501030004');

    $component->assertDontSee('1771012501030004')
        ->assertDontSee('NIK:');

    $selected = $component->get('employeeResults')[0];
    $component->call('selectEmployee', $selected)
        ->assertSet('selectedEmployee', $selected)
        ->assertSet('requestor_name', $selected['name'])
        ->assertSet('isEmailLocked', true)
        ->assertSet('isPhoneLocked', true)
        ->assertDontSee('1771012501030004')
        ->assertDontSee('NIK:');

    $component->call('clearSelectedEmployee')
        ->assertSet('selectedEmployee', null)
        ->assertSet('requestor_name', '')
        ->assertSet('isEmailLocked', false)
        ->assertSet('isPhoneLocked', false);
});

test('letter history page renders and supports search and filter', function () {
    Letter::factory()->create([
        'reference_number' => 'SJP/I/2026/001',
        'branch_code' => 'SJP',
        'subject' => 'Surat Pembelian Server',
    ]);

    Letter::factory()->create([
        'reference_number' => 'CSI/I/2026/001',
        'branch_code' => 'CSI',
        'subject' => 'Surat Sewa Gedung',
    ]);

    $this->get(route('letter.history'))
        ->assertOk()
        ->assertSee('Surat Pembelian Server')
        ->assertSee('Surat Sewa Gedung');

    Livewire::test(LetterHistory::class)
        ->set('search', 'Pembelian')
        ->assertSee('Surat Pembelian Server')
        ->assertDontSee('Surat Sewa Gedung');

    Livewire::test(LetterHistory::class)
        ->set('branch', 'CSI')
        ->assertSee('Surat Sewa Gedung')
        ->assertDontSee('Surat Pembelian Server');
});

test('karyawan in letter history is isolated to own branch', function () {
    session([
        'auth_sso' => [
            'type' => 'karyawan',
            'role' => 'karyawan',
            'nik' => '1771055705020001',
            'name' => 'Alfiyyah',
            'branch_code' => 'SJK',
        ],
    ]);

    Letter::factory()->create([
        'reference_number' => 'SJK/I/2026/001',
        'branch_code' => 'SJK',
        'subject' => 'Surat Khusus SJK',
    ]);

    Letter::factory()->create([
        'reference_number' => 'SJP/I/2026/001',
        'branch_code' => 'SJP',
        'subject' => 'Surat Khusus SJP',
    ]);

    Livewire::test(LetterHistory::class)
        ->assertSee('Surat Khusus SJK')
        ->assertDontSee('Surat Khusus SJP');
});

test('can export letters history to csv', function () {
    Letter::factory()->create([
        'reference_number' => 'SJP/I/2026/001',
        'subject' => 'Surat Khusus CSV',
    ]);

    $response = Livewire::test(LetterHistory::class)
        ->call('exportCsv');

    $response->assertFileDownloaded();
});

test('can filter letters history by date', function () {
    $todayLetter = Letter::factory()->create([
        'reference_number' => 'SJP/IX/2026/001',
        'subject' => 'Surat Hari Ini',
        'created_at' => '2026-09-03 10:00:00',
    ]);

    $pastLetter = Letter::factory()->create([
        'reference_number' => 'SJP/VIII/2026/002',
        'subject' => 'Surat Bulan Lalu',
        'created_at' => '2026-08-15 10:00:00',
    ]);

    Livewire::test(LetterHistory::class)
        ->set('date', '2026-09-03')
        ->assertSee('Surat Hari Ini')
        ->assertDontSee('Surat Bulan Lalu');
});

test('letter request form populates requestor email and phone for karyawan from session and locks them', function () {
    session([
        'auth_sso' => [
            'type' => 'karyawan',
            'role' => 'karyawan',
            'nik' => '1771012501030004',
            'name' => 'Muhammad Nurul Karim',
            'email' => 'mhmdnurulkarim@gmail.com',
            'phone' => '08516364898199',
            'branch_code' => 'SJP',
            'department_name' => 'Information Technology',
            'position_name' => 'Staff',
        ],
    ]);

    Livewire::test(LetterRequestForm::class)
        ->assertSet('requestor_name', 'Muhammad Nurul Karim')
        ->assertSet('requestor_department', 'Information Technology')
        ->assertSet('requestor_position', 'Staff')
        ->assertSet('requestor_email', 'mhmdnurulkarim@gmail.com')
        ->assertSet('requestor_phone', '08516364898199')
        ->assertSet('isEmailLocked', true)
        ->assertSet('isPhoneLocked', true);
});

test('letter request form leaves email and phone unlocked for manual edit if employee data is empty', function () {
    session([
        'auth_sso' => [
            'type' => 'karyawan',
            'role' => 'karyawan',
            'nik' => '9999999999999999', // non-existent NIK so no DB fallback
            'name' => 'Karyawan Baru Tanpa Email',
            'email' => null,
            'phone' => null,
            'branch_code' => 'SJP',
            'department_name' => 'Operasional',
            'position_name' => 'Staff',
        ],
    ]);

    Livewire::test(LetterRequestForm::class)
        ->assertSet('requestor_name', 'Karyawan Baru Tanpa Email')
        ->assertSet('requestor_email', '')
        ->assertSet('requestor_phone', '')
        ->assertSet('isEmailLocked', false)
        ->assertSet('isPhoneLocked', false)
        ->set('requestor_email', 'manual@example.com')
        ->set('requestor_phone', '08123456789')
        ->assertSet('requestor_email', 'manual@example.com')
        ->assertSet('requestor_phone', '08123456789');
});

test('success modal can be closed via closeSuccessModal and renders close button', function () {
    Livewire::test(LetterRequestForm::class)
        ->set('showSuccessModal', true)
        ->assertSeeHtml('wire:click="closeSuccessModal"')
        ->call('closeSuccessModal')
        ->assertSet('showSuccessModal', false);
});

test('can submit letter request without optional purpose', function () {
    Livewire::test(LetterRequestForm::class)
        ->set('branch_code', 'SJP')
        ->set('target_code', 'IJTM')
        ->set('month', 1)
        ->set('year', 2026)
        ->set('subject', 'Surat Permohonan Tanpa Keterangan')
        ->set('purpose', '')
        ->set('requestor_name', 'Budi Santoso')
        ->call('submit')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('letters', [
        'subject' => 'Surat Permohonan Tanpa Keterangan',
        'purpose' => null,
    ]);
});

test('letter history renders custom daisyui pagination and handles perPage changes', function () {
    // Create 20 letters to trigger pagination (default 15 per page)
    Letter::factory()->count(20)->create([
        'branch_code' => 'SJP',
    ]);

    Livewire::test(LetterHistory::class)
        ->assertSee('Item per halaman:')
        ->assertSeeHtml('wire:model.live="perPage"')
        ->assertSeeHtml('join')
        ->assertSeeHtml('Page 1')
        ->set('perPage', 10)
        ->assertSet('perPage', 10)
        ->call('gotoPage', 2)
        ->assertSet('paginators.page', 2);
});

test('collapsible icon-only drawer sidebar renders with DaisyUI 5 classes and tooltips', function () {
    $response = $this->get(route('dashboard'));

    $response->assertOk()
        ->assertSeeHtml('drawer lg:drawer-open')
        ->assertSeeHtml('id="main-drawer"')
        ->assertSeeHtml('drawer-toggle inline')
        ->assertSeeHtml('drawer-content')
        ->assertSeeHtml('flex-none hidden lg:block')
        ->assertSeeHtml('flex-1 flex justify-end items-center gap-3')
        ->assertSeeHtml('drawer-side is-drawer-close:overflow-visible max-lg:hidden')
        ->assertSeeHtml('is-drawer-close:w-16 is-drawer-open:w-64')
        ->assertSeeHtml('is-drawer-close:tooltip is-drawer-close:tooltip-right')
        ->assertSeeHtml('is-drawer-close:w-11 is-drawer-close:h-11')
        ->assertSeeHtml('data-tip="Dashboard"')
        ->assertSeeHtml('data-tip="Buat Nomor Surat"')
        ->assertSeeHtml('data-tip="Riwayat Nomor Surat"')
        ->assertSeeHtml('data-tip="Pengaturan Cabang"')
        ->assertSeeHtml('data-tip="Daftar Tujuan Surat"')
        ->assertSeeHtml('data-tip="Kembali Absenku SJP"')
        ->assertSeeHtml('dock dock-bottom')
        ->assertSeeHtml('is-drawer-close:hidden');
});

test('app css contains mobile scroll lock override for daisyui drawer-toggle', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)->toContain('@media (max-width: 1023.98px)')
        ->toContain('--page-scroll-lock: revert-layer')
        ->toContain('overflow-y: auto');
});

test('admin cabang can pick any branch in request form and employees scoped to their own branch', function () {
    $this->withSession([
        'auth_sso' => [
            'type' => 'admin cabang',
            'role' => 'admin cabang',
            'admin_role' => 'admin cabang',
            'name' => 'HRD SITE KETAHUN',
            'raw_branch_code' => 'CBNG0003',
            'branch_code' => 'KTN01',
            'branch_name' => 'Cabang Ketahun',
        ],
    ]);

    $response = $this->get(route('letter.request'));
    $response->assertOk();

    Livewire::test(LetterRequestForm::class)
        ->assertSet('isAdminCabang', true)
        ->assertSet('isBranchLocked', false)
        ->assertSet('branch_code', 'KTN01')
        ->assertSet('branch_name', 'Cabang Ketahun');
});

test('admin cabang can view all branch letters with sensitive fields masked for other branches', function () {
    Letter::factory()->create([
        'branch_code' => 'KTN01',
        'subject' => 'Surat Khusus Ketahun',
        'target_code' => 'IM',
    ]);
    Letter::factory()->create([
        'branch_code' => 'SJP',
        'subject' => 'Surat Milik Pusat SJP',
        'target_code' => 'EK',
    ]);
    Letter::factory()->create([
        'branch_code' => 'JMB01',
        'subject' => 'Surat Milik Jambi',
        'target_code' => 'DN',
    ]);

    $this->withSession([
        'auth_sso' => [
            'type' => 'admin cabang',
            'role' => 'admin cabang',
            'admin_role' => 'admin cabang',
            'name' => 'HRD SITE KETAHUN',
            'raw_branch_code' => 'CBNG0003',
            'branch_code' => 'KTN01',
            'branch_name' => 'Cabang Ketahun',
        ],
    ]);

    Livewire::test(LetterHistory::class)
        ->assertOk()
        ->assertSet('isAdminCabang', true)
        ->assertSet('branch', 'KTN01')
        ->assertSee('Surat Khusus Ketahun')
        ->set('branch', '')
        ->assertSee('Surat Khusus Ketahun')
        ->assertDontSee('Surat Milik Pusat SJP')
        ->assertSee('••••••••••••');
});

test('admin cabang only exports their own branch letters via CSV export', function () {
    Letter::factory()->create([
        'branch_code' => 'KTN01',
        'subject' => 'Surat Export Ketahun',
    ]);
    Letter::factory()->create([
        'branch_code' => 'SJP',
        'subject' => 'Surat Export Pusat SJP',
    ]);

    $this->withSession([
        'auth_sso' => [
            'type' => 'admin cabang',
            'role' => 'admin cabang',
            'admin_role' => 'admin cabang',
            'name' => 'HRD SITE KETAHUN',
            'raw_branch_code' => 'CBNG0003',
            'branch_code' => 'KTN01',
            'branch_name' => 'Cabang Ketahun',
        ],
    ]);

    $component = Livewire::test(LetterHistory::class);
    $response = $component->call('exportCsv');
    $streamedResponse = $response->effects['download'] ?? null;

    // Call exportCsv directly on instance
    $export = $component->instance()->exportCsv();
    ob_start();
    $export->sendContent();
    $content = ob_get_clean();

    expect($content)->toContain('Surat Export Ketahun')
        ->and($content)->not->toContain('Surat Export Pusat SJP');
});

test('header user profile displays role label according to user state', function () {
    // 1. Admin Cabang
    $this->withSession([
        'auth_sso' => [
            'type' => 'admin',
            'role' => 'admin cabang',
            'admin_role' => 'admin cabang',
            'name' => 'HRD SITE KETAHUN',
        ],
    ])->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Admin Cabang');

    // 2. HRD
    $this->withSession([
        'auth_sso' => [
            'type' => 'admin',
            'role' => 'hrd',
            'admin_role' => 'hrd',
            'name' => 'Ahmad Yozi',
        ],
    ])->get(route('dashboard'))
        ->assertOk()
        ->assertSee('HRD');

    // 3. Administrator
    $this->withSession([
        'auth_sso' => [
            'type' => 'admin',
            'role' => 'administrator',
            'admin_role' => 'administrator',
            'name' => 'Muhammad Nurul Karim',
        ],
    ])->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Administrator');

    // 4. Karyawan
    $this->withSession([
        'auth_sso' => [
            'type' => 'karyawan',
            'role' => 'karyawan',
            'name' => 'Budi Santoso',
            'department_name' => 'Operasional',
        ],
    ])->get(route('letter.request'))
        ->assertOk()
        ->assertSee('Operasional');
});

test('admin cabang dashboard stats and recent letters are strictly filtered to their own branch', function () {
    Letter::factory()->create([
        'branch_code' => 'KTN01',
        'subject' => 'Surat Dashboard Ketahun',
    ]);
    Letter::factory()->create([
        'branch_code' => 'SJP',
        'subject' => 'Surat Dashboard Pusat SJP 1',
    ]);
    Letter::factory()->create([
        'branch_code' => 'SJP',
        'subject' => 'Surat Dashboard Pusat SJP 2',
    ]);

    $this->withSession([
        'auth_sso' => [
            'type' => 'admin cabang',
            'role' => 'admin cabang',
            'admin_role' => 'admin cabang',
            'name' => 'HRD SITE KETAHUN',
            'raw_branch_code' => 'CBNG0003',
            'branch_code' => 'KTN01',
            'branch_name' => 'Cabang Ketahun',
        ],
    ]);

    $response = $this->get(route('dashboard'));
    $response->assertOk()
        ->assertSee('Dashboard Monitoring Surat')
        ->assertSee('Cabang KTN01')
        ->assertSee('Surat Dashboard Ketahun')
        ->assertDontSee('Surat Dashboard Pusat SJP 1')
        ->assertDontSee('Akumulasi Surat Per Cabang');

    Livewire::test(DashboardAdmin::class)
        ->assertOk()
        ->assertSet('isAdminCabang', true)
        ->assertSet('adminBranchCode', 'KTN01')
        ->assertViewHas('totalLetters', 1)
        ->assertViewHas('totalBranchesCount', 1)
        ->assertSee('Surat Dashboard Ketahun')
        ->assertDontSee('Surat Dashboard Pusat SJP 1')
        ->assertDontSee('Akumulasi Surat Per Cabang');
});

test('LetterHistory automatically opens detail modal when open query parameter is present', function () {
    $letter = Letter::factory()->create([
        'branch_code' => 'SJP',
        'subject' => 'Surat Auto Open Modal',
        'reference_number' => '999/SJP/IX/2026',
    ]);

    // Initial load without open param: modal is closed
    Livewire::test(LetterHistory::class)
        ->assertSet('showDetailModal', false)
        ->assertSet('selectedLetter', null);

    // Load with open param: modal is immediately open with correct letter
    Livewire::withQueryParams(['open' => $letter->id])
        ->test(LetterHistory::class)
        ->assertSet('showDetailModal', true)
        ->assertSet('open', $letter->id)
        ->assertSee('Detail Nomor Surat')
        ->assertSee('999/SJP/IX/2026')
        ->assertSee('Surat Auto Open Modal')
        ->call('closeDetailModal')
        ->assertSet('showDetailModal', false)
        ->assertSet('open', null);
});

test('dashboard recent letters table displays only parent letters with sub-nomor column and clickable rows', function () {
    $service = new LetterNumberService;

    // Parent 1 with 2 sub-letters
    $parent1 = $service->createLetter([
        'branch_code' => 'SJP',
        'target_code' => 'IM',
        'month' => 9,
        'year' => 2026,
        'subject' => 'Surat Pengajuan Induk Ada Sub',
        'requestor_name' => 'Budi',
        'sub_count' => 2,
    ]);

    // Parent 2 without sub-letters
    $parent2 = $service->createLetter([
        'branch_code' => 'SJP',
        'target_code' => 'IM',
        'month' => 9,
        'year' => 2026,
        'subject' => 'Surat Pengajuan Induk Tanpa Sub',
        'requestor_name' => 'Siti',
    ]);

    $sub1 = $parent1->subLetters()->first();

    Livewire::test(DashboardAdmin::class)
        ->assertSee('Pengajuan Surat Terkini')
        ->assertSee('Sub-Nomor')
        ->assertSee($parent1->reference_number)
        ->assertSee($parent2->reference_number)
        ->assertDontSee($sub1->reference_number) // sub-letters must NOT appear as separate rows
        ->assertSee('2 Sub-Nomor Surat') // badge with count 2 for parent1
        ->assertSee('-') // dash for parent2
        ->assertSeeHtml(route('letter.history', ['open' => $parent1->id]))
        ->assertSeeHtml(route('letter.history', ['open' => $parent2->id]));
});

test('LetterRequestForm Lihat Riwayat link points to letter history with open parameter of created letter', function () {
    Livewire::test(LetterRequestForm::class)
        ->set('branch_code', 'SJP')
        ->set('target_code', 'IM')
        ->set('month', 9)
        ->set('year', 2026)
        ->set('subject', 'Permohonan Pembelian Alat Berat')
        ->set('requestor_name', 'Hendra')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('showSuccessModal', true);

    $created = Letter::where('subject', 'Permohonan Pembelian Alat Berat')->first();
    expect($created)->not->toBeNull();

    Livewire::test(LetterRequestForm::class)
        ->set('createdLetter', $created)
        ->set('showSuccessModal', true)
        ->assertSeeHtml(route('letter.history', ['open' => $created->id]))
        ->assertSee('Lihat Riwayat');
});

test('admin can add custom branch outside HRIS and it persists in active branches', function () {
    $this->withSession([
        'auth_sso' => [
            'type' => 'admin',
            'role' => 'administrator',
            'admin_role' => 'administrator',
            'name' => 'Super Admin',
        ],
    ]);

    Livewire::test(BranchManagement::class)
        ->call('openAddModal')
        ->assertSet('showAddModal', true)
        ->set('newBranchName', 'Cabang Khusus IKN')
        ->set('newBranchCode', 'IKN01')
        ->call('saveNewBranch')
        ->assertHasNoErrors()
        ->assertSet('showAddModal', false);

    $branch = Branch::where('branch_code', 'IKN01')->first();
    expect($branch)->not->toBeNull();
    expect($branch->name)->toBe('Cabang Khusus IKN');
    expect($branch->hr_code)->toBeNull();
    expect($branch->is_active)->toBeTrue();

    $activeBranches = Cabang::getActiveBranches();
    expect($activeBranches->pluck('code')->all())->toContain('IKN01');
});

test('requestor selection does not overwrite or lock the publishing branch', function () {
    Livewire::test(LetterRequestForm::class)
        ->set('branch_code', 'SJP')
        ->call('selectEmployee', [
            'id' => 'EMP-001',
            'name' => 'Budi Santoso',
            'branch_code' => 'BDG01',
            'branch_name' => 'Cabang Bandung',
            'email' => 'budi@example.com',
            'phone' => '08123456789',
            'department' => 'Operasional',
            'position' => 'Staff',
        ])
        ->assertSet('branch_code', 'SJP') // Still SJP, not overwritten by employee branch
        ->assertSet('requestor_name', 'Budi Santoso')
        ->assertSet('isBranchLocked', false);
});

test('super admin and hrd are never masked in letter history', function () {
    Letter::factory()->create([
        'branch_code' => 'KTN01',
        'subject' => 'Surat Rahasia Cabang Lain',
        'purpose' => 'Keperluan Rahasia',
        'target_code' => 'IM',
        'requestor_name' => 'Karyawan Ketahun',
    ]);

    // Test as Super Admin
    $this->withSession([
        'auth_sso' => [
            'type' => 'admin',
            'role' => 'super admin',
            'admin_role' => 'super admin',
            'name' => 'Direktur Utama',
        ],
    ]);

    Livewire::test(LetterHistory::class)
        ->set('branch', '')
        ->assertSee('Surat Rahasia Cabang Lain')
        ->assertSee('Keperluan Rahasia')
        ->assertDontSee('••••••••••••');

    // Test as HRD
    $this->withSession([
        'auth_sso' => [
            'type' => 'admin',
            'role' => 'hrd',
            'admin_role' => 'hrd',
            'name' => 'HR Pusat',
        ],
    ]);

    Livewire::test(LetterHistory::class)
        ->set('branch', '')
        ->assertSee('Surat Rahasia Cabang Lain')
        ->assertSee('Keperluan Rahasia')
        ->assertDontSee('••••••••••••');
});
