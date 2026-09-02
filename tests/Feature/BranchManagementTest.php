<?php

use App\Livewire\BranchManagement;
use App\Models\Branch;
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

test('can toggle branch active status via livewire component', function () {
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
