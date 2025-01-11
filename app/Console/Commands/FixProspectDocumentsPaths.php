<?php

namespace App\Console\Commands;

use App\Models\Prospect;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FixProspectDocumentsPaths extends Command
{
    protected $signature = 'app:fix-prospect-documents';
    protected $description = 'Corrige les chemins des documents des prospects';

    public function handle()
    {
        $this->info("Début de la correction des chemins...");
        
        // Vérifier si le dossier existe
        $directory = 'prospects/documents';
        if (!Storage::disk('public')->exists($directory)) {
            $this->error("Le répertoire {$directory} n'existe pas!");
            return;
        }
        
        $this->info("Recherche des prospects avec des documents...");
        
        // Récupérer tous les prospects
        $prospects = Prospect::all();
        $this->info("Nombre total de prospects trouvés : " . $prospects->count());
        
        foreach ($prospects as $prospect) {
            $this->info("\nTraitement du prospect ID: " . $prospect->id);
            $this->info("Documents actuels : " . json_encode($prospect->documents));
            
            // Lister les fichiers dans le répertoire
            $files = Storage::disk('public')->files($directory);
            if (!empty($files)) {
                $this->info("Fichiers trouvés dans le répertoire : " . implode(', ', $files));
                
                // Mettre à jour les documents du prospect
                $documents = array_map(function($file) {
                    return basename($file);
                }, $files);
                
                $prospect->documents = $documents;
                $prospect->save();
                
                $this->info("Documents mis à jour pour le prospect : " . json_encode($prospect->documents));
            } else {
                $this->warn("Aucun fichier trouvé dans le répertoire pour ce prospect");
            }
        }
        
        $this->info("\nMise à jour terminée!");
    }
}
