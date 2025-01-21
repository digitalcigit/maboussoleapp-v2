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
        // Récupérer tous les dossiers triés par date de création
        $dossiers = DB::table('dossiers')
            ->orderBy('created_at')
            ->get();

        $generator = app(ReferenceGeneratorService::class);

        // Mettre à jour chaque dossier avec une nouvelle référence séquentielle
        foreach ($dossiers as $dossier) {
            $newReference = $generator->generateReference('dossier');
            
            DB::table('dossiers')
                ->where('id', $dossier->id)
                ->update([
                    'reference_number' => $newReference,
                    'updated_at' => now(),
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
