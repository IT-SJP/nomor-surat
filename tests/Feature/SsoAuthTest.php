<?php

use Illuminate\Support\Facades\Config;

test('unauthenticated users are rejected with 403 access denied screen', function () {
    $response = $this->get('/');
    $response->assertStatus(403);
    $response->assertSee('Portal Nomor Surat Internal');
    $response->assertSee('Akses Terkunci (403)');
});

test('sso verification fails if token or signature is missing', function () {
    $response = $this->get('/sso/verify');
    $response->assertStatus(403);
    $response->assertSee('Parameter autentikasi SSO tidak lengkap.');
});

test('sso verification fails if signature is invalid', function () {
    $payload = base64_encode(json_encode([
        'role' => 'karyawan',
        'nik' => '1771055705020001',
        'name' => 'Alfiyyah Nuur Fadhilah',
        'branch_code' => 'SJK',
        'exp' => now()->addHour()->timestamp,
    ]));

    $response = $this->get("/sso/verify?token={$payload}&sig=invalid-signature");
    $response->assertStatus(403);
    $response->assertSee('Tanda tangan digital (signature) SSO tidak valid');
});

test('sso verification succeeds for karyawan and redirects to letter request page', function () {
    $secret = 'sjp-holding-secret-sso-key-2026';
    Config::set('services.sso.secret', $secret);

    $rawPayload = [
        'type' => 'karyawan',
        'role' => 'karyawan',
        'nik' => '1771055705020001',
        'name' => 'Alfiyyah Nuur Fadhilah',
        'branch_code' => 'SJK',
        'branch_name' => 'PT. SELAMAT JAYA KONSTRUKSI',
        'exp' => now()->addHour()->timestamp,
    ];

    $payload = base64_encode(json_encode($rawPayload));
    $sig = hash_hmac('sha256', $payload, $secret);

    $response = $this->get("/sso/verify?token={$payload}&sig={$sig}");

    $response->assertRedirect(route('letter.request'));
    $response->assertSessionHas('auth_sso');
    expect(session('auth_sso.role'))->toBe('karyawan');
    expect(session('auth_sso.branch_code'))->toBe('SJK');
    expect(session('auth_sso.nik'))->toBe('1771055705020001');
});

test('sso verification succeeds for admin and redirects to dashboard', function () {
    $secret = 'sjp-holding-secret-sso-key-2026';
    Config::set('services.sso.secret', $secret);

    $rawPayload = [
        'type' => 'admin',
        'role' => 'admin',
        'name' => 'Admin SJP',
        'email' => 'admin@sjp.co.id',
        'branch_code' => 'SJP',
        'exp' => now()->addHour()->timestamp,
    ];

    $payload = base64_encode(json_encode($rawPayload));
    $sig = hash_hmac('sha256', $payload, $secret);

    $response = $this->get("/sso/verify?token={$payload}&sig={$sig}");

    $response->assertRedirect(route('dashboard'));
    $response->assertSessionHas('auth_sso');
    expect(session('auth_sso.role'))->toBe('admin');
});

test('karyawan accessing dashboard is redirected to letter request page', function () {
    $this->withSession([
        'auth_sso' => [
            'type' => 'karyawan',
            'role' => 'karyawan',
            'nik' => '1771055705020001',
            'name' => 'Alfiyyah Nuur Fadhilah',
            'branch_code' => 'SJK',
        ],
    ]);

    $response = $this->get('/dashboard');
    $response->assertRedirect(route('letter.request'));
});
