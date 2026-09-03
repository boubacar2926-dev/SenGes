<?php

use App\Models\Produit;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// --- IDOR : un admin ne doit pas pouvoir agir sur un autre compte admin ---

test('un admin ne peut pas éditer un autre compte admin via la route commerçants', function () {
    $admin = User::factory()->admin()->create();
    $autreAdmin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get("/admin/users/{$autreAdmin->id}/edit");

    $response->assertForbidden();
});

test('un admin ne peut pas mettre à jour un autre compte admin via la route commerçants', function () {
    $admin = User::factory()->admin()->create();
    $autreAdmin = User::factory()->admin()->create(['name' => 'Admin Victime']);

    $response = $this->actingAs($admin)->put("/admin/users/{$autreAdmin->id}", [
        'name' => 'Nom Modifié',
        'email' => $autreAdmin->email,
    ]);

    $response->assertForbidden();
    expect($autreAdmin->fresh()->name)->toBe('Admin Victime');
});

test('un admin ne peut pas supprimer un autre compte admin', function () {
    $admin = User::factory()->admin()->create();
    $autreAdmin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->delete("/admin/users/{$autreAdmin->id}");

    $response->assertForbidden();
    $this->assertDatabaseHas('users', ['id' => $autreAdmin->id]);
});

test('un admin ne peut pas suspendre un autre compte admin', function () {
    $admin = User::factory()->admin()->create();
    $autreAdmin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post("/admin/users/{$autreAdmin->id}/suspend");

    $response->assertForbidden();
    expect($autreAdmin->fresh()->suspended)->toBeFalse();
});

// --- Mass assignment : mise à jour d'un commerçant par un admin ---

test('un admin ne peut pas changer le rôle ou le statut suspendu d’un commerçant via le formulaire de mise à jour', function () {
    $admin = User::factory()->admin()->create();
    $commercant = User::factory()->commercant()->create();

    $response = $this->actingAs($admin)->put("/admin/users/{$commercant->id}", [
        'name' => 'Nom Mis à Jour',
        'email' => $commercant->email,
        'role' => 'admin',
        'suspended' => true,
        'password' => 'hackedpassword',
    ]);

    $response->assertRedirect('/admin/users');
    $fresh = $commercant->fresh();
    expect($fresh->role)->toBe('commercant');
    expect($fresh->suspended)->toBeFalse();
    expect(Hash::check('hackedpassword', $fresh->password))->toBeFalse();
});

// --- Mass assignment : un utilisateur ne peut pas s'auto-élever via son profil ---

test('un commerçant ne peut pas s’attribuer le rôle admin via la mise à jour de son profil', function () {
    $commercant = User::factory()->commercant()->create();

    $response = $this->actingAs($commercant)->patch('/profile', [
        'name' => 'Nom Test',
        'email' => $commercant->email,
        'role' => 'admin',
        'suspended' => false,
    ]);

    $response->assertSessionHasNoErrors();
    expect($commercant->fresh()->role)->toBe('commercant');
});

// --- Isolation multi-tenant : dashboard et statistiques ---

test('un commerçant ne voit pas les revenus d’un autre commerçant sur le dashboard', function () {
    $commercant = User::factory()->commercant()->create();
    $autre = User::factory()->commercant()->create();
    $produitAutre = Produit::factory()->for($autre)->create(['nom' => 'Produit Confidentiel Autre']);
    Transaction::factory()->create([
        'user_id' => $autre->id,
        'produit_id' => $produitAutre->id,
        'total' => 999999,
    ]);

    $response = $this->actingAs($commercant)->get('/dashboard');

    $response->assertOk();
    $response->assertDontSee('Produit Confidentiel Autre');
    $response->assertDontSee('999999');
});

test('un commerçant ne voit pas le total des ventes d’un autre commerçant sur les statistiques', function () {
    $commercant = User::factory()->commercant()->create();
    $autre = User::factory()->commercant()->create();
    $produitAutre = Produit::factory()->for($autre)->create(['nom' => 'Produit Secret Stats']);
    Transaction::factory()->create([
        'user_id' => $autre->id,
        'produit_id' => $produitAutre->id,
        'total' => 123456,
    ]);

    $response = $this->actingAs($commercant)->get('/statistiques');

    $response->assertOk();
    $response->assertDontSee('Produit Secret Stats');
    $response->assertSee('0 FCFA');
});

// --- XSS stocké dans les graphiques du dashboard (json_encode dans un <script>) ---

test('un nom de produit ne peut pas casser hors du bloc script du dashboard', function () {
    $commercant = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($commercant)->create([
        'nom' => '</script><script>alert(1)</script>',
    ]);
    Transaction::factory()->create([
        'user_id' => $commercant->id,
        'produit_id' => $produit->id,
    ]);

    $response = $this->actingAs($commercant)->get('/dashboard');

    $response->assertOk();
    $response->assertDontSee('</script><script>alert(1)</script>', false);
});

// --- Suppression de compte : confirmation du mot de passe côté serveur ---

test('la suppression du compte échoue sans le bon mot de passe même avec un champ password falsifié absent', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->delete('/profile', []);

    $response->assertSessionHasErrorsIn('userDeletion', 'password');
    $this->assertNotNull($user->fresh());
});

// --- Accès admin réservé ---

test('un commerçant ne peut pas accéder au tableau de bord admin', function () {
    $commercant = User::factory()->commercant()->create();

    $response = $this->actingAs($commercant)->get('/admin/dashboard');

    $response->assertForbidden();
});

test('un commerçant ne peut pas créer un nouveau commerçant via une requête directe', function () {
    $commercant = User::factory()->commercant()->create();

    $response = $this->actingAs($commercant)->post('/admin/users', [
        'name' => 'Injection',
        'email' => 'injection@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertForbidden();
    $this->assertDatabaseMissing('users', ['email' => 'injection@example.com']);
});
