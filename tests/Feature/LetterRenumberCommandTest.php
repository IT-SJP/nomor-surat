<?php

use App\Models\Letter;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    Letter::query()->delete();
});

test('letter:renumber --dry-run previews changes without modifying database records', function () {
    // Month 1: seq 1, 2
    $letter1 = Letter::create([
        'branch_code' => 'SJP',
        'sequence_number' => 1,
        'reference_number' => '001/IM/SJP/I/2026',
        'target_code' => 'IM',
        'month_roman' => 'I',
        'month' => 1,
        'year' => 2026,
        'subject' => 'Surat 1',
        'requestor_name' => 'User 1',
    ]);

    $letter2 = Letter::create([
        'branch_code' => 'SJP',
        'sequence_number' => 2,
        'reference_number' => '002/IM/SJP/I/2026',
        'target_code' => 'IM',
        'month_roman' => 'I',
        'month' => 1,
        'year' => 2026,
        'subject' => 'Surat 2',
        'requestor_name' => 'User 2',
    ]);

    // Month 2: old seq 1 (under old monthly reset)
    $letter3 = Letter::create([
        'branch_code' => 'SJP',
        'sequence_number' => 1,
        'reference_number' => '001/EXT/SJP/II/2026',
        'target_code' => 'EXT',
        'month_roman' => 'II',
        'month' => 2,
        'year' => 2026,
        'subject' => 'Surat 3',
        'requestor_name' => 'User 3',
    ]);

    $exitCode = Artisan::call('letter:renumber', [
        '--dry-run' => true,
    ]);

    expect($exitCode)->toBe(0);

    // Verify database remains untouched
    $letter3Fresh = $letter3->fresh();
    expect($letter3Fresh->sequence_number)->toBe(1)
        ->and($letter3Fresh->reference_number)->toBe('001/EXT/SJP/II/2026');
});

test('letter:renumber sequentially renumbers letters across months in the same year per branch', function () {
    // SJP Month 1: 2 letters
    Letter::create([
        'branch_code' => 'SJP',
        'sequence_number' => 1,
        'reference_number' => '001/IM/SJP/I/2026',
        'target_code' => 'IM',
        'month_roman' => 'I',
        'month' => 1,
        'year' => 2026,
        'subject' => 'Surat SJP Jan 1',
        'requestor_name' => 'User',
    ]);

    Letter::create([
        'branch_code' => 'SJP',
        'sequence_number' => 2,
        'reference_number' => '002/IM/SJP/I/2026',
        'target_code' => 'IM',
        'month_roman' => 'I',
        'month' => 1,
        'year' => 2026,
        'subject' => 'Surat SJP Jan 2',
        'requestor_name' => 'User',
    ]);

    // SJP Month 2: old seq 1, 2
    $sjpFeb1 = Letter::create([
        'branch_code' => 'SJP',
        'sequence_number' => 1,
        'reference_number' => '001/BA/SJP/II/2026',
        'target_code' => 'BA',
        'month_roman' => 'II',
        'month' => 2,
        'year' => 2026,
        'subject' => 'Surat SJP Feb 1',
        'requestor_name' => 'User',
    ]);

    $sjpFeb2 = Letter::create([
        'branch_code' => 'SJP',
        'sequence_number' => 2,
        'reference_number' => '002/EXT/SJP/II/2026',
        'target_code' => 'EXT',
        'month_roman' => 'II',
        'month' => 2,
        'year' => 2026,
        'subject' => 'Surat SJP Feb 2',
        'requestor_name' => 'User',
    ]);

    // SJP Month 3: old seq 1
    $sjpMar1 = Letter::create([
        'branch_code' => 'SJP',
        'sequence_number' => 1,
        'reference_number' => '001/SPECIAL-CODE/SJP/III/2026',
        'target_code' => 'SPECIAL-CODE',
        'month_roman' => 'III',
        'month' => 3,
        'year' => 2026,
        'subject' => 'Surat SJP Mar 1',
        'requestor_name' => 'User',
    ]);

    // CSI Month 2: separate branch, old seq 1
    $csiFeb1 = Letter::create([
        'branch_code' => 'CSI',
        'sequence_number' => 1,
        'reference_number' => '001/CSI/II/2026',
        'target_code' => 'INTERNAL',
        'month_roman' => 'II',
        'month' => 2,
        'year' => 2026,
        'subject' => 'Surat CSI Feb 1',
        'requestor_name' => 'User CSI',
    ]);

    $exitCode = Artisan::call('letter:renumber');
    expect($exitCode)->toBe(0);

    // Verify SJP Month 2 continues to 3 and 4
    $sjpFeb1Fresh = $sjpFeb1->fresh();
    expect($sjpFeb1Fresh->sequence_number)->toBe(3)
        ->and($sjpFeb1Fresh->reference_number)->toBe('003/BA/SJP/II/2026');

    $sjpFeb2Fresh = $sjpFeb2->fresh();
    expect($sjpFeb2Fresh->sequence_number)->toBe(4)
        ->and($sjpFeb2Fresh->reference_number)->toBe('004/EXT/SJP/II/2026');

    // Verify SJP Month 3 continues to 5 with custom target preserved
    $sjpMar1Fresh = $sjpMar1->fresh();
    expect($sjpMar1Fresh->sequence_number)->toBe(5)
        ->and($sjpMar1Fresh->reference_number)->toBe('005/SPECIAL-CODE/SJP/III/2026');

    // Verify CSI branch is independent and starts at 1
    $csiFeb1Fresh = $csiFeb1->fresh();
    expect($csiFeb1Fresh->sequence_number)->toBe(1)
        ->and($csiFeb1Fresh->reference_number)->toBe('001/CSI/II/2026');
});

test('letter:renumber supports filtering by branch and year', function () {
    // SJP 2026 Month 2: seq 1
    $sjp2026 = Letter::create([
        'branch_code' => 'SJP',
        'sequence_number' => 1,
        'reference_number' => '001/IM/SJP/II/2026',
        'target_code' => 'IM',
        'month_roman' => 'II',
        'month' => 2,
        'year' => 2026,
        'subject' => 'Surat SJP 2026',
        'requestor_name' => 'User',
    ]);

    // CSI 2026 Month 2: seq 1
    $csi2026 = Letter::create([
        'branch_code' => 'CSI',
        'sequence_number' => 1,
        'reference_number' => '001/IM/CSI/II/2026',
        'target_code' => 'IM',
        'month_roman' => 'II',
        'month' => 2,
        'year' => 2026,
        'subject' => 'Surat CSI 2026',
        'requestor_name' => 'User',
    ]);

    // Run only for CSI
    $exitCode = Artisan::call('letter:renumber', [
        '--branch' => 'CSI',
        '--year' => 2026,
    ]);

    expect($exitCode)->toBe(0);

    // CSI processed
    expect($csi2026->fresh()->sequence_number)->toBe(1);
    // SJP remained as is
    expect($sjp2026->fresh()->sequence_number)->toBe(1);
});
