<?php

use App\Livewire\TargetManagement;
use App\Models\Letter;
use App\Models\LetterTarget;
use Database\Seeders\LetterTargetSeeder;
use Livewire\Livewire;

beforeEach(function () {
    (new LetterTargetSeeder)->run();

    // Default admin session
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

test('admin can access target management page and see CRUD controls', function () {
    $this->get(route('target.management'))
        ->assertOk()
        ->assertSee('Pengaturan Master Tujuan Surat')
        ->assertSee('Tambah Tujuan Baru')
        ->assertSee('Internal Memo');
});

test('karyawan can access target management page in read-only mode', function () {
    session([
        'auth_sso' => [
            'type' => 'karyawan',
            'role' => 'karyawan',
            'nik' => '1771012501030004',
            'name' => 'Karim Staff',
            'branch_code' => 'SJP',
            'department_name' => 'IT',
            'position_name' => 'Staff',
        ],
    ]);

    $this->get(route('target.management'))
        ->assertOk()
        ->assertSee('Daftar Tujuan Surat Resmi')
        ->assertDontSee('Tambah Tujuan Baru');

    Livewire::test(TargetManagement::class)
        ->assertSet('isAdmin', false)
        ->assertDontSee('Tambah Tujuan Baru');
});

test('admin can create a new standard target', function () {
    Livewire::test(TargetManagement::class)
        ->set('code', 'SKK')
        ->set('name', 'Surat Keterangan Kerja')
        ->set('description', 'Untuk keperluan pengajuan visa atau perbankan')
        ->set('is_active', true)
        ->call('createTarget')
        ->assertHasNoErrors()
        ->assertSet('showCreateModal', false);

    $this->assertDatabaseHas('letter_targets', [
        'code' => 'SKK',
        'name' => 'Surat Keterangan Kerja',
        'is_active' => true,
    ]);
});

test('validates unique target code on creation', function () {
    Livewire::test(TargetManagement::class)
        ->set('code', 'IM') // IM already seeded
        ->set('name', 'Internal Memo Duplikat')
        ->call('createTarget')
        ->assertHasErrors(['code']);
});

test('admin can update an existing target', function () {
    $target = LetterTarget::where('code', 'IM')->firstOrFail();

    Livewire::test(TargetManagement::class)
        ->call('openEditModal', $target->id)
        ->assertSet('editingTargetId', $target->id)
        ->assertSet('code', 'IM')
        ->set('name', 'Internal Memo Perusahaan')
        ->call('updateTarget')
        ->assertHasNoErrors()
        ->assertSet('showEditModal', false);

    $this->assertDatabaseHas('letter_targets', [
        'id' => $target->id,
        'name' => 'Internal Memo Perusahaan',
    ]);
});

test('admin can toggle target active status', function () {
    $target = LetterTarget::where('code', 'ND')->firstOrFail();
    expect($target->is_active)->toBeTrue();

    Livewire::test(TargetManagement::class)
        ->call('toggleActive', $target->id);

    expect($target->fresh()->is_active)->toBeFalse();

    Livewire::test(TargetManagement::class)
        ->call('toggleActive', $target->id);

    expect($target->fresh()->is_active)->toBeTrue();
});

test('admin can delete unused target', function () {
    $target = LetterTarget::create([
        'code' => 'TEMP',
        'name' => 'Target Sementara',
        'is_active' => false,
    ]);

    Livewire::test(TargetManagement::class)
        ->call('deleteTarget', $target->id);

    $this->assertDatabaseMissing('letter_targets', [
        'id' => $target->id,
    ]);
});

test('admin cannot delete target that has been used in letters', function () {
    $target = LetterTarget::where('code', 'IM')->firstOrFail();

    Letter::factory()->create([
        'reference_number' => '001/IM/SJP/IX/2026',
        'target_code' => 'IM',
        'branch_code' => 'SJP',
    ]);

    Livewire::test(TargetManagement::class)
        ->call('deleteTarget', $target->id)
        ->assertDispatched('toast');

    $this->assertDatabaseHas('letter_targets', [
        'id' => $target->id,
    ]);
});

test('karyawan is prevented from mutating target operations', function () {
    session([
        'auth_sso' => [
            'type' => 'karyawan',
            'role' => 'karyawan',
            'name' => 'Karim',
        ],
    ]);

    $target = LetterTarget::where('code', 'IM')->firstOrFail();

    Livewire::test(TargetManagement::class)
        ->call('createTarget')
        ->assertDispatched('toast')
        ->call('toggleActive', $target->id)
        ->assertDispatched('toast')
        ->call('deleteTarget', $target->id)
        ->assertDispatched('toast');
});

test('can search targets by code or name', function () {
    Livewire::test(TargetManagement::class)
        ->set('search', 'Nota')
        ->assertSee('Nota Dinas')
        ->assertDontSee('Surat Keputusan');
});
