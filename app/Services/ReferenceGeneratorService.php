<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReferenceGeneratorService
{
    /**
     * Génère une nouvelle référence séquentielle unique pour un dossier
     * Cette référence sera utilisée pour le dossier, le prospect et le client
     *
     * @return string La référence générée (ex: MB-001)
     */
    public function generateUnifiedReference(): string
    {
        try {
            $result = DB::transaction(function () {
                $counter = DB::table('reference_counters')
                    ->where('type', 'dossier')
                    ->lockForUpdate()
                    ->first();

                if (!$counter) {
                    throw new RuntimeException("Compteur non trouvé pour le type: dossier");
                }

                $newValue = $counter->current_value + 1;

                DB::table('reference_counters')
                    ->where('type', 'dossier')
                    ->update([
                        'current_value' => $newValue,
                        'updated_at' => now(),
                    ]);

                return $newValue;
            });

            // MB pour MaBoussole
            return sprintf("MB-DOS-%s", str_pad($result, 6, '0', STR_PAD_LEFT));
        } catch (\Exception $e) {
            \Log::error("Erreur lors de la génération de référence unifiée", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * @deprecated Utiliser generateUnifiedReference() à la place
     */
    public function generateReference(string $type, string $prefix = 'DOS', int $padding = 3): string
    {
        return $this->generateUnifiedReference();
    }

    /**
     * Réinitialise un compteur à une valeur donnée
     *
     * @param string $type Le type de compteur
     * @param int $value La nouvelle valeur
     * @return bool
     */
    public function resetCounter(string $type, int $value = 0): bool
    {
        return DB::table('reference_counters')
            ->where('type', $type)
            ->update([
                'current_value' => $value,
                'updated_at' => now(),
            ]) > 0;
    }
}
