<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            // Convertir d'abord les valeurs existantes en entiers
            DB::statement('UPDATE dossiers SET agency_payment_amount = ROUND(agency_payment_amount) WHERE agency_payment_amount IS NOT NULL');
            
            // Modifier le type de colonne
            $table->integer('agency_payment_amount')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->decimal('agency_payment_amount', 10, 2)->nullable()->change();
        });
    }
};
