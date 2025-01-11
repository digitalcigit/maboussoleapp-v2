<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Création du compte conseiller
        $conseiller = User::create([
            'name' => 'Beda Conseiller',
            'email' => 'beda@maboussole-crm.com',
            'password' => Hash::make('password'),
        ]);
        $conseiller->assignRole('conseiller');

        // Création du compte manager
        $manager = User::create([
            'name' => 'Manager Test',
            'email' => 'manager@maboussole-crm.com',
            'password' => Hash::make('password'),
        ]);
        $manager->assignRole('manager');

        // Création du compte commercial
        $commercial = User::create([
            'name' => 'Commercial Test',
            'email' => 'commercial@maboussole-crm.com',
            'password' => Hash::make('password'),
        ]);
        $commercial->assignRole('commercial');

        // Création du compte partenaire
        $partenaire = User::create([
            'name' => 'Partenaire Test',
            'email' => 'partenaire@maboussole-crm.com',
            'password' => Hash::make('password'),
        ]);
        $partenaire->assignRole('partenaire');
    }
}
