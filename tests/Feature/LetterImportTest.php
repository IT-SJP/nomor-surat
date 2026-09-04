<?php

use App\Livewire\LetterHistory;
use App\Models\Letter;
use App\Services\LetterImportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;

beforeEach(function () {
    // Clear letters before each test
    Letter::query()->delete();
});

test('LetterImportService correctly parses CSV, generates sequential numbers, and persists records', function () {
    $csvContent = <<<'CSV'
No,Timestamp,Nomor Surat,Kode Perusahaan,Kode Tujuan,Bulan,Tahun,Perihal,Tujuan,Letak Arsip,Requestor
1,"2/1/2026, 09.43.45",SJP/I/2026/001,SJP,HRD-IN,I,2026,Perhentian sementara operasional pertambangan,Internal,HR,HR
2,"2/1/2026, 11.29.57",SJP/I/2026/001,SJP,BNi,I,2026,Surat Konfirmasi Hutang Audit Tahun Buku 2025,Surat Konfirmasi Hutang Audit Tahun Buku 2025,Finance,Ana Faizah
3,"2/2/2026, 11.11.51",SJP/II/2026/001,SJP,BRI,II,2026,Surat Pemberitahuan Kontrak,EXT,Legal,Agustin
CSV;

    $tempPath = tempnam(sys_get_temp_dir(), 'test_csv_');
    file_put_contents($tempPath, $csvContent);

    try {
        $service = new LetterImportService;
        $result = $service->importFromPath($tempPath, false);

        expect($result['success'])->toBeTrue()
            ->and($result['total_rows'])->toBe(3)
            ->and($result['imported_count'])->toBe(3)
            ->and(Letter::count())->toBe(3);

        $letter1 = Letter::where('sequence_number', 1)->where('month', 1)->first();
        expect($letter1)->not->toBeNull()
            ->and($letter1->reference_number)->toBe('001/HRD-IN/SJP/I/2026')
            ->and($letter1->branch_code)->toBe('SJP')
            ->and($letter1->month_roman)->toBe('I')
            ->and($letter1->year)->toBe(2026)
            ->and($letter1->subject)->toBe('Perhentian sementara operasional pertambangan')
            ->and($letter1->requestor_name)->toBe('HR');

        $letter2 = Letter::where('sequence_number', 2)->where('month', 1)->first();
        expect($letter2)->not->toBeNull()
            ->and($letter2->reference_number)->toBe('002/BNi/SJP/I/2026')
            ->and($letter2->requestor_name)->toBe('Ana Faizah');

        // Month 2 sequence resets to 1
        $letter3 = Letter::where('sequence_number', 1)->where('month', 2)->first();
        expect($letter3)->not->toBeNull()
            ->and($letter3->reference_number)->toBe('001/BRI/SJP/II/2026')
            ->and($letter3->month_roman)->toBe('II');
    } finally {
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }
});

test('letter:import-csv artisan command executes successfully with dry-run and actual import', function () {
    $filePath = 'storage/app/letters_initial_import.csv';

    // 1. Dry run
    $exitCodeDry = Artisan::call('letter:import-csv', [
        'file' => $filePath,
        '--dry-run' => true,
    ]);

    expect($exitCodeDry)->toBe(0)
        ->and(Letter::count())->toBe(0);

    // 2. Real import
    $exitCodeReal = Artisan::call('letter:import-csv', [
        'file' => $filePath,
    ]);

    expect($exitCodeReal)->toBe(0)
        ->and(Letter::count())->toBe(316);
});

test('administrator can view import CSV button and trigger import modal on letter history', function () {
    session([
        'auth_sso' => [
            'role' => 'admin',
            'name' => 'Admin User',
            'branch_code' => 'SJP',
            'branch_name' => 'PT Selamat Jaya Persada',
        ],
    ]);

    Livewire::test(LetterHistory::class)
        ->assertOk()
        ->assertSee('Import CSV')
        ->set('showImportModal', false)
        ->call('openImportModal')
        ->assertSet('showImportModal', true)
        ->call('closeImportModal')
        ->assertSet('showImportModal', false);
});

test('karyawan cannot see import CSV button and is forbidden from opening import modal', function () {
    session([
        'auth_sso' => [
            'role' => 'karyawan',
            'name' => 'Staff Biasa',
            'branch_code' => 'SJP',
            'branch_name' => 'PT Selamat Jaya Persada',
        ],
    ]);

    Livewire::test(LetterHistory::class)
        ->assertOk()
        ->assertDontSee('Import CSV')
        ->call('openImportModal')
        ->assertForbidden();
});

test('admin can upload CSV file and import records through Livewire component', function () {
    session([
        'auth_sso' => [
            'role' => 'admin',
            'name' => 'Super Admin',
            'branch_code' => 'SJP',
            'branch_name' => 'PT Selamat Jaya Persada',
        ],
    ]);

    $csvContent = "No,Timestamp,Nomor Surat,Kode Perusahaan,Kode Tujuan,Bulan,Tahun,Perihal,Tujuan,Letak Arsip,Requestor\n1,\"05/01/2026 10:00:00\",SJP/I/2026/001,SJP,EXT,I,2026,Surat Uji Coba Web,Pengujian,Legal,Admin";
    $file = UploadedFile::fake()->createWithContent('letters.csv', $csvContent);

    Livewire::test(LetterHistory::class)
        ->call('openImportModal')
        ->set('csvFile', $file)
        ->call('importCsv')
        ->assertDispatched('toast')
        ->assertSet('importResult.success', true)
        ->assertSet('importResult.imported_count', 1);

    expect(Letter::count())->toBe(1)
        ->and(Letter::first()->reference_number)->toBe('001/EXT/SJP/I/2026')
        ->and(Letter::first()->subject)->toBe('Surat Uji Coba Web');
});
