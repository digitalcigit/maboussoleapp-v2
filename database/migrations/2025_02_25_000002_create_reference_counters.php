<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Créer la table si elle n'existe pas
        if (!Schema::hasTable('reference_counters')) {
            Schema::create('reference_counters', function (Blueprint $table) {
                $table->id();
                $table->string('type')->unique();
                $table->integer('current_value')->default(0);
                $table->timestamps();
            });
        }

        // Ajouter le compteur pour les dossiers s'il n'existe pas
        if (!DB::table('reference_counters')->where('type', 'dossier')->exists()) {
            // Trouver la plus grande valeur actuelle des numéros de dossier
            $maxDossierNumber = DB::table('dossiers')
                ->where('reference_number', 'like', 'DOS-%')
                ->get()
                ->map(function ($dossier) {
                    preg_match('/DOS-(\d+)/', $dossier->reference_number, $matches);
                    return isset($matches[1]) ? (int)$matches[1] : 0;
                })
                ->max() ?? 0;

            // Insérer le compteur avec la valeur maximale trouvée
            DB::table('reference_counters')->insert([
                'type' => 'dossier',
                'current_value' => $maxDossierNumber,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Ne pas supprimer la table entière car elle pourrait contenir d'autres compteurs
        DB::table('reference_counters')->where('type', 'dossier')->delete();
    }
};
