<?php

use App\Models\Produit;
use App\Models\Transaction;
use App\Models\User;

// --- IDOR : produit_id d'un autre commerçant dans une transaction ---

test('un commerçant ne peut pas créer une transaction sur le produit d’un autre commerçant', function () {
    $attaquant = User::factory()->commercant()->create();
    $victime = User::factory()->commercant()->create();
    $produitVictime = Produit::factory()->for($victime)->create(['quantite' => 20, 'prix' => 1000]);

    $response = $this->actingAs($attaquant)->post('/transactions', [
        'items' => [['produit_id' => $produitVictime->id, 'quantite' => 5]],
    ]);

    $response->assertSessionHasErrors(['items.0.produit_id']);
    $this->assertDatabaseCount('transactions', 0);
    expect($produitVictime->fresh()->quantite)->toBe(20);
});

test('dans un lot, une seule ligne visant le produit d’un autre commerçant fait échouer toute la validation', function () {
    $attaquant = User::factory()->commercant()->create();
    $victime = User::factory()->commercant()->create();
    $produitAttaquant = Produit::factory()->for($attaquant)->create(['quantite' => 20]);
    $produitVictime = Produit::factory()->for($victime)->create(['quantite' => 20]);

    $response = $this->actingAs($attaquant)->post('/transactions', [
        'items' => [
            ['produit_id' => $produitAttaquant->id, 'quantite' => 1],
            ['produit_id' => $produitVictime->id, 'quantite' => 1],
        ],
    ]);

    $response->assertSessionHasErrors(['items.1.produit_id']);
    $this->assertDatabaseCount('transactions', 0);
    expect($produitAttaquant->fresh()->quantite)->toBe(20);
    expect($produitVictime->fresh()->quantite)->toBe(20);
});

test('un commerçant ne peut pas réaffecter sa transaction au produit d’un autre commerçant', function () {
    $attaquant = User::factory()->commercant()->create();
    $victime = User::factory()->commercant()->create();
    $produitAttaquant = Produit::factory()->for($attaquant)->create(['quantite' => 20, 'prix' => 100]);
    $produitVictime = Produit::factory()->for($victime)->create(['quantite' => 20, 'prix' => 5000]);

    $this->actingAs($attaquant)->post('/transactions', [
        'items' => [['produit_id' => $produitAttaquant->id, 'quantite' => 5]],
    ]);
    $transaction = Transaction::first();
    expect($produitAttaquant->fresh()->quantite)->toBe(15);

    $response = $this->actingAs($attaquant)->put("/transactions/{$transaction->id}", [
        'produit_id' => $produitVictime->id,
        'quantite' => 5,
        'statut' => 'effectuée',
    ]);

    $response->assertSessionHasErrors(['produit_id']);
    // Rien n'a changé : ni la transaction, ni le stock des deux produits.
    expect($transaction->fresh()->produit_id)->toBe($produitAttaquant->id);
    expect($produitAttaquant->fresh()->quantite)->toBe(15);
    expect($produitVictime->fresh()->quantite)->toBe(20);
});

test('la validation d’exists sur produit_id ne peut pas être contournée en manipulant directement la requête', function () {
    // Même si la règle de validation venait à être court-circuitée (ex: régression),
    // le contrôleur doit lui-même re-scoper par utilisateur (défense en profondeur).
    // On simule cela en vérifiant qu'aucune transaction n'est jamais créée pour un
    // produit n'appartenant pas à l'utilisateur connecté, quel que soit le chemin.
    $attaquant = User::factory()->commercant()->create();
    $victime = User::factory()->commercant()->create();
    $produitVictime = Produit::factory()->for($victime)->create(['quantite' => 20]);

    $this->actingAs($attaquant)->post('/transactions', [
        'items' => [['produit_id' => $produitVictime->id, 'quantite' => 1]],
    ]);

    $this->assertDatabaseMissing('transactions', ['produit_id' => $produitVictime->id]);
});

// --- Mass assignment ---

test('on ne peut pas s’attribuer un produit à un autre utilisateur via mass assignment', function () {
    $commercant = User::factory()->commercant()->create();
    $autre = User::factory()->commercant()->create();

    $this->actingAs($commercant)->post('/produits', [
        'nom' => 'Produit test',
        'prix' => 100,
        'quantite' => 10,
        'user_id' => $autre->id,
    ]);

    $this->assertDatabaseHas('produits', [
        'nom' => 'Produit test',
        'user_id' => $commercant->id,
    ]);
    $this->assertDatabaseMissing('produits', [
        'nom' => 'Produit test',
        'user_id' => $autre->id,
    ]);
});

test('la mise à jour d’un produit ne permet pas de changer son propriétaire', function () {
    $commercant = User::factory()->commercant()->create();
    $autre = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($commercant)->create();

    $this->actingAs($commercant)->put("/produits/{$produit->id}", [
        'nom' => 'Nouveau nom',
        'prix' => 100,
        'quantite' => 10,
        'user_id' => $autre->id,
    ]);

    expect($produit->fresh()->user_id)->toBe($commercant->id);
});

// --- Validation des quantités ---

test('une quantité de transaction négative ou nulle est rejetée', function () {
    $commercant = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($commercant)->create(['quantite' => 20]);

    $response = $this->actingAs($commercant)->post('/transactions', [
        'items' => [['produit_id' => $produit->id, 'quantite' => -5]],
    ]);

    $response->assertSessionHasErrors(['items.0.quantite']);
    $this->assertDatabaseCount('transactions', 0);
    expect($produit->fresh()->quantite)->toBe(20);
});

test('une quantité de produit négative est rejetée à la création', function () {
    $commercant = User::factory()->commercant()->create();

    $response = $this->actingAs($commercant)->post('/produits', [
        'nom' => 'Produit test',
        'prix' => 100,
        'quantite' => -1,
    ]);

    $response->assertSessionHasErrors(['quantite']);
    $this->assertDatabaseCount('produits', 0);
});

// --- XSS ---

test('le nom d’un produit contenant du HTML est échappé dans la liste des produits', function () {
    $commercant = User::factory()->commercant()->create();
    Produit::factory()->for($commercant)->create(['nom' => '<script>alert(1)</script>']);

    $response = $this->actingAs($commercant)->get('/produits');

    $response->assertDontSee('<script>alert(1)</script>', false);
    $response->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
});

test('le nom d’un produit contenant du HTML est échappé dans la liste des transactions', function () {
    $commercant = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($commercant)->create(['nom' => '<img src=x onerror=alert(1)>', 'quantite' => 10]);
    $this->actingAs($commercant)->post('/transactions', ['items' => [['produit_id' => $produit->id, 'quantite' => 1]]]);

    $response = $this->actingAs($commercant)->get('/transactions');

    $response->assertDontSee('<img src=x onerror=alert(1)>', false);
});

// --- Isolation multi-tenant sur l’index des transactions ---

test('un commerçant ne voit pas les transactions d’un autre dans sa liste', function () {
    $commercant = User::factory()->commercant()->create();
    $autre = User::factory()->commercant()->create();
    $produitAutre = Produit::factory()->for($autre)->create(['nom' => 'Produit secret', 'quantite' => 10]);
    $this->actingAs($autre)->post('/transactions', ['items' => [['produit_id' => $produitAutre->id, 'quantite' => 1]]]);

    $response = $this->actingAs($commercant)->get('/transactions');

    $response->assertOk();
    $response->assertDontSee('Produit secret');
});
