<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Produit>
 */
class ProduitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nom' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'prix' => fake()->randomFloat(2, 1, 100),
            'quantite' => fake()->numberBetween(10, 50),
            'user_id' => User::factory(),
        ];
    }
}
