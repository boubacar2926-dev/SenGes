<?php

namespace Database\Factories;

use App\Models\Produit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    public function definition(): array
    {
        $produit = Produit::factory()->create();

        return [
            'produit_id' => $produit->id,
            'user_id' => $produit->user_id,
            'quantite' => 1,
            'total' => $produit->prix,
            'statut' => 'effectuée',
        ];
    }
}
