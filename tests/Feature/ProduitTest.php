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

test('la recherche filtre les produits par nom', function () {
    $commercant = User::factory()->commercant()->create();
    Produit::factory()->for($commercant)->create(['nom' => 'Sac de riz']);
    Produit::factory()->for($commercant)->create(['nom' => 'Huile végétale']);

    $response = $this->actingAs($commercant)->get('/produits?search=riz');

    $response->assertSee('Sac de riz');
    $response->assertDontSee('Huile végétale');
});

test('le tri par prix fonctionne dans les deux sens', function () {
    $commercant = User::factory()->commercant()->create();
    Produit::factory()->for($commercant)->create(['nom' => 'Cher', 'prix' => 100]);
    Produit::factory()->for($commercant)->create(['nom' => 'Pas cher', 'prix' => 10]);

    $asc = $this->actingAs($commercant)->get('/produits?sort=prix&direction=asc');
    $asc->assertSeeInOrder(['Pas cher', 'Cher']);

    $desc = $this->actingAs($commercant)->get('/produits?sort=prix&direction=desc');
    $desc->assertSeeInOrder(['Cher', 'Pas cher']);
});

test('la recherche ignore la casse et les accents', function () {
    $commercant = User::factory()->commercant()->create();
    Produit::factory()->for($commercant)->create(['nom' => 'Café en grains']);

    $response = $this->actingAs($commercant)->get('/produits?search=CAFE');

    $response->assertSee('Café en grains');
});

test('les suggestions ignorent aussi la casse et les accents', function () {
    $commercant = User::factory()->commercant()->create();
    Produit::factory()->for($commercant)->create(['nom' => 'Café en grains']);

    $response = $this->actingAs($commercant)->getJson('/produits/suggestions?q=cafe');

    $response->assertOk();
    $response->assertJson(['Café en grains']);
});

test('les suggestions renvoient les noms de produits correspondants', function () {
    $commercant = User::factory()->commercant()->create();
    Produit::factory()->for($commercant)->create(['nom' => 'Sac de riz']);
    Produit::factory()->for($commercant)->create(['nom' => 'Huile végétale']);

    $response = $this->actingAs($commercant)->getJson('/produits/suggestions?q=riz');

    $response->assertOk();
    $response->assertJson(['Sac de riz']);
});

test('les suggestions ne remontent pas les produits d’un autre commerçant', function () {
    $commercant = User::factory()->commercant()->create();
    $autre = User::factory()->commercant()->create();
    Produit::factory()->for($autre)->create(['nom' => 'Sac de riz']);

    $response = $this->actingAs($commercant)->getJson('/produits/suggestions?q=riz');

    $response->assertOk();
    $response->assertJson([]);
});

test('une alerte s’affiche pour les produits en stock faible', function () {
    $commercant = User::factory()->commercant()->create();
    Produit::factory()->for($commercant)->create(['nom' => 'Riz presque épuisé', 'quantite' => 3]);
    Produit::factory()->for($commercant)->create(['nom' => 'Huile bien stockée', 'quantite' => 40]);

    $response = $this->actingAs($commercant)->get('/produits');

    $response->assertSee('Stock faible');
    $response->assertSee('Riz presque épuisé');
    $response->assertSee('⚠️ Faible');
});

test('aucune alerte de stock faible si tout va bien', function () {
    $commercant = User::factory()->commercant()->create();
    Produit::factory()->for($commercant)->create(['quantite' => 40]);

    $response = $this->actingAs($commercant)->get('/produits');

    $response->assertDontSee('Stock faible');
});

test('l’alerte de stock faible ne montre pas les produits d’un autre commerçant', function () {
    $commercant = User::factory()->commercant()->create();
    $autre = User::factory()->commercant()->create();
    Produit::factory()->for($autre)->create(['nom' => 'Produit d’un autre', 'quantite' => 1]);

    $response = $this->actingAs($commercant)->get('/produits');

    $response->assertDontSee('Stock faible');
});

test('un tri sur une colonne non autorisée est ignoré', function () {
    $commercant = User::factory()->commercant()->create();
    Produit::factory()->for($commercant)->create();

    $response = $this->actingAs($commercant)->get('/produits?sort=user_id&direction=asc');

    $response->assertOk();
});
