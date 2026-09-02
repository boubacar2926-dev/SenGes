<?php

namespace Database\Seeders;

use App\Models\Produit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAdmin();
        $this->seedDemoCommercant();
    }

    private function seedAdmin(): void
    {
        $email = env('SEED_ADMIN_EMAIL');
        $password = env('SEED_ADMIN_PASSWORD');

        if (! $email || ! $password) {
            $this->command?->warn('SEED_ADMIN_EMAIL / SEED_ADMIN_PASSWORD non définies : aucun compte admin créé.');

            return;
        }

        User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin',
                'password' => Hash::make($password),
                'role' => 'admin',
            ]
        );
    }

    private function seedDemoCommercant(): void
    {
        $email = env('SEED_DEMO_COMMERCANT_EMAIL');
        $password = env('SEED_DEMO_COMMERCANT_PASSWORD');

        if (! $email || ! $password) {
            return;
        }

        $commercant = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Commerçant Démo',
                'password' => Hash::make($password),
                'role' => 'commercant',
            ]
        );

        $produitsCount = (int) env('SEED_DEMO_PRODUCTS_COUNT', 20);

        if ($produitsCount > 0 && Produit::where('user_id', $commercant->id)->count() === 0) {
            Produit::factory()->for($commercant)->count($produitsCount)->create();
        }
    }
}
