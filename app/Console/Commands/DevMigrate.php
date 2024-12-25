<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class DevMigrate extends Command
{
    protected $signature = 'dev:migrate {--fresh : Recréer la base de données} {--seed : Exécuter les seeders}';
    protected $description = 'Exécute les tests de base de données puis applique les migrations si les tests passent';

    public function handle()
    {
        $this->info('🚀 Démarrage du processus de migration...');

        // 1. Sauvegarder l'environnement actuel
        $this->info('📝 Sauvegarde de l\'environnement...');
        $currentEnv = env('APP_ENV');
        putenv('APP_ENV=testing');

        // 2. Exécuter les tests
        $this->info('🧪 Exécution des tests...');
        $testProcess = new Process(['php', 'artisan', 'test', '--filter=Database']);
        $testProcess->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        // Restaurer l'environnement
        putenv("APP_ENV={$currentEnv}");

        // Si les tests échouent
        if (! $testProcess->isSuccessful()) {
            $this->error('❌ Les tests ont échoué. Les migrations ne seront pas appliquées.');

            return 1;
        }

        $this->info('✅ Les tests sont passés !');

        // 3. Demander confirmation pour la migration
        if (! $this->confirm('Voulez-vous appliquer les migrations sur la base de développement ?', true)) {
            $this->info('🛑 Migration annulée.');

            return 0;
        }

        // 4. Appliquer les migrations
        $this->info('🔄 Application des migrations...');
        
        $command = ['php', 'artisan', 'migrate'];
        if ($this->option('fresh')) {
            $command[] = '--fresh';
        }
        
        $migrateProcess = new Process($command);
        $migrateProcess->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        if (! $migrateProcess->isSuccessful()) {
            $this->error('❌ Erreur lors de l\'application des migrations.');

            return 1;
        }

        // 5. Exécuter les seeders si demandé
        if ($this->option('seed')) {
            $this->info('🌱 Exécution des seeders...');
            $seedProcess = new Process(['php', 'artisan', 'db:seed']);
            $seedProcess->run(function ($type, $buffer) {
                $this->output->write($buffer);
            });

            if (! $seedProcess->isSuccessful()) {
                $this->error('❌ Erreur lors de l\'exécution des seeders.');

                return 1;
            }
        }

        $this->info('✨ Processus de migration terminé avec succès !');

        return 0;
    }
}
