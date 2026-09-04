<?php

use App\Services\LetterNumberService;
use Database\Seeders\LetterTargetSeeder;

beforeEach(function () {
    (new LetterTargetSeeder)->run();
});

test('converts month numbers to roman numerals correctly', function () {
    expect(LetterNumberService::monthToRoman(1))->toBe('I')
        ->and(LetterNumberService::monthToRoman(5))->toBe('V')
        ->and(LetterNumberService::monthToRoman(12))->toBe('XII');

    expect(LetterNumberService::romanToMonth('I'))->toBe(1)
        ->and(LetterNumberService::romanToMonth('xii'))->toBe(12);
});

test('generates initial sequence 001 for a new branch, month, and year with standard and custom targets', function () {
    $service = new LetterNumberService;

    // Standard target (IM exists in letter_targets)
    $letterStandard = $service->createLetter([
        'branch_code' => 'SJP',
        'target_code' => 'IM',
        'month' => 1,
        'year' => 2026,
        'subject' => 'Memo Internal Operasional',
        'purpose' => 'Koordinasi antar divisi',
        'requestor_name' => 'Budi Santoso',
    ]);

    expect($letterStandard->reference_number)->toBe('001/IM/SJP/I/2026')
        ->and($letterStandard->sequence_number)->toBe(1)
        ->and($letterStandard->month_roman)->toBe('I');

    $this->assertDatabaseHas('letters', [
        'reference_number' => '001/IM/SJP/I/2026',
        'requestor_name' => 'Budi Santoso',
    ]);

    // Custom target outside standard database (fallback to concise format: no/branch/month/year)
    $letterCustom = $service->createLetter([
        'branch_code' => 'SJP',
        'target_code' => 'Klien Luar Khusus',
        'month' => 1,
        'year' => 2026,
        'subject' => 'Surat Pengantar Dokumen',
        'purpose' => 'Pengiriman berkas',
        'requestor_name' => 'Siti Nurhaliza',
    ]);

    expect($letterCustom->reference_number)->toBe('002/SJP/I/2026')
        ->and($letterCustom->sequence_number)->toBe(2);
});

test('increments sequence within the same month and year', function () {
    $service = new LetterNumberService;

    $letter1 = $service->createLetter([
        'branch_code' => 'SJP',
        'target_code' => 'IM',
        'month' => 3,
        'year' => 2026,
        'subject' => 'Surat 1',
        'purpose' => 'Testing 1',
        'requestor_name' => 'User 1',
    ]);

    $letter2 = $service->createLetter([
        'branch_code' => 'SJP',
        'target_code' => 'Custom Target',
        'month' => 3,
        'year' => 2026,
        'subject' => 'Surat 2',
        'purpose' => 'Testing 2',
        'requestor_name' => 'User 2',
    ]);

    expect($letter1->reference_number)->toBe('001/IM/SJP/III/2026')
        ->and($letter2->reference_number)->toBe('002/SJP/III/2026')
        ->and($letter2->sequence_number)->toBe(2);
});

test('resets sequence to 001 for different months or branches', function () {
    $service = new LetterNumberService;

    // SJP Month 1
    $sjpJan = $service->createLetter([
        'branch_code' => 'SJP',
        'target_code' => 'IM',
        'month' => 1,
        'year' => 2026,
        'subject' => 'Surat SJP Jan',
        'purpose' => 'Test',
        'requestor_name' => 'User',
    ]);

    // SJP Month 2 (should reset to 001)
    $sjpFeb = $service->createLetter([
        'branch_code' => 'SJP',
        'target_code' => 'IM',
        'month' => 2,
        'year' => 2026,
        'subject' => 'Surat SJP Feb',
        'purpose' => 'Test',
        'requestor_name' => 'User',
    ]);

    // CSI Month 1 (different branch, should reset to 001)
    $csiJan = $service->createLetter([
        'branch_code' => 'CSI',
        'target_code' => 'NonStandard',
        'month' => 1,
        'year' => 2026,
        'subject' => 'Surat CSI Jan',
        'purpose' => 'Test',
        'requestor_name' => 'User',
    ]);

    expect($sjpJan->reference_number)->toBe('001/IM/SJP/I/2026')
        ->and($sjpFeb->reference_number)->toBe('001/IM/SJP/II/2026')
        ->and($csiJan->reference_number)->toBe('001/CSI/I/2026');
});

test('previews next letter number accurately with standard target and without standard target', function () {
    $service = new LetterNumberService;

    expect($service->previewNextNumber('SJP', 5, 2026, 'IM'))->toBe('001/IM/SJP/V/2026')
        ->and($service->previewNextNumber('SJP', 5, 2026, 'Tujuan Bebas'))->toBe('001/SJP/V/2026')
        ->and($service->previewNextNumber('SJP', 5, 2026))->toBe('001/SJP/V/2026');

    $service->createLetter([
        'branch_code' => 'SJP',
        'target_code' => 'IM',
        'month' => 5,
        'year' => 2026,
        'subject' => 'Surat Test',
        'purpose' => 'Test',
        'requestor_name' => 'User',
    ]);

    expect($service->previewNextNumber('SJP', 5, 2026, 'IM'))->toBe('002/IM/SJP/V/2026')
        ->and($service->previewNextNumber('SJP', 5, 2026, 'Bebas'))->toBe('002/SJP/V/2026');
});

test('handles formatted code and name target string correctly', function () {
    $service = new LetterNumberService;

    // Check preview with "EXT - Eksternal / Instansi Luar"
    expect($service->previewNextNumber('SJP', 9, 2026, 'EXT - Eksternal / Instansi Luar'))
        ->toBe('001/EXT/SJP/IX/2026');

    // Create letter with formatted target string
    $letter = $service->createLetter([
        'branch_code' => 'SJP',
        'target_code' => 'EXT - Eksternal / Instansi Luar',
        'month' => 9,
        'year' => 2026,
        'subject' => 'Surat Pengantar Vendor',
        'purpose' => 'Kerjasama',
        'requestor_name' => 'Budi Santoso',
    ]);

    expect($letter->reference_number)->toBe('001/EXT/SJP/IX/2026')
        ->and($letter->target_code)->toBe('EXT - Eksternal / Instansi Luar');
});
