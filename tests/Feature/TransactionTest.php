<?php

use App\Models\Produit;
use App\Models\Transaction;
use App\Models\User;

test('créer une transaction décrémente le stock du produit', function () {
    $commercant = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($commercant)->create(['quantite' => 20, 'prix' => 100]);

    $response = $this->actingAs($commercant)->post('/transactions', [
        'items' => [['produit_id' => $produit->id, 'quantite' => 5]],
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

test('le formulaire de création affiche les produits disponibles', function () {
    $commercant = User::factory()->commercant()->create();
    Produit::factory()->for($commercant)->create(['nom' => 'Sac de riz']);

    $response = $this->actingAs($commercant)->get('/transactions/create');

    $response->assertOk();
    $response->assertSee('Sac de riz');
    $response->assertSee('Ajouter un produit');
});

test('on peut créer plusieurs transactions en une seule requête', function () {
    $commercant = User::factory()->commercant()->create();
    $riz = Produit::factory()->for($commercant)->create(['quantite' => 20, 'prix' => 100]);
    $huile = Produit::factory()->for($commercant)->create(['quantite' => 10, 'prix' => 50]);

    $response = $this->actingAs($commercant)->post('/transactions', [
        'items' => [
            ['produit_id' => $riz->id, 'quantite' => 5],
            ['produit_id' => $huile->id, 'quantite' => 3],
        ],
    ]);

    $response->assertRedirect('/transactions');
    expect($riz->fresh()->quantite)->toBe(15);
    expect($huile->fresh()->quantite)->toBe(7);
    $this->assertDatabaseCount('transactions', 2);
    $this->assertDatabaseHas('transactions', ['produit_id' => $riz->id, 'quantite' => 5, 'total' => 500]);
    $this->assertDatabaseHas('transactions', ['produit_id' => $huile->id, 'quantite' => 3, 'total' => 150]);
});

test('si une ligne échoue par manque de stock, aucune transaction du lot n’est créée', function () {
    $commercant = User::factory()->commercant()->create();
    $riz = Produit::factory()->for($commercant)->create(['quantite' => 20, 'prix' => 100]);
    $huile = Produit::factory()->for($commercant)->create(['quantite' => 2, 'prix' => 50]);

    $response = $this->actingAs($commercant)->post('/transactions', [
        'items' => [
            ['produit_id' => $riz->id, 'quantite' => 5],
            ['produit_id' => $huile->id, 'quantite' => 10],
        ],
    ]);

    $response->assertSessionHas('error');
    expect($riz->fresh()->quantite)->toBe(20);
    expect($huile->fresh()->quantite)->toBe(2);
    $this->assertDatabaseCount('transactions', 0);
});

test('créer une transaction échoue si le stock est insuffisant', function () {
    $commercant = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($commercant)->create(['quantite' => 3]);

    $response = $this->actingAs($commercant)->post('/transactions', [
        'items' => [['produit_id' => $produit->id, 'quantite' => 5]],
    ]);

    $response->assertSessionHas('error');
    expect($produit->fresh()->quantite)->toBe(3);
    $this->assertDatabaseCount('transactions', 0);
});

test('modifier la quantité d’une transaction effectuée ajuste le stock sans le décompter deux fois', function () {
    $commercant = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($commercant)->create(['quantite' => 20, 'prix' => 100]);

    $this->actingAs($commercant)->post('/transactions', [
        'items' => [['produit_id' => $produit->id, 'quantite' => 5]],
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
        'items' => [['produit_id' => $produit->id, 'quantite' => 5]],
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
        'items' => [['produit_id' => $produit->id, 'quantite' => 5]],
    ]);
    $transaction = Transaction::first();
    expect($produit->fresh()->quantite)->toBe(15);

    $response = $this->actingAs($commercant)->delete("/transactions/{$transaction->id}");

    $response->assertRedirect('/transactions');
    expect($produit->fresh()->quantite)->toBe(20);
    expect($transaction->fresh()->statut)->toBe('annulée');
});

test('la recherche de transactions ignore la casse et les accents', function () {
    $commercant = User::factory()->commercant()->create();
    $cafe = Produit::factory()->for($commercant)->create(['nom' => 'Café en grains', 'quantite' => 10]);
    $this->actingAs($commercant)->post('/transactions', ['items' => [['produit_id' => $cafe->id, 'quantite' => 1]]]);

    $response = $this->actingAs($commercant)->get('/transactions?search=CAFE');

    $response->assertSee('Café en grains');
});

test('la recherche filtre les transactions par nom de produit', function () {
    $commercant = User::factory()->commercant()->create();
    $riz = Produit::factory()->for($commercant)->create(['nom' => 'Sac de riz', 'quantite' => 50]);
    $huile = Produit::factory()->for($commercant)->create(['nom' => 'Huile végétale', 'quantite' => 50]);

    $this->actingAs($commercant)->post('/transactions', ['items' => [['produit_id' => $riz->id, 'quantite' => 1]]]);
    $this->actingAs($commercant)->post('/transactions', ['items' => [['produit_id' => $huile->id, 'quantite' => 1]]]);

    $response = $this->actingAs($commercant)->get('/transactions?search=riz');

    $response->assertSee('Sac de riz');
    $response->assertDontSee('Huile végétale');
});

test('les transactions créées ensemble apparaissent dans un seul bloc avec une seule facture', function () {
    $commercant = User::factory()->commercant()->create();
    $riz = Produit::factory()->for($commercant)->create(['nom' => 'Sac de riz', 'quantite' => 50]);
    $huile = Produit::factory()->for($commercant)->create(['nom' => 'Huile végétale', 'quantite' => 50]);

    $this->actingAs($commercant)->post('/transactions', [
        'items' => [
            ['produit_id' => $riz->id, 'quantite' => 1],
            ['produit_id' => $huile->id, 'quantite' => 1],
        ],
    ]);

    $response = $this->actingAs($commercant)->get('/transactions');

    $response->assertOk();
    $groupeId = Transaction::first()->groupe_id;
    $lienFacture = route('transactions.facture', $groupeId);
    expect(substr_count($response->getContent(), $lienFacture))->toBe(1);
});

test('deux ventes distinctes apparaissent dans des blocs séparés', function () {
    $commercant = User::factory()->commercant()->create();
    $riz = Produit::factory()->for($commercant)->create(['nom' => 'Sac de riz', 'quantite' => 50]);
    $huile = Produit::factory()->for($commercant)->create(['nom' => 'Huile végétale', 'quantite' => 50]);

    $this->actingAs($commercant)->post('/transactions', ['items' => [['produit_id' => $riz->id, 'quantite' => 1]]]);
    $this->actingAs($commercant)->post('/transactions', ['items' => [['produit_id' => $huile->id, 'quantite' => 1]]]);

    $response = $this->actingAs($commercant)->get('/transactions');

    $response->assertOk();
    $groupeIds = Transaction::pluck('groupe_id')->unique();
    expect($groupeIds)->toHaveCount(2);

    foreach ($groupeIds as $groupeId) {
        expect(substr_count($response->getContent(), route('transactions.facture', $groupeId)))->toBe(1);
    }
});

test('un commerçant peut consulter la facture de sa transaction', function () {
    $commercant = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($commercant)->create(['nom' => 'Sac de riz', 'prix' => 100, 'quantite' => 50]);
    $this->actingAs($commercant)->post('/transactions', ['items' => [['produit_id' => $produit->id, 'quantite' => 2]]]);
    $transaction = Transaction::first();

    $response = $this->actingAs($commercant)->get('/transactions/facture/'.$transaction->groupe_id);

    $response->assertOk();
    $response->assertSee('FACTURE');
    $response->assertSee('Sac de riz');
    $response->assertSee('200'); // total
});

test('une facture regroupe toutes les transactions créées dans la même soumission', function () {
    $commercant = User::factory()->commercant()->create();
    $riz = Produit::factory()->for($commercant)->create(['nom' => 'Sac de riz', 'prix' => 100, 'quantite' => 50]);
    $huile = Produit::factory()->for($commercant)->create(['nom' => 'Huile végétale', 'prix' => 50, 'quantite' => 50]);

    $this->actingAs($commercant)->post('/transactions', [
        'items' => [
            ['produit_id' => $riz->id, 'quantite' => 2],
            ['produit_id' => $huile->id, 'quantite' => 3],
        ],
    ]);

    $transactions = Transaction::all();
    expect($transactions)->toHaveCount(2);
    expect($transactions->pluck('groupe_id')->unique())->toHaveCount(1);

    $response = $this->actingAs($commercant)->get('/transactions/facture/'.$transactions->first()->groupe_id);

    $response->assertOk();
    $response->assertSee('Sac de riz');
    $response->assertSee('Huile végétale');
    $response->assertSee('350'); // 200 + 150 : total du lot
});

test('un commerçant ne peut pas consulter la facture de la transaction d’un autre', function () {
    $proprietaire = User::factory()->commercant()->create();
    $autre = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($proprietaire)->create(['quantite' => 50]);
    $this->actingAs($proprietaire)->post('/transactions', ['items' => [['produit_id' => $produit->id, 'quantite' => 1]]]);
    $transaction = Transaction::first();

    $response = $this->actingAs($autre)->get('/transactions/facture/'.$transaction->groupe_id);

    $response->assertForbidden();
});

test('une facture pour un groupe inexistant renvoie une 404', function () {
    $commercant = User::factory()->commercant()->create();

    $response = $this->actingAs($commercant)->get('/transactions/facture/00000000-0000-0000-0000-000000000000');

    $response->assertNotFound();
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
