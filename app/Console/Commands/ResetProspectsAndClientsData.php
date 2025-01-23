<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetProspectsAndClientsData extends Command
{
    protected $signature = 'app:reset-prospects-clients';
    protected $description = 'Réinitialise les données des tables prospects et clients';

    public function handle()
    {
        if ($this->confirm('Êtes-vous sûr de vouloir supprimer toutes les données des prospects et clients ?')) {
            // Désactiver temporairement les contraintes de clés étrangères
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Vider les tables
            DB::table('prospects')->delete();
            DB::table('clients')->delete();

            // Réactiver les contraintes
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            $this->info('Les données des prospects et clients ont été réinitialisées avec succès !');
        }
    }
}
