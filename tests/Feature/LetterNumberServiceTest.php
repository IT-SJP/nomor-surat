<?php

use App\Services\LetterNumberService;

test('converts month numbers to roman numerals correctly', function () {
    expect(LetterNumberService::monthToRoman(1))->toBe('I')
        ->and(LetterNumberService::monthToRoman(5))->toBe('V')
        ->and(LetterNumberService::monthToRoman(12))->toBe('XII');

    expect(LetterNumberService::romanToMonth('I'))->toBe(1)
        ->and(LetterNumberService::romanToMonth('xii'))->toBe(12);
});

test('generates initial sequence 001 for a new branch, month, and year', function () {
    $service = new LetterNumberService;

    $letter = $service->createLetter([
        'branch_code' => 'SJP',
        'target_code' => 'IJTM',
        'month' => 1,
        'year' => 2026,
        'subject' => 'Surat Pengantar',
        'purpose' => 'Pengajuan proposal',
        'requestor_name' => 'Budi Santoso',
    ]);

    expect($letter->reference_number)->toBe('SJP/I/2026/001')
        ->and($letter->sequence_number)->toBe(1)
        ->and($letter->month_roman)->toBe('I');

    $this->assertDatabaseHas('letters', [
        'reference_number' => 'SJP/I/2026/001',
        'requestor_name' => 'Budi Santoso',
    ]);
});

test('increments sequence within the same month and year', function () {
    $service = new LetterNumberService;

    $letter1 = $service->createLetter([
        'branch_code' => 'SJP',
        'target_code' => 'IJTM',
        'month' => 3,
        'year' => 2026,
        'subject' => 'Surat 1',
        'purpose' => 'Testing 1',
        'requestor_name' => 'User 1',
    ]);

    $letter2 = $service->createLetter([
        'branch_code' => 'SJP',
        'target_code' => 'JKT',
        'month' => 3,
        'year' => 2026,
        'subject' => 'Surat 2',
        'purpose' => 'Testing 2',
        'requestor_name' => 'User 2',
    ]);

    expect($letter1->reference_number)->toBe('SJP/III/2026/001')
        ->and($letter2->reference_number)->toBe('SJP/III/2026/002')
        ->and($letter2->sequence_number)->toBe(2);
});

test('resets sequence to 001 for different months or branches', function () {
    $service = new LetterNumberService;

    // SJP Month 1
    $sjpJan = $service->createLetter([
        'branch_code' => 'SJP',
        'target_code' => 'IJTM',
        'month' => 1,
        'year' => 2026,
        'subject' => 'Surat SJP Jan',
        'purpose' => 'Test',
        'requestor_name' => 'User',
    ]);

    // SJP Month 2 (should reset to 001)
    $sjpFeb = $service->createLetter([
        'branch_code' => 'SJP',
        'target_code' => 'IJTM',
        'month' => 2,
        'year' => 2026,
        'subject' => 'Surat SJP Feb',
        'purpose' => 'Test',
        'requestor_name' => 'User',
    ]);

    // CSI Month 1 (different branch, should reset to 001)
    $csiJan = $service->createLetter([
        'branch_code' => 'CSI',
        'target_code' => 'IJTM',
        'month' => 1,
        'year' => 2026,
        'subject' => 'Surat CSI Jan',
        'purpose' => 'Test',
        'requestor_name' => 'User',
    ]);

    expect($sjpJan->reference_number)->toBe('SJP/I/2026/001')
        ->and($sjpFeb->reference_number)->toBe('SJP/II/2026/001')
        ->and($csiJan->reference_number)->toBe('CSI/I/2026/001');
});

test('previews next letter number accurately without persisting', function () {
    $service = new LetterNumberService;

    expect($service->previewNextNumber('SJP', 5, 2026))->toBe('SJP/V/2026/001');

    $service->createLetter([
        'branch_code' => 'SJP',
        'target_code' => 'IJTM',
        'month' => 5,
        'year' => 2026,
        'subject' => 'Surat Test',
        'purpose' => 'Test',
        'requestor_name' => 'User',
    ]);

    expect($service->previewNextNumber('SJP', 5, 2026))->toBe('SJP/V/2026/002');
});
