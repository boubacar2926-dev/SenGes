<?php

use App\Models\Produit;
use App\Models\Transaction;
use App\Models\User;

test('les ventes d’un commerçant sont numérotées séquentiellement à partir de 1', function () {
    $commercant = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($commercant)->create(['quantite' => 100]);

    $this->actingAs($commercant)->post('/transactions', ['items' => [['produit_id' => $produit->id, 'quantite' => 1]]]);
    $this->actingAs($commercant)->post('/transactions', ['items' => [['produit_id' => $produit->id, 'quantite' => 1]]]);
    $this->actingAs($commercant)->post('/transactions', ['items' => [['produit_id' => $produit->id, 'quantite' => 1]]]);

    $numeros = Transaction::where('user_id', $commercant->id)->pluck('groupe_id', 'numero_facture');
    expect($numeros->keys()->sort()->values()->all())->toBe([1, 2, 3]);
});

test('toutes les lignes d’une même vente partagent le même numéro de facture', function () {
    $commercant = User::factory()->commercant()->create();
    $p1 = Produit::factory()->for($commercant)->create(['quantite' => 100]);
    $p2 = Produit::factory()->for($commercant)->create(['quantite' => 100]);

    $this->actingAs($commercant)->post('/transactions', [
        'items' => [
            ['produit_id' => $p1->id, 'quantite' => 1],
            ['produit_id' => $p2->id, 'quantite' => 1],
        ],
    ]);

    $numeros = Transaction::where('user_id', $commercant->id)->pluck('numero_facture')->unique();
    expect($numeros)->toHaveCount(1);
});

test('deux commerçants différents ont chacun leur propre numérotation à partir de 1', function () {
    $a = User::factory()->commercant()->create();
    $b = User::factory()->commercant()->create();
    $produitA = Produit::factory()->for($a)->create(['quantite' => 100]);
    $produitB = Produit::factory()->for($b)->create(['quantite' => 100]);

    $this->actingAs($a)->post('/transactions', ['items' => [['produit_id' => $produitA->id, 'quantite' => 1]]]);
    $this->actingAs($b)->post('/transactions', ['items' => [['produit_id' => $produitB->id, 'quantite' => 1]]]);

    expect(Transaction::where('user_id', $a->id)->first()->numero_facture)->toBe(1);
    expect(Transaction::where('user_id', $b->id)->first()->numero_facture)->toBe(1);
});

test('la facture affiche le numéro séquentiel du commerçant', function () {
    $commercant = User::factory()->commercant()->create();
    $produit = Produit::factory()->for($commercant)->create(['quantite' => 100]);

    $this->actingAs($commercant)->post('/transactions', ['items' => [['produit_id' => $produit->id, 'quantite' => 1]]]);
    $groupeId = Transaction::first()->groupe_id;

    $response = $this->actingAs($commercant)->get("/transactions/facture/{$groupeId}");

    $response->assertOk();
    $response->assertSee('N° 000001');
});
