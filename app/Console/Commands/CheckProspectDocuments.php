<?php

namespace App\Console\Commands;

use App\Models\Prospect;
use Illuminate\Console\Command;

class CheckProspectDocuments extends Command
{
    protected $signature = 'app:check-prospect-documents';
    protected $description = 'Vérifie les chemins des documents des prospects';

    public function handle()
    {
        $prospects = Prospect::whereNotNull('documents')->get();
        
        foreach ($prospects as $prospect) {
            $this->info("Prospect ID: " . $prospect->id);
            $this->info("Documents: " . print_r($prospect->documents, true));
        }
    }
}
