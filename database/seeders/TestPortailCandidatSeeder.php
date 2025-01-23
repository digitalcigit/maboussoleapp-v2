<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestPortailCandidatSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'name' => 'Test Candidat',
            'email' => 'candidat@test.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $user->assignRole('portail_candidat');
    }
}
