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
        // Mettre à jour les enregistrements existants
        DB::table('dossiers')
            ->whereNull('current_status')
            ->update(['current_status' => 'en_attente']);

        // Modifier la colonne pour ajouter la valeur par défaut
        Schema::table('dossiers', function (Blueprint $table) {
            $table->string('current_status', 50)->default('en_attente')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->string('current_status', 50)->change();
        });
    }
};
