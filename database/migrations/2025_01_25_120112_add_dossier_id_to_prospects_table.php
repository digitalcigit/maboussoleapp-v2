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
        Schema::table('prospects', function (Blueprint $table) {
            $table->foreignId('dossier_id')->nullable()->after('id')->constrained('dossiers')->nullOnDelete();
        });

        // Mise à jour des relations existantes
        DB::statement('
            UPDATE prospects p
            INNER JOIN dossiers d ON d.prospect_id = p.id
            SET p.dossier_id = d.id
            WHERE p.converted_to_dossier = 1
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->dropForeign(['dossier_id']);
            $table->dropColumn('dossier_id');
        });
    }
};
