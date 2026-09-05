<?php

use App\Livewire\BranchManagement;
use App\Models\Branch;
use App\Models\Letter;
use Livewire\Livewire;

beforeEach(function () {
    $this->withSession([
        'auth_sso' => [
            'type' => 'admin',
            'role' => 'administrator',
            'admin_role' => 'administrator',
            'name' => 'Super Admin SJP',
            'branch_code' => 'SJP',
            'branch_name' => 'PT Selamat Jaya Persada',
        ],
    ]);
});

test('branch management page renders successfully for admin', function () {
    $this->get(route('branch.management'))
        ->assertOk()
        ->assertSee('Pengaturan Cabang');
});

test('branch management page redirects for karyawan', function () {
    $this->withSession([
        'auth_sso' => [
            'type' => 'karyawan',
            'role' => 'karyawan',
        ],
    ]);

    $this->get(route('branch.management'))
        ->assertRedirect(route('dashboard'));
});

test('can toggle branch active status via livewire component for administrator', function () {
    $branch = Branch::create([
        'hr_code' => 'ABC',
        'branch_code' => 'ABC',
        'name' => 'Cabang ABC',
        'is_active' => true,
    ]);

    Livewire::test(BranchManagement::class)
        ->call('toggleActive', $branch->id)
        ->assertHasNoErrors();

    expect($branch->fresh()->is_active)->toBeFalse();
});

test('hrd role can also manage branches', function () {
    $this->withSession([
        'auth_sso' => [
            'type' => 'admin',
            'role' => 'admin',
            'admin_role' => 'hrd',
            'name' => 'HRD Pusat',
        ],
    ]);

    $branch = Branch::create([
        'hr_code' => 'HRD01',
        'branch_code' => 'HRD01',
        'name' => 'Cabang HRD Test',
        'is_active' => true,
    ]);

    Livewire::test(BranchManagement::class)
        ->assertSet('canManageBranches', true)
        ->call('toggleActive', $branch->id)
        ->assertHasNoErrors();

    expect($branch->fresh()->is_active)->toBeFalse();
});

test('can update branch code via livewire component', function () {
    $branch = Branch::create([
        'hr_code' => 'XYZ',
        'branch_code' => 'XYZ',
        'name' => 'Cabang XYZ',
        'is_active' => true,
    ]);

    Livewire::test(BranchManagement::class)
        ->call('updateBranchCode', $branch->id, 'NEW_XYZ')
        ->assertHasNoErrors();

    expect($branch->fresh()->branch_code)->toBe('NEW_XYZ');
});

test('can delete branch via livewire component when no letters exist', function () {
    $branch = Branch::create([
        'hr_code' => 'DEL01',
        'branch_code' => 'DEL01',
        'name' => 'Cabang Dihapus',
        'is_active' => true,
    ]);

    Livewire::test(BranchManagement::class)
        ->call('deleteBranch', $branch->id)
        ->assertDispatched('toast');

    expect(Branch::find($branch->id))->toBeNull();
});

test('cannot delete branch when letters exist', function () {
    $branch = Branch::create([
        'hr_code' => 'DEL02',
        'branch_code' => 'DEL02',
        'name' => 'Cabang Ada Surat',
        'is_active' => true,
    ]);

    Letter::create([
        'reference_number' => 'DEL02/IX/2026/001',
        'sequence_number' => 1,
        'branch_code' => 'DEL02',
        'branch_name' => 'Cabang Ada Surat',
        'target_code' => 'INSTANSI',
        'month_roman' => 'IX',
        'month' => 9,
        'year' => 2026,
        'subject' => 'Surat Uji',
        'purpose' => 'Pengujian',
        'requestor_name' => 'Tester',
    ]);

    Livewire::test(BranchManagement::class)
        ->call('deleteBranch', $branch->id)
        ->assertDispatched('toast');

    expect(Branch::find($branch->id))->not->toBeNull();
});

test('admin cabang only sees their own branch and can edit and delete it', function () {
    $ownBranch = Branch::create([
        'hr_code' => 'CBG01',
        'branch_code' => 'CBG01',
        'name' => 'Cabang Ketahun',
        'is_active' => true,
    ]);

    $otherBranch = Branch::create([
        'hr_code' => 'CBG02',
        'branch_code' => 'CBG02',
        'name' => 'Cabang Jambi',
        'is_active' => true,
    ]);

    $this->withSession([
        'auth_sso' => [
            'type' => 'admin cabang',
            'role' => 'admin cabang',
            'admin_role' => 'admin cabang',
            'name' => 'HRD SITE KETAHUN',
            'raw_branch_code' => 'CBG01',
            'branch_code' => 'CBG01',
            'branch_name' => 'Cabang Ketahun',
        ],
    ]);

    $response = $this->get(route('branch.management'));
    $response->assertOk()
        ->assertSee('Cabang Ketahun')
        ->assertDontSee('Cabang Jambi')
        ->assertSee('title="Edit Kode"', false)
        ->assertSee('title="Hapus Cabang dari Nomor Surat"', false);

    Livewire::test(BranchManagement::class)
        ->assertSet('canManageBranches', true)
        ->assertSet('isAdminCabang', true)
        ->call('updateBranchCode', $ownBranch->id, 'KTN01')
        ->assertDispatched('toast');

    expect($ownBranch->fresh()->branch_code)->toBe('KTN01');

    // Admin cabang can also toggle active status of their own branch
    Livewire::test(BranchManagement::class)
        ->call('toggleActive', $ownBranch->id)
        ->assertDispatched('toast');

    expect($ownBranch->fresh()->is_active)->toBeFalse();

    // Admin cabang can delete their own branch if no letters exist
    Livewire::test(BranchManagement::class)
        ->call('deleteBranch', $ownBranch->id)
        ->assertDispatched('toast');

    expect(Branch::find($ownBranch->id))->toBeNull();
});

test('admin cabang cannot modify or delete other branches', function () {
    $ownBranch = Branch::create([
        'hr_code' => 'CBG01',
        'branch_code' => 'CBG01',
        'name' => 'Cabang Ketahun',
        'is_active' => true,
    ]);

    $otherBranch = Branch::create([
        'hr_code' => 'CBG02',
        'branch_code' => 'CBG02',
        'name' => 'Cabang Jambi',
        'is_active' => true,
    ]);

    $this->withSession([
        'auth_sso' => [
            'type' => 'admin cabang',
            'role' => 'admin cabang',
            'admin_role' => 'admin cabang',
            'name' => 'HRD SITE KETAHUN',
            'raw_branch_code' => 'CBG01',
            'branch_code' => 'CBG01',
            'branch_name' => 'Cabang Ketahun',
        ],
    ]);

    // Attempt to update other branch code
    Livewire::test(BranchManagement::class)
        ->call('updateBranchCode', $otherBranch->id, 'HACKED')
        ->assertNotDispatched('toast');

    expect($otherBranch->fresh()->branch_code)->toBe('CBG02');

    // Attempt to toggle other branch active
    Livewire::test(BranchManagement::class)
        ->call('toggleActive', $otherBranch->id)
        ->assertNotDispatched('toast');

    expect($otherBranch->fresh()->is_active)->toBeTrue();

    // Attempt to delete other branch
    Livewire::test(BranchManagement::class)
        ->call('deleteBranch', $otherBranch->id)
        ->assertNotDispatched('toast');

    expect(Branch::find($otherBranch->id))->not->toBeNull();
});

test('other admin roles like owner and staff it have read-only view and cannot edit', function () {
    $branch = Branch::create([
        'hr_code' => 'RO01',
        'branch_code' => 'RO01',
        'name' => 'Cabang Readonly',
        'is_active' => true,
    ]);

    $this->withSession([
        'auth_sso' => [
            'type' => 'admin',
            'role' => 'admin',
            'admin_role' => 'owner',
            'name' => 'Dedeng Marco Saputra',
        ],
    ]);

    Livewire::test(BranchManagement::class)
        ->assertSet('canManageBranches', false)
        ->call('updateBranchCode', $branch->id, 'HACKED')
        ->assertNotDispatched('toast');

    expect($branch->fresh()->branch_code)->toBe('RO01');

    $this->withSession([
        'auth_sso' => [
            'type' => 'admin',
            'role' => 'admin',
            'admin_role' => 'staff it',
            'name' => 'Admin IT',
        ],
    ]);

    Livewire::test(BranchManagement::class)
        ->assertSet('canManageBranches', false)
        ->call('deleteBranch', $branch->id)
        ->assertNotDispatched('toast');

    expect(Branch::find($branch->id))->not->toBeNull();
});
