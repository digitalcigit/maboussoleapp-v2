<?php

use App\Models\Dossier;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mettre à jour les dossiers en phase d'admission qui sont en "Documents physiques reçus"
        // pour les passer en "En attente de documents physiques"
        $dossiers = Dossier::where('current_step', Dossier::STEP_ADMISSION)
            ->where('current_status', Dossier::STATUS_DOCS_RECEIVED)
            ->get();

        foreach ($dossiers as $dossier) {
            $dossier->update([
                'current_status' => Dossier::STATUS_WAITING_PHYSICAL_DOCS
            ]);
        }
    }

    public function down(): void
    {
        // Remettre les dossiers en "Documents physiques reçus"
        $dossiers = Dossier::where('current_step', Dossier::STEP_ADMISSION)
            ->where('current_status', Dossier::STATUS_WAITING_PHYSICAL_DOCS)
            ->get();

        foreach ($dossiers as $dossier) {
            $dossier->update([
                'current_status' => Dossier::STATUS_DOCS_RECEIVED
            ]);
        }
    }
};
