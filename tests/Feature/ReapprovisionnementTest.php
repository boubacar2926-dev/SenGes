<?php

use App\Models\Produit;
use App\Models\Reapprovisionnement;
use App\Models\User;

test('un commerçant peut réapprovisionner son produit', function () {
    $commercant = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($commercant)->create(['quantite' => 5]);

    $response = $this->actingAs($commercant)->post("/produits/{$produit->id}/reapprovisionnements", [
        'quantite' => 20,
    ]);

    $response->assertRedirect(route('produits.reapprovisionnements.index', $produit));
    expect($produit->fresh()->quantite)->toBe(25);
    $this->assertDatabaseHas('reapprovisionnements', [
        'produit_id' => $produit->id,
        'user_id' => $commercant->id,
        'quantite' => 20,
    ]);
});

test('la page d’historique affiche les réapprovisionnements précédents', function () {
    $commercant = User::factory()->commercant()->create(['name' => 'Fatou']);
    $produit = Produit::factory()->for($commercant)->create(['quantite' => 5]);
    Reapprovisionnement::create(['produit_id' => $produit->id, 'user_id' => $commercant->id, 'quantite' => 15]);

    $response = $this->actingAs($commercant)->get("/produits/{$produit->id}/reapprovisionnements");

    $response->assertOk();
    $response->assertSee('Fatou');
    $response->assertSee('+15');
});

test('une quantité négative ou nulle est rejetée', function () {
    $commercant = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($commercant)->create(['quantite' => 5]);

    $response = $this->actingAs($commercant)->post("/produits/{$produit->id}/reapprovisionnements", [
        'quantite' => 0,
    ]);

    $response->assertSessionHasErrors(['quantite']);
    expect($produit->fresh()->quantite)->toBe(5);
});

test('un commerçant ne peut pas réapprovisionner le produit d’un autre', function () {
    $proprietaire = User::factory()->commercant()->create();
    $autre = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($proprietaire)->create(['quantite' => 5]);

    $response = $this->actingAs($autre)->post("/produits/{$produit->id}/reapprovisionnements", [
        'quantite' => 10,
    ]);

    $response->assertForbidden();
    expect($produit->fresh()->quantite)->toBe(5);
});

test('un commerçant ne peut pas consulter l’historique du produit d’un autre', function () {
    $proprietaire = User::factory()->commercant()->create();
    $autre = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($proprietaire)->create();

    $response = $this->actingAs($autre)->get("/produits/{$produit->id}/reapprovisionnements");

    $response->assertForbidden();
});
