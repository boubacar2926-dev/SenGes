<?php

use App\Models\Produit;
use App\Models\Transaction;
use App\Models\User;

// --- Suppression d'un produit : l'historique des ventes doit survivre ---

test('supprimer un produit déjà vendu conserve ses transactions en base', function () {
    $commercant = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($commercant)->create(['quantite' => 20, 'prix' => 1000]);
    $this->actingAs($commercant)->post('/transactions', [
        'items' => [['produit_id' => $produit->id, 'quantite' => 2]],
    ]);
    $transaction = Transaction::first();

    $this->actingAs($commercant)->delete("/produits/{$produit->id}");

    $this->assertSoftDeleted('produits', ['id' => $produit->id]);
    $this->assertDatabaseHas('transactions', ['id' => $transaction->id, 'produit_id' => $produit->id]);
});

test('la facture d’une vente reste consultable après suppression du produit vendu', function () {
    $commercant = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($commercant)->create(['nom' => 'Sac de riz', 'quantite' => 20, 'prix' => 1000]);
    $this->actingAs($commercant)->post('/transactions', [
        'items' => [['produit_id' => $produit->id, 'quantite' => 2]],
    ]);
    $groupeId = Transaction::first()->groupe_id;

    $this->actingAs($commercant)->delete("/produits/{$produit->id}");

    $response = $this->actingAs($commercant)->get("/transactions/facture/{$groupeId}");

    $response->assertOk();
    $response->assertSee('Sac de riz');
});

test('un produit supprimé n’apparaît plus dans la liste des produits ni dans le formulaire de vente', function () {
    $commercant = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($commercant)->create(['nom' => 'Produit obsolète']);

    $this->actingAs($commercant)->delete("/produits/{$produit->id}");

    $index = $this->actingAs($commercant)->get('/produits');
    $index->assertDontSee('Produit obsolète');

    $create = $this->actingAs($commercant)->get('/transactions/create');
    $create->assertDontSee('Produit obsolète');
});

test('on ne peut plus vendre un produit déjà supprimé', function () {
    $commercant = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($commercant)->create(['quantite' => 20]);
    $produit->delete();

    $response = $this->actingAs($commercant)->post('/transactions', [
        'items' => [['produit_id' => $produit->id, 'quantite' => 1]],
    ]);

    $response->assertSessionHasErrors(['items.0.produit_id']);
    $this->assertDatabaseCount('transactions', 0);
});

// --- Suppression d'un commerçant par l'admin : l'historique doit survivre ---

test('supprimer un commerçant conserve ses produits et transactions en base', function () {
    $admin = User::factory()->admin()->create();
    $commercant = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($commercant)->create(['quantite' => 20, 'prix' => 1000]);
    $this->actingAs($commercant)->post('/transactions', [
        'items' => [['produit_id' => $produit->id, 'quantite' => 1]],
    ]);
    $transaction = Transaction::first();

    $this->actingAs($admin)->delete("/admin/users/{$commercant->id}");

    $this->assertSoftDeleted('users', ['id' => $commercant->id]);
    $this->assertDatabaseHas('produits', ['id' => $produit->id]);
    $this->assertDatabaseHas('transactions', ['id' => $transaction->id]);
});

test('un commerçant supprimé ne peut plus se connecter', function () {
    $admin = User::factory()->admin()->create();
    $commercant = User::factory()->commercant()->create(['password' => bcrypt('password')]);

    $this->actingAs($admin)->delete("/admin/users/{$commercant->id}");
    $this->post('/logout');

    $response = $this->post('/login', [
        'email' => $commercant->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
});
