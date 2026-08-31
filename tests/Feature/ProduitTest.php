<?php

use App\Models\Produit;
use App\Models\User;

test('un commerçant peut créer un produit', function () {
    $commercant = User::factory()->commercant()->create();

    $response = $this->actingAs($commercant)->post('/produits', [
        'nom' => 'Sac de riz',
        'description' => '50kg',
        'prix' => 25000,
        'quantite' => 10,
    ]);

    $response->assertRedirect('/produits');
    $this->assertDatabaseHas('produits', [
        'nom' => 'Sac de riz',
        'user_id' => $commercant->id,
    ]);
});

test('la création d’un produit échoue sans les champs obligatoires', function () {
    $commercant = User::factory()->commercant()->create();

    $response = $this->actingAs($commercant)->post('/produits', []);

    $response->assertSessionHasErrors(['nom', 'prix', 'quantite']);
    $this->assertDatabaseCount('produits', 0);
});

test('un commerçant ne peut pas modifier le produit d’un autre commerçant', function () {
    $proprietaire = User::factory()->commercant()->create();
    $autre = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($proprietaire)->create();

    $response = $this->actingAs($autre)->put("/produits/{$produit->id}", [
        'nom' => 'Modifié',
        'prix' => 10,
        'quantite' => 5,
    ]);

    $response->assertForbidden();
    $this->assertDatabaseHas('produits', ['id' => $produit->id, 'nom' => $produit->nom]);
});

test('un commerçant ne peut pas supprimer le produit d’un autre commerçant', function () {
    $proprietaire = User::factory()->commercant()->create();
    $autre = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($proprietaire)->create();

    $response = $this->actingAs($autre)->delete("/produits/{$produit->id}");

    $response->assertForbidden();
    $this->assertDatabaseHas('produits', ['id' => $produit->id]);
});

test('un admin ne peut pas accéder aux routes commerçant', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/produits');

    $response->assertForbidden();
});
