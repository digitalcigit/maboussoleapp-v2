<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Création du super admin
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@maboussole-crm.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('super-admin');

        // Création d'un manager de test
        $manager = User::create([
            'name' => 'Manager Test',
            'email' => 'manager@maboussole-crm.com',
            'password' => Hash::make('password'),
        ]);
        $manager->assignRole('manager');

        // Création d'un conseiller de test
        $conseiller = User::create([
            'name' => 'Conseiller Test',
            'email' => 'conseiller@maboussole-crm.com',
            'password' => Hash::make('password'),
        ]);
        $conseiller->assignRole('conseiller');
    }
}
