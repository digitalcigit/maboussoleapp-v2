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
        // 1. Ajouter la nouvelle colonne
        Schema::table('dossiers', function (Blueprint $table) {
            $table->decimal('down_payment_amount', 15, 2)->nullable()->after('tuition_total_amount');
        });

        // 2. Copier les données
        DB::statement('UPDATE dossiers SET down_payment_amount = montant_accompte');

        // 3. Supprimer l'ancienne colonne
        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropColumn('montant_accompte');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Recréer l'ancienne colonne
        Schema::table('dossiers', function (Blueprint $table) {
            $table->decimal('montant_accompte', 15, 2)->nullable()->after('tuition_total_amount');
        });

        // 2. Restaurer les données
        DB::statement('UPDATE dossiers SET montant_accompte = down_payment_amount');

        // 3. Supprimer la nouvelle colonne
        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropColumn('down_payment_amount');
        });
    }
};
