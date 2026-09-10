<?php

test('unauthenticated access to root shows 403 access denied screen', function () {
    $response = $this->get('/');

    $response->assertStatus(403);
    $response->assertSee('403 FORBIDDEN');
    $response->assertSee('Akses Tidak Diizinkan');
});
