<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Dossier;

return new class extends Migration
{
    public function up(): void
    {
        // Mettre à jour les statuts des dossiers existants
        DB::table('dossiers')->orderBy('id')->chunk(100, function ($dossiers) {
            foreach ($dossiers as $dossier) {
                $newStatus = match ($dossier->current_status) {
                    'attente_documents', 'attente_documents_physiques' => 'attente_documents',
                    'analyse_en_cours', 'preparation_en_cours', 'soumission_en_cours' => 'en_cours',
                    'analyse_terminee', 'preparation_terminee', 'soumission_terminee' => 'termine',
                    'bloque', 'abandonne' => 'bloque',
                    default => 'en_cours',
                };

                DB::table('dossiers')
                    ->where('id', $dossier->id)
                    ->update([
                        'current_status' => $newStatus,
                        'updated_at' => now(),
                    ]);
            }
        });
    }

    public function down(): void
    {
        // Cette migration ne peut pas être annulée de manière sûre
    }
};
