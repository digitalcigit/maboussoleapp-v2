<?php

namespace App\Console\Commands;

use App\Services\DossierAutomationService;
use Illuminate\Console\Command;

class CheckDelayedDossiersCommand extends Command
{
    protected $signature = 'dossiers:check-delays';
    protected $description = 'Vérifie les dossiers en retard et envoie les notifications';

    public function handle(DossierAutomationService $service)
    {
        $this->info('Début de la vérification des dossiers en retard...');
        $service->checkDelayedDossiers();
        $this->info('Vérification terminée.');
    }
}
