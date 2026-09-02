<?php

use App\Models\Produit;
use App\Models\Transaction;
use App\Models\User;

test('créer une transaction décrémente le stock du produit', function () {
    $commercant = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($commercant)->create(['quantite' => 20, 'prix' => 100]);

    $response = $this->actingAs($commercant)->post('/transactions', [
        'produit_id' => $produit->id,
        'quantite' => 5,
    ]);

    $response->assertRedirect('/transactions');
    expect($produit->fresh()->quantite)->toBe(15);
    $this->assertDatabaseHas('transactions', [
        'produit_id' => $produit->id,
        'quantite' => 5,
        'total' => 500,
        'statut' => 'effectuée',
    ]);
});

test('créer une transaction échoue si le stock est insuffisant', function () {
    $commercant = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($commercant)->create(['quantite' => 3]);

    $response = $this->actingAs($commercant)->post('/transactions', [
        'produit_id' => $produit->id,
        'quantite' => 5,
    ]);

    $response->assertSessionHas('error');
    expect($produit->fresh()->quantite)->toBe(3);
    $this->assertDatabaseCount('transactions', 0);
});

test('modifier la quantité d’une transaction effectuée ajuste le stock sans le décompter deux fois', function () {
    $commercant = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($commercant)->create(['quantite' => 20, 'prix' => 100]);

    $this->actingAs($commercant)->post('/transactions', [
        'produit_id' => $produit->id,
        'quantite' => 5,
    ]);
    $transaction = Transaction::first();
    expect($produit->fresh()->quantite)->toBe(15);

    $response = $this->actingAs($commercant)->put("/transactions/{$transaction->id}", [
        'produit_id' => $produit->id,
        'quantite' => 8,
        'statut' => 'effectuée',
    ]);

    $response->assertRedirect('/transactions');
    // 15 + 5 (restitution de l'ancienne quantité) - 8 (nouvelle quantité) = 12
    expect($produit->fresh()->quantite)->toBe(12);
    expect($transaction->fresh()->quantite)->toBe(8);
    expect((float) $transaction->fresh()->total)->toBe(800.0);
});

test('annuler une transaction via update restitue le stock', function () {
    $commercant = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($commercant)->create(['quantite' => 20]);

    $this->actingAs($commercant)->post('/transactions', [
        'produit_id' => $produit->id,
        'quantite' => 5,
    ]);
    $transaction = Transaction::first();
    expect($produit->fresh()->quantite)->toBe(15);

    $this->actingAs($commercant)->put("/transactions/{$transaction->id}", [
        'produit_id' => $produit->id,
        'quantite' => 5,
        'statut' => 'annulée',
    ]);

    expect($produit->fresh()->quantite)->toBe(20);
    expect($transaction->fresh()->statut)->toBe('annulée');
});

test('supprimer (annuler) une transaction effectuée restitue le stock', function () {
    $commercant = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($commercant)->create(['quantite' => 20]);

    $this->actingAs($commercant)->post('/transactions', [
        'produit_id' => $produit->id,
        'quantite' => 5,
    ]);
    $transaction = Transaction::first();
    expect($produit->fresh()->quantite)->toBe(15);

    $response = $this->actingAs($commercant)->delete("/transactions/{$transaction->id}");

    $response->assertRedirect('/transactions');
    expect($produit->fresh()->quantite)->toBe(20);
    expect($transaction->fresh()->statut)->toBe('annulée');
});

test('la recherche filtre les transactions par nom de produit', function () {
    $commercant = User::factory()->commercant()->create();
    $riz = Produit::factory()->for($commercant)->create(['nom' => 'Sac de riz', 'quantite' => 50]);
    $huile = Produit::factory()->for($commercant)->create(['nom' => 'Huile végétale', 'quantite' => 50]);

    $this->actingAs($commercant)->post('/transactions', ['produit_id' => $riz->id, 'quantite' => 1]);
    $this->actingAs($commercant)->post('/transactions', ['produit_id' => $huile->id, 'quantite' => 1]);

    $response = $this->actingAs($commercant)->get('/transactions?search=riz');

    $response->assertSee('Sac de riz');
    $response->assertDontSee('Huile végétale');
});

test('un commerçant peut consulter la facture de sa transaction', function () {
    $commercant = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($commercant)->create(['nom' => 'Sac de riz', 'prix' => 100, 'quantite' => 50]);
    $this->actingAs($commercant)->post('/transactions', ['produit_id' => $produit->id, 'quantite' => 2]);
    $transaction = Transaction::first();

    $response = $this->actingAs($commercant)->get("/transactions/{$transaction->id}/facture");

    $response->assertOk();
    $response->assertSee('FACTURE');
    $response->assertSee('Sac de riz');
    $response->assertSee('200'); // total
});

test('un commerçant ne peut pas consulter la facture de la transaction d’un autre', function () {
    $proprietaire = User::factory()->commercant()->create();
    $autre = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($proprietaire)->create(['quantite' => 50]);
    $this->actingAs($proprietaire)->post('/transactions', ['produit_id' => $produit->id, 'quantite' => 1]);
    $transaction = Transaction::first();

    $response = $this->actingAs($autre)->get("/transactions/{$transaction->id}/facture");

    $response->assertForbidden();
});

test('un commerçant ne peut pas modifier la transaction d’un autre', function () {
    $proprietaire = User::factory()->commercant()->create();
    $autre = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($proprietaire)->create();
    $transaction = Transaction::factory()->create([
        'produit_id' => $produit->id,
        'user_id' => $proprietaire->id,
    ]);

    $response = $this->actingAs($autre)->put("/transactions/{$transaction->id}", [
        'produit_id' => $produit->id,
        'quantite' => 1,
        'statut' => 'effectuée',
    ]);

    $response->assertForbidden();
});
