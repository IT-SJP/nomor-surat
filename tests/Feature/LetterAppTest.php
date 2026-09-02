<?php

use App\Livewire\LetterHistory;
use App\Livewire\LetterRequestForm;
use App\Models\Letter;
use Livewire\Livewire;

beforeEach(function () {
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

test('can create a new letter via livewire component', function () {
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
        'reference_number' => 'SJP/I/2026/001',
        'requestor_name' => 'Ahmad Dani',
        'target_code' => 'IJTM',
    ]);
});

test('validates required fields on letter request form', function () {
    Livewire::test(LetterRequestForm::class)
        ->set('target_code', '')
        ->set('subject', '')
        ->set('purpose', '')
        ->set('requestor_name', '')
        ->call('submit')
        ->assertHasErrors(['target_code', 'subject', 'purpose', 'requestor_name']);
});

test('can lock branch when employee is selected in admin mode', function () {
    $employeeMock = [
        'id' => 123,
        'nik' => '2024099',
        'name' => 'Dewi Sartika',
        'branch_id' => 2,
        'branch_code' => 'CSI',
        'branch_name' => 'PT CSI Group',
        'email' => 'dewi@csi.com',
        'department' => 'Finance Dept',
        'position' => 'Finance Staff',
    ];

    Livewire::test(LetterRequestForm::class)
        ->call('selectEmployee', $employeeMock)
        ->assertSet('requestor_name', 'Dewi Sartika')
        ->assertSet('requestor_department', 'Finance Dept')
        ->assertSet('requestor_position', 'Finance Staff')
        ->assertSet('branch_code', 'CSI')
        ->assertSet('isBranchLocked', true);
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
