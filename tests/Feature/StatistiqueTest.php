<?php

use App\Models\Produit;
use App\Models\Transaction;
use App\Models\User;

test('annuler une transaction retire son montant des statistiques', function () {
    $commercant = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($commercant)->create(['prix' => 1000, 'quantite' => 50]);

    $this->actingAs($commercant)->post('/transactions', [
        'items' => [['produit_id' => $produit->id, 'quantite' => 3]],
    ]);
    $transaction = Transaction::first();

    $avant = $this->actingAs($commercant)->get('/statistiques');
    $avant->assertSee('3 000 FCFA');

    $this->actingAs($commercant)->delete("/transactions/{$transaction->id}");

    $apres = $this->actingAs($commercant)->get('/statistiques');
    $apres->assertDontSee('3 000 FCFA');
    $apres->assertSee('0 FCFA');
});

test('le nombre de transactions sur les statistiques exclut les annulées', function () {
    $commercant = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($commercant)->create(['quantite' => 50]);

    $this->actingAs($commercant)->post('/transactions', ['items' => [['produit_id' => $produit->id, 'quantite' => 1]]]);
    $transaction = Transaction::first();

    $this->actingAs($commercant)->get('/statistiques')->assertSee('>1<', false);

    $this->actingAs($commercant)->delete("/transactions/{$transaction->id}");

    $this->actingAs($commercant)->get('/statistiques')->assertSee('>0<', false);
});

test('le dashboard affiche le revenu du jour', function () {
    $commercant = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($commercant)->create(['prix' => 1500, 'quantite' => 50]);

    $this->actingAs($commercant)->post('/transactions', ['items' => [['produit_id' => $produit->id, 'quantite' => 2]]]);

    $response = $this->actingAs($commercant)->get('/dashboard');

    $response->assertOk();
    $response->assertSee('Revenu du jour');
    $response->assertSee('3 000 FCFA');
});

test('le revenu du jour du dashboard exclut une transaction annulée le même jour', function () {
    $commercant = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($commercant)->create(['prix' => 1500, 'quantite' => 50]);

    $this->actingAs($commercant)->post('/transactions', ['items' => [['produit_id' => $produit->id, 'quantite' => 2]]]);
    $transaction = Transaction::first();
    $this->actingAs($commercant)->delete("/transactions/{$transaction->id}");

    $response = $this->actingAs($commercant)->get('/dashboard');

    $response->assertOk();
    $response->assertSee('Revenu du jour');
    $response->assertDontSee('3 000 FCFA');
});

test('le revenu du dashboard exclut les transactions annulées', function () {
    $commercant = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($commercant)->create(['prix' => 500, 'quantite' => 50]);

    $this->actingAs($commercant)->post('/transactions', ['items' => [['produit_id' => $produit->id, 'quantite' => 2]]]);
    $transaction = Transaction::first();

    $this->actingAs($commercant)->delete("/transactions/{$transaction->id}");

    $response = $this->actingAs($commercant)->get('/dashboard');

    $response->assertOk();
    $response->assertDontSee('1000');
});

test('le revenu total du dashboard admin exclut les transactions annulées', function () {
    $admin = User::factory()->admin()->create();
    $commercant = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($commercant)->create(['prix' => 2000, 'quantite' => 50]);

    $this->actingAs($commercant)->post('/transactions', ['items' => [['produit_id' => $produit->id, 'quantite' => 1]]]);
    $transaction = Transaction::first();
    $this->actingAs($commercant)->delete("/transactions/{$transaction->id}");

    $response = $this->actingAs($admin)->get('/admin/dashboard');

    $response->assertOk();
    $response->assertDontSee('2,000.00');
    $response->assertSee('0.00');
});

test('le statut "en attente" n’est plus accepté lors de la modification (n’existe pas en base)', function () {
    $commercant = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($commercant)->create(['quantite' => 50]);

    $this->actingAs($commercant)->post('/transactions', ['items' => [['produit_id' => $produit->id, 'quantite' => 1]]]);
    $transaction = Transaction::first();

    $response = $this->actingAs($commercant)->put("/transactions/{$transaction->id}", [
        'produit_id' => $produit->id,
        'quantite' => 1,
        'statut' => 'en attente',
    ]);

    $response->assertSessionHasErrors('statut');
});
