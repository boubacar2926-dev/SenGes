<?php

use App\Models\User;

test('un admin peut créer un commerçant', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post('/admin/users', [
        'name' => 'Nouveau Commerçant',
        'email' => 'nouveau@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect('/admin/users');
    $this->assertDatabaseHas('users', [
        'email' => 'nouveau@example.com',
        'role' => 'commercant',
        'suspended' => false,
    ]);
});

test('un non-admin ne peut pas accéder à la gestion des commerçants', function () {
    $commercant = User::factory()->commercant()->create();

    $response = $this->actingAs($commercant)->get('/admin/users');

    $response->assertForbidden();
});

test('suspendre un commerçant l’empêche de se connecter', function () {
    $admin = User::factory()->admin()->create();
    $commercant = User::factory()->commercant()->create(['password' => bcrypt('password')]);

    $this->actingAs($admin)->post("/admin/users/{$commercant->id}/suspend");

    expect($commercant->fresh()->suspended)->toBeTrue();

    $this->post('/logout');

    $response = $this->post('/login', [
        'email' => $commercant->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
});

test('réactiver un commerçant suspendu lui permet de nouveau de se connecter', function () {
    $admin = User::factory()->admin()->create();
    $commercant = User::factory()->commercant()->suspended()->create(['password' => bcrypt('password')]);

    $this->actingAs($admin)->post("/admin/users/{$commercant->id}/suspend");

    expect($commercant->fresh()->suspended)->toBeFalse();

    $response = $this->post('/login', [
        'email' => $commercant->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
});
