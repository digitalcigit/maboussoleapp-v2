<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Dossier;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Mettre à jour les dossiers existants pour s'assurer que les champs optionnels
        // sont définis comme null s'ils sont vides
        Dossier::whereNotNull('prospect_info')->chunk(100, function ($dossiers) {
            foreach ($dossiers as $dossier) {
                $prospect_info = $dossier->prospect_info;
                
                // Définir explicitement les champs optionnels comme null s'ils sont vides
                $prospect_info['phone'] = $prospect_info['phone'] ?? null;
                $prospect_info['birth_date'] = $prospect_info['birth_date'] ?? null;
                $prospect_info['profession'] = $prospect_info['profession'] ?? null;
                $prospect_info['education_level'] = $prospect_info['education_level'] ?? null;
                $prospect_info['desired_field'] = $prospect_info['desired_field'] ?? null;
                $prospect_info['desired_destination'] = $prospect_info['desired_destination'] ?? null;
                $prospect_info['emergency_contact'] = $prospect_info['emergency_contact'] ?? null;
                
                $dossier->prospect_info = $prospect_info;
                $dossier->save();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cette migration est non destructive, donc pas besoin de rollback
    }
};
