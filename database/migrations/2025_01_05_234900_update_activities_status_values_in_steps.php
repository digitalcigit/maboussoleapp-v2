<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Étape 1 : Supprimer la contrainte enum
        DB::statement('ALTER TABLE activities MODIFY status VARCHAR(255)');

        // Étape 2 : Mettre à jour les valeurs
        DB::table('activities')->where('status', 'en_attente')->update(['status' => 'pending']);
        DB::table('activities')->where('status', 'en_cours')->update(['status' => 'in_progress']);
        DB::table('activities')->where('status', 'termine')->update(['status' => 'completed']);
        DB::table('activities')->where('status', 'annule')->update(['status' => 'cancelled']);

        // Étape 3 : Remettre la contrainte enum avec les nouvelles valeurs
        DB::statement("ALTER TABLE activities MODIFY status ENUM('pending', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Étape 1 : Supprimer la contrainte enum
        DB::statement('ALTER TABLE activities MODIFY status VARCHAR(255)');

        // Étape 2 : Restaurer les anciennes valeurs
        DB::table('activities')->where('status', 'pending')->update(['status' => 'en_attente']);
        DB::table('activities')->where('status', 'in_progress')->update(['status' => 'en_cours']);
        DB::table('activities')->where('status', 'completed')->update(['status' => 'termine']);
        DB::table('activities')->where('status', 'cancelled')->update(['status' => 'annule']);

        // Étape 3 : Remettre la contrainte enum avec les anciennes valeurs
        DB::statement("ALTER TABLE activities MODIFY status ENUM('en_attente', 'en_cours', 'termine', 'annule') NOT NULL DEFAULT 'en_attente'");
    }
};
