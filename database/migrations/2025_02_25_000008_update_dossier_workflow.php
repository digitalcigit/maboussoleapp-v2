<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            // Mettre à jour la valeur par défaut du statut
            $table->string('current_status', 50)->default('attente_documents')->change();
            
            // Ajouter les colonnes manquantes
            if (!Schema::hasColumn('dossiers', 'date_naissance')) {
                $table->date('date_naissance')->nullable();
            }
            if (!Schema::hasColumn('dossiers', 'lieu_naissance')) {
                $table->string('lieu_naissance')->nullable();
            }
            if (!Schema::hasColumn('dossiers', 'nationalite')) {
                $table->string('nationalite')->nullable();
            }
            if (!Schema::hasColumn('dossiers', 'pays_residence')) {
                $table->string('pays_residence')->nullable();
            }
            if (!Schema::hasColumn('dossiers', 'ville_residence')) {
                $table->string('ville_residence')->nullable();
            }
            if (!Schema::hasColumn('dossiers', 'adresse')) {
                $table->string('adresse')->nullable();
            }
            if (!Schema::hasColumn('dossiers', 'code_postal')) {
                $table->string('code_postal')->nullable();
            }
            if (!Schema::hasColumn('dossiers', 'experience_professionnelle')) {
                $table->text('experience_professionnelle')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->string('current_status', 50)->default('en_attente')->change();
            
            $table->dropColumn([
                'date_naissance',
                'lieu_naissance',
                'nationalite',
                'pays_residence',
                'ville_residence',
                'adresse',
                'code_postal',
                'experience_professionnelle'
            ]);
        });
    }
};
