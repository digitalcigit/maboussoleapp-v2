<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Dossier;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->decimal('tuition_total_amount', 10, 2)->nullable();
            $table->decimal('tuition_paid_amount', 10, 2)->nullable()->default(0);
        });

        // Migration des données existantes
        $dossiers = Dossier::whereIn('current_status', [
            Dossier::STATUS_PARTIAL_TUITION,
            Dossier::STATUS_FULL_TUITION
        ])->get();

        foreach ($dossiers as $dossier) {
            if ($dossier->current_status === Dossier::STATUS_FULL_TUITION) {
                // Si le paiement était total, on considère que c'est 100% du montant
                $dossier->tuition_total_amount = $dossier->admission_fees ?? 0;
                $dossier->tuition_paid_amount = $dossier->admission_fees ?? 0;
            }
            $dossier->save();
        }
    }

    public function down(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropColumn('tuition_total_amount');
            $table->dropColumn('tuition_paid_amount');
        });
    }
};
