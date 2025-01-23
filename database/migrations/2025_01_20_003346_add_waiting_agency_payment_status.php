<?php

use App\Models\Dossier;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mettre à jour les dossiers en phase de paiement qui sont en "Frais d'agence payés"
        // pour les passer en "En attente de paiement des frais d'agence"
        $dossiers = Dossier::where('current_step', Dossier::STEP_PAYMENT)
            ->where('current_status', Dossier::STATUS_AGENCY_PAID)
            ->get();

        foreach ($dossiers as $dossier) {
            $dossier->update([
                'current_status' => Dossier::STATUS_WAITING_AGENCY_PAYMENT
            ]);
        }
    }

    public function down(): void
    {
        // Remettre les dossiers en "Frais d'agence payés"
        $dossiers = Dossier::where('current_step', Dossier::STEP_PAYMENT)
            ->where('current_status', Dossier::STATUS_WAITING_AGENCY_PAYMENT)
            ->get();

        foreach ($dossiers as $dossier) {
            $dossier->update([
                'current_status' => Dossier::STATUS_AGENCY_PAID
            ]);
        }
    }
};
