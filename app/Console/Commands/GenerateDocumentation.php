<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Scripts\DocumentationGenerator;

class GenerateDocumentation extends Command
{
    protected $signature = 'docs:generate {--type=all : Type de documentation à générer (all, models, controllers)}';
    protected $description = 'Génère automatiquement la documentation du projet';

    public function handle()
    {
        $this->info('Génération de la documentation...');

        $generator = new DocumentationGenerator();
        $type = $this->option('type');

        switch ($type) {
            case 'models':
                $generator->generateModelsDocs();
                $this->info('Documentation des modèles générée.');

                break;
            case 'controllers':
                $generator->generateControllersDocs();
                $this->info('Documentation des contrôleurs générée.');

                break;
            case 'all':
                $generator->generateModelsDocs();
                $generator->generateControllersDocs();
                $generator->generateCoverageReport();
                $this->info('Documentation complète générée.');

                break;
        }

        $this->info('Terminé !');
    }
}
