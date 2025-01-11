<?php

namespace App\Console\Commands;

use App\Models\Prospect;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateProspectDocuments extends Command
{
    protected $signature = 'app:migrate-prospect-documents';
    protected $description = 'Migre les documents des prospects vers le nouveau format structuré';

    public function handle()
    {
        $prospects = Prospect::whereNotNull('documents')->get();
        
        $this->info("Début de la migration des documents...");
        $bar = $this->output->createProgressBar(count($prospects));
        
        foreach ($prospects as $prospect) {
            // Sauvegarde des anciens documents
            $prospect->old_documents = $prospect->documents;
            
            if (!is_array($prospect->documents)) {
                continue;
            }

            $newDocuments = [];
            foreach ($prospect->documents as $path) {
                if (empty($path)) continue;
                
                // Nouveau chemin dans public/prospects/documents
                $newPath = 'prospects/documents/' . basename($path);
                
                // Si le fichier existe dans l'ancien emplacement
                if (Storage::exists($path)) {
                    // Copier vers le nouveau chemin si nécessaire
                    if (!Storage::exists('public/' . $newPath)) {
                        Storage::copy($path, 'public/' . $newPath);
                    }
                    
                    // Créer une nouvelle entrée structurée
                    $newDocuments[] = [
                        'type' => 'other', // Type par défaut
                        'file' => $newPath,
                        'description' => 'Document migré le ' . now()->format('d/m/Y')
                    ];
                    
                    $this->info("Document migré : {$path} -> {$newPath}");
                }
            }
            
            // Mettre à jour avec la nouvelle structure
            $prospect->documents = $newDocuments;
            $prospect->save();
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("Migration des documents terminée !");
    }
}
