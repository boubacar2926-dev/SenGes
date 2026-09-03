<?php

use App\Models\User;

// --- Session active + compte suspendu en cours de route ---

test('un compte suspendu pendant qu’il est connecté perd immédiatement l’accès', function () {
    $commercant = User::factory()->commercant()->create();

    // Session valide établie avant la suspension.
    $this->actingAs($commercant)
        ->get('/commercant/dashboard')
        ->assertOk();

    $commercant->update(['suspended' => true]);

    // La même session ne doit plus donner accès à une route protégée par "auth".
    $response = $this->actingAs($commercant)->get('/profile');
    $response->assertRedirect('/login');
    $this->assertGuest();
});

test('un compte suspendu pendant qu’il est connecté perd l’accès aux routes par rôle', function () {
    $commercant = User::factory()->commercant()->create();

    $commercant->update(['suspended' => true]);

    $response = $this->actingAs($commercant)->get('/commercant/dashboard');
    $response->assertRedirect('/login');
    $this->assertGuest();
});

test('admin suspendu perd aussi l’accès immédiatement', function () {
    $admin = User::factory()->admin()->create();

    $admin->update(['suspended' => true]);

    $response = $this->actingAs($admin)->get('/admin/dashboard');
    $response->assertRedirect('/login');
    $this->assertGuest();
});

// --- Rate limiting sur l'inscription ---

test('l’inscription est limitée en nombre de tentatives par minute', function () {
    for ($i = 0; $i < 6; $i++) {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => "test{$i}@example.com",
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $this->post('/logout');
    }

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test-throttled@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertStatus(429);
});
