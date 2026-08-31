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
