<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Services\ReferenceGeneratorService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Réinitialiser le compteur à 0
        DB::table('reference_counters')
            ->where('type', 'dossier')
            ->update(['current_value' => 0]);

        // 2. Récupérer tous les dossiers triés par date de création
        $dossiers = DB::table('dossiers')
            ->orderBy('created_at')
            ->get();

        $generator = app(ReferenceGeneratorService::class);

        // 3. Mettre à jour chaque dossier avec une nouvelle référence séquentielle
        foreach ($dossiers as $dossier) {
            $newReference = $generator->generateReference('dossier');
            
            DB::table('dossiers')
                ->where('id', $dossier->id)
                ->update([
                    'reference_number' => $newReference,
                    'updated_at' => now(),
                ]);

            // Log pour traçabilité
            \Log::info("Référence dossier mise à jour", [
                'dossier_id' => $dossier->id,
                'old_reference' => $dossier->reference_number,
                'new_reference' => $newReference
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // La réversion n'est pas possible car nous ne gardons pas trace des anciennes références
    }
};
