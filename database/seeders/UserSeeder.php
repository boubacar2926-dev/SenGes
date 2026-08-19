<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Création de l'Administrateur
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Création d'un Commerçant
        User::create([
            'name' => 'Commerçant',
            'email' => 'commercant@example.com',
            'password' => Hash::make('password'),
            'role' => 'commercant',
        ]);
    }
}
