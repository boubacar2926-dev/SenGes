<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SEED_ADMIN_EMAIL');
        $password = env('SEED_ADMIN_PASSWORD');

        if (! $email || ! $password) {
            $this->command?->warn('SEED_ADMIN_EMAIL / SEED_ADMIN_PASSWORD non définies : aucun compte admin créé.');

            return;
        }

        // TEMPORAIRE : updateOrCreate (au lieu de firstOrCreate) pour forcer
        // une rotation ponctuelle du mot de passe admin compromis, sans accès
        // shell. À repasser en firstOrCreate juste après, sinon chaque
        // redéploiement écrasera silencieusement le mot de passe.
        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin',
                'password' => Hash::make($password),
                'role' => 'admin',
            ]
        );
    }
}
