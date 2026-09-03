<?php

use App\Models\AdminActionLog;
use App\Models\User;

test('créer un commerçant enregistre une entrée dans le journal admin', function () {
    $admin = User::factory()->admin()->create(['name' => 'Admin Principal']);

    $this->actingAs($admin)->post('/admin/users', [
        'name' => 'Nouveau Commerçant',
        'email' => 'nouveau@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertDatabaseHas('admin_action_logs', [
        'admin_name' => 'Admin Principal',
        'action' => 'creation',
        'target_name' => 'Nouveau Commerçant',
        'target_email' => 'nouveau@example.com',
    ]);
});

test('suspendre puis réactiver un commerçant enregistre deux entrées distinctes', function () {
    $admin = User::factory()->admin()->create();
    $commercant = User::factory()->commercant()->create();

    $this->actingAs($admin)->post("/admin/users/{$commercant->id}/suspend");
    $this->actingAs($admin)->post("/admin/users/{$commercant->id}/suspend");

    $this->assertDatabaseHas('admin_action_logs', ['action' => 'suspension', 'target_user_id' => $commercant->id]);
    $this->assertDatabaseHas('admin_action_logs', ['action' => 'reactivation', 'target_user_id' => $commercant->id]);
});

test('supprimer un commerçant enregistre une entrée avant que le compte ne soit soft-deleted', function () {
    $admin = User::factory()->admin()->create();
    $commercant = User::factory()->commercant()->create(['name' => 'À supprimer']);

    $this->actingAs($admin)->delete("/admin/users/{$commercant->id}");

    $this->assertDatabaseHas('admin_action_logs', [
        'action' => 'suppression',
        'target_user_id' => $commercant->id,
        'target_name' => 'À supprimer',
    ]);
});

test('modifier l’email d’un commerçant enregistre le changement dans les détails', function () {
    $admin = User::factory()->admin()->create();
    $commercant = User::factory()->commercant()->create(['name' => 'Vieux Nom', 'email' => 'vieux@example.com']);

    $this->actingAs($admin)->put("/admin/users/{$commercant->id}", [
        'name' => 'Nouveau Nom',
        'email' => 'nouveau@example.com',
    ]);

    $log = AdminActionLog::where('target_user_id', $commercant->id)->where('action', 'modification')->firstOrFail();
    expect($log->details)->toContain('nouveau@example.com');
});

test('un commerçant ne peut pas accéder au journal admin', function () {
    $commercant = User::factory()->commercant()->create();

    $response = $this->actingAs($commercant)->get('/admin/journal');

    $response->assertForbidden();
});

test('le journal admin liste les entrées les plus récentes en premier', function () {
    $admin = User::factory()->admin()->create();
    $commercant = User::factory()->commercant()->create();

    AdminActionLog::record($admin, 'creation', $commercant, 'premier');
    AdminActionLog::record($admin, 'modification', $commercant, 'deuxieme');

    $response = $this->actingAs($admin)->get('/admin/journal');

    $response->assertOk();
    $response->assertSeeInOrder(['deuxieme', 'premier']);
});
