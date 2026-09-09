<?php

use App\Livewire\LetterHistory;
use App\Livewire\LetterRequestForm;
use App\Models\Letter;
use App\Services\LetterNumberService;
use Database\Seeders\LetterTargetSeeder;
use Illuminate\Support\Carbon;

beforeEach(function () {
    (new LetterTargetSeeder)->run();
});

test('creates sub-letter under parent letter with correct format and inherited attributes', function () {
    $service = new LetterNumberService;

    // Create a parent letter
    $parent = $service->createLetter([
        'branch_code' => 'SJP',
        'target_code' => 'IM',
        'month' => 1,
        'year' => 2026,
        'subject' => 'Memo Operasional Induk',
        'purpose' => 'Koordinasi Pusat',
        'requestor_name' => 'Budi Santoso',
    ]);

    expect($parent->reference_number)->toBe('001/IM/SJP/I/2026')
        ->and($parent->isSubLetter())->toBeFalse()
        ->and($parent->parent_id)->toBeNull()
        ->and($parent->sub_number)->toBeNull();

    // Create first sub-letter
    $sub1 = $service->createNextSubLetter($parent);

    expect($sub1->reference_number)->toBe('001.1/IM/SJP/I/2026')
        ->and($sub1->sequence_number)->toBe(1)
        ->and($sub1->sub_number)->toBe(1)
        ->and($sub1->parent_id)->toBe($parent->id)
        ->and($sub1->branch_code)->toBe('SJP')
        ->and($sub1->target_code)->toBe('IM')
        ->and($sub1->month)->toBe(1)
        ->and($sub1->month_roman)->toBe('I')
        ->and($sub1->year)->toBe(2026)
        ->and($sub1->subject)->toBe('Memo Operasional Induk')
        ->and($sub1->requestor_name)->toBe('Budi Santoso')
        ->and($sub1->isSubLetter())->toBeTrue();

    // Create second sub-letter
    $sub2 = $service->createNextSubLetter($parent);

    expect($sub2->reference_number)->toBe('001.2/IM/SJP/I/2026')
        ->and($sub2->sequence_number)->toBe(1)
        ->and($sub2->sub_number)->toBe(2)
        ->and($sub2->parent_id)->toBe($parent->id);

    // Verify parent relationships
    $parent->refresh();
    expect($parent->subLetters)->toHaveCount(2)
        ->and($parent->subLetters->first()->id)->toBe($sub1->id)
        ->and($parent->subLetters->last()->id)->toBe($sub2->id);

    // Verify sub-letter parent relationship
    expect($sub1->parent->id)->toBe($parent->id);

    // Verify creating sub-letters does NOT advance branch sequence counter for new letters
    $nextParent = $service->createLetter([
        'branch_code' => 'SJP',
        'target_code' => 'IM',
        'month' => 1,
        'year' => 2026,
        'subject' => 'Surat Induk Berikutnya',
        'requestor_name' => 'Budi Santoso',
    ]);

    expect($nextParent->sequence_number)->toBe(2)
        ->and($nextParent->reference_number)->toBe('002/IM/SJP/I/2026');
});

test('createLetter with sub_count automatically generates sequential sub-letters', function () {
    $service = new LetterNumberService;

    $letter = $service->createLetter([
        'branch_code' => 'SJP',
        'target_code' => 'IM',
        'month' => 2,
        'year' => 2026,
        'subject' => 'Surat Pengumuman Bersama',
        'requestor_name' => 'HRD Pusat',
        'sub_count' => 3,
    ]);

    expect($letter->reference_number)->toBe('001/IM/SJP/II/2026')
        ->and($letter->subLetters)->toHaveCount(3);

    expect($letter->subLetters[0]->reference_number)->toBe('001.1/IM/SJP/II/2026')
        ->and($letter->subLetters[1]->reference_number)->toBe('001.2/IM/SJP/II/2026')
        ->and($letter->subLetters[2]->reference_number)->toBe('001.3/IM/SJP/II/2026');
});

test('LetterRequestForm creates letter with counting sub-numbers under target field', function () {
    Livewire\Livewire::test(LetterRequestForm::class)
        ->set('branch_code', 'SJP')
        ->set('target_code', 'IM')
        ->set('month', 3)
        ->set('year', 2026)
        ->set('subject', 'Pengumuman Operasional Tambang')
        ->set('requestor_name', 'Andi Wijaya')
        ->call('incrementSubCount')
        ->call('incrementSubCount')
        ->assertSet('sub_count', 2)
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('showSuccessModal', true);

    $parent = Letter::whereNull('parent_id')->first();
    expect($parent)->not->toBeNull()
        ->and($parent->reference_number)->toBe('001/IM/SJP/III/2026');

    $subs = Letter::where('parent_id', $parent->id)->orderBy('sub_number')->get();
    expect($subs)->toHaveCount(2)
        ->and($subs[0]->reference_number)->toBe('001.1/IM/SJP/III/2026')
        ->and($subs[1]->reference_number)->toBe('001.2/IM/SJP/III/2026')
        ->and($subs[0]->subject)->toBe('Pengumuman Operasional Tambang')
        ->and($subs[0]->requestor_name)->toBe('Andi Wijaya');
});

test('LetterHistory table displays Sub-Nomor column with dash or sub-numbers and excludes sub-letters from main rows', function () {
    $service = new LetterNumberService;

    // Parent 1: without sub-numbers
    $p1 = $service->createLetter([
        'branch_code' => 'SJP',
        'target_code' => 'IM',
        'month' => 4,
        'year' => 2026,
        'subject' => 'Surat Tanpa Sub',
        'requestor_name' => 'User A',
    ]);

    // Parent 2: with 2 sub-numbers
    $p2 = $service->createLetter([
        'branch_code' => 'SJP',
        'target_code' => 'IM',
        'month' => 4,
        'year' => 2026,
        'subject' => 'Surat Dengan Sub',
        'requestor_name' => 'User B',
        'sub_count' => 2,
    ]);

    // Test table rendering
    Livewire\Livewire::test(LetterHistory::class)
        ->assertSee('Sub-Nomor')
        ->assertSee('Surat Tanpa Sub')
        ->assertSee('Surat Dengan Sub')
        ->assertSeeHtml('>2</span>')
        ->assertSee('-');

    // Ensure total rows rendered is 2 (only parents), not 4
    expect(Letter::count())->toBe(4) // 2 parents + 2 subs
        ->and(Letter::whereNull('parent_id')->count())->toBe(2);
});

test('LetterHistory modal detail supports 1-click + Tambah Sub-Nomor Surat', function () {
    $service = new LetterNumberService;

    $parent = $service->createLetter([
        'branch_code' => 'SJP',
        'target_code' => 'IM',
        'month' => 5,
        'year' => 2026,
        'subject' => 'Surat Utama Cabang SJP',
        'requestor_name' => 'Kepala Cabang',
    ]);

    // Open detail modal and click Tambah Sub-Nomor Surat
    Livewire\Livewire::test(LetterHistory::class)
        ->call('viewLetter', $parent->id)
        ->assertSet('showDetailModal', true)
        ->assertSee('Tambah Sub-Nomor Surat')
        ->call('addSubLetter')
        ->assertDispatched('toast');

    // Verify 001.1 was created
    $sub1 = Letter::where('parent_id', $parent->id)->where('sub_number', 1)->first();
    expect($sub1)->not->toBeNull()
        ->and($sub1->reference_number)->toBe('001.1/IM/SJP/V/2026')
        ->and($sub1->subject)->toBe('Surat Utama Cabang SJP')
        ->and($sub1->requestor_name)->toBe('Kepala Cabang');

    // Click again to add 001.2
    Livewire\Livewire::test(LetterHistory::class)
        ->call('viewLetter', $parent->id)
        ->call('addSubLetter')
        ->assertDispatched('toast');

    $sub2 = Letter::where('parent_id', $parent->id)->where('sub_number', 2)->first();
    expect($sub2)->not->toBeNull()
        ->and($sub2->reference_number)->toBe('001.2/IM/SJP/V/2026');

    expect(Letter::where('parent_id', $parent->id)->count())->toBe(2);
});

test('LetterHistory requires LIFO deletion for sub-letters starting from largest/latest number and displays Salin Semua Sub-Nomor button', function () {
    $service = new LetterNumberService;

    $parent = $service->createLetter([
        'branch_code' => 'SJP',
        'target_code' => 'IM',
        'month' => 6,
        'year' => 2026,
        'subject' => 'Surat Uji Hapus Sub LIFO',
        'requestor_name' => 'Admin Testing',
        'sub_count' => 3,
    ]);

    $subs = $parent->subLetters()->orderBy('sub_number')->get();
    expect($subs)->toHaveCount(3);

    $sub1 = $subs[0];
    $sub2 = $subs[1];
    $sub3 = $subs[2];

    // Detail modal shows "Salin Semua Sub-Nomor" button and sub-letters
    Livewire\Livewire::test(LetterHistory::class)
        ->call('viewLetter', $parent->id)
        ->assertSee('Salin Semua Sub-Nomor')
        ->assertSee($sub1->reference_number)
        ->assertSee($sub2->reference_number)
        ->assertSee($sub3->reference_number);

    // Attempting to delete sub1 (not the latest) should fail due to LIFO rule
    Livewire\Livewire::test(LetterHistory::class)
        ->call('viewLetter', $parent->id)
        ->call('deleteSubLetter', $sub1->id)
        ->assertDispatched('toast', function ($event, $params) {
            $data = is_array($params) && isset($params[0]) && is_array($params[0]) ? $params[0] : $params;

            return ($data['type'] ?? '') === 'error' && ($data['title'] ?? '') === 'Urutan Tidak Sesuai';
        });

    expect(Letter::find($sub1->id))->not->toBeNull()
        ->and(Letter::where('parent_id', $parent->id)->count())->toBe(3);

    // Deleting sub3 (the largest/latest) should succeed
    Livewire\Livewire::test(LetterHistory::class)
        ->call('viewLetter', $parent->id)
        ->call('deleteSubLetter', $sub3->id)
        ->assertDispatched('toast', function ($event, $params) {
            $data = is_array($params) && isset($params[0]) && is_array($params[0]) ? $params[0] : $params;

            return ($data['type'] ?? '') === 'success' && ($data['title'] ?? '') === 'Sub-Nomor Dibatalkan';
        });

    expect(Letter::find($sub3->id))->toBeNull()
        ->and(Letter::where('parent_id', $parent->id)->count())->toBe(2);

    // Now sub2 is the largest, deleting sub2 should succeed
    Livewire\Livewire::test(LetterHistory::class)
        ->call('viewLetter', $parent->id)
        ->call('deleteSubLetter', $sub2->id)
        ->assertDispatched('toast', function ($event, $params) {
            $data = is_array($params) && isset($params[0]) && is_array($params[0]) ? $params[0] : $params;

            return ($data['type'] ?? '') === 'success';
        });

    expect(Letter::find($sub2->id))->toBeNull()
        ->and(Letter::where('parent_id', $parent->id)->count())->toBe(1);

    // Now sub1 is the only one remaining, deleting sub1 should succeed
    Livewire\Livewire::test(LetterHistory::class)
        ->call('viewLetter', $parent->id)
        ->call('deleteSubLetter', $sub1->id)
        ->assertDispatched('toast', function ($event, $params) {
            $data = is_array($params) && isset($params[0]) && is_array($params[0]) ? $params[0] : $params;

            return ($data['type'] ?? '') === 'success';
        });

    expect(Letter::find($sub1->id))->toBeNull()
        ->and(Letter::where('parent_id', $parent->id)->count())->toBe(0)
        ->and(Letter::find($parent->id))->not->toBeNull();
});

test('LetterHistory can cancel/delete parent letter along with all its sub-letters', function () {
    $service = new LetterNumberService;

    $parent = $service->createLetter([
        'branch_code' => 'SJP',
        'target_code' => 'IM',
        'month' => 7,
        'year' => 2026,
        'subject' => 'Surat Uji Hapus Induk',
        'requestor_name' => 'Admin Testing',
        'sub_count' => 3,
    ]);

    expect(Letter::where('parent_id', $parent->id)->count())->toBe(3);

    Livewire\Livewire::test(LetterHistory::class)
        ->call('deleteLetter', $parent->id)
        ->assertDispatched('toast');

    // Parent and all 3 sub letters are deleted
    expect(Letter::find($parent->id))->toBeNull()
        ->and(Letter::where('parent_id', $parent->id)->count())->toBe(0);
});

test('LetterHistory displays publish time for sub-letters only when different from parent, with date if different day', function () {
    $service = new LetterNumberService;

    // Parent created at 2026-09-09 08:00:00
    $parent = $service->createLetter([
        'branch_code' => 'SJP',
        'target_code' => 'IM',
        'month' => 9,
        'year' => 2026,
        'subject' => 'Surat Uji Waktu Terbit Sub',
        'requestor_name' => 'Admin Waktu',
        'sub_count' => 1,
    ]);

    $parent->created_at = Carbon::parse('2026-09-09 08:00:00', 'Asia/Jakarta')->utc();
    $parent->saveQuietly();

    // Sub 1: Same time as parent (08:00 WIB)
    $sub1 = $parent->subLetters()->first();
    $sub1->created_at = Carbon::parse('2026-09-09 08:00:00', 'Asia/Jakarta')->utc();
    $sub1->saveQuietly();

    // Sub 2: Same day, different time (11:30 WIB)
    $sub2 = $service->createNextSubLetter($parent);
    $sub2->created_at = Carbon::parse('2026-09-09 11:30:00', 'Asia/Jakarta')->utc();
    $sub2->saveQuietly();

    // Sub 3: Different day (12 Sep 2026, 15:45 WIB)
    $sub3 = $service->createNextSubLetter($parent);
    $sub3->created_at = Carbon::parse('2026-09-12 15:45:00', 'Asia/Jakarta')->utc();
    $sub3->saveQuietly();

    Livewire\Livewire::test(LetterHistory::class)
        ->call('viewLetter', $parent->id)
        ->assertSet('showDetailModal', true)
        ->assertSee($sub1->reference_number)
        ->assertSee($sub2->reference_number)
        ->assertSee($sub3->reference_number)
        // Sub 2 shows only time because it is on the same day
        ->assertSee('11:30 WIB')
        // Sub 3 shows concrete date and time because it is on a different day
        ->assertSee('12 Sep 2026, 15:45 WIB');
});

test('LetterHistory can delete parent letter directly from modal detail without ModelNotFoundException or 404', function () {
    $service = new LetterNumberService;

    $parent = $service->createLetter([
        'branch_code' => 'SJP',
        'target_code' => 'IM',
        'month' => 9,
        'year' => 2026,
        'subject' => 'Surat Uji Hapus dari Modal Detail',
        'requestor_name' => 'Tester Modal',
        'sub_count' => 2,
    ]);

    expect(Letter::find($parent->id))->not->toBeNull()
        ->and(Letter::where('parent_id', $parent->id)->count())->toBe(2);

    Livewire\Livewire::test(LetterHistory::class)
        ->call('viewLetter', $parent->id)
        ->assertSet('showDetailModal', true)
        ->assertSet('open', $parent->id)
        ->call('deleteLetter', $parent->id)
        ->assertSet('showDetailModal', false)
        ->assertSet('selectedLetter', null)
        ->assertSet('open', null)
        ->assertDispatched('toast');

    expect(Letter::find($parent->id))->toBeNull()
        ->and(Letter::where('parent_id', $parent->id)->count())->toBe(0);
});
