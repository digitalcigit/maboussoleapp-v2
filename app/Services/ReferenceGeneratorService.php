<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReferenceGeneratorService
{
    /**
     * Génère une nouvelle référence séquentielle pour un type donné
     *
     * @param string $type Le type de référence (ex: 'dossier')
     * @param string $prefix Le préfixe de la référence (ex: 'DOS')
     * @param int $padding Le nombre de chiffres pour le padding (ex: 3 pour 001)
     * @return string La référence générée (ex: DOS-001)
     */
    public function generateReference(string $type, string $prefix = 'DOS', int $padding = 3): string
    {
        try {
            $result = DB::transaction(function () use ($type) {
                $counter = DB::table('reference_counters')
                    ->where('type', $type)
                    ->lockForUpdate()
                    ->first();

                if (!$counter) {
                    throw new RuntimeException("Compteur non trouvé pour le type: {$type}");
                }

                $newValue = $counter->current_value + 1;

                DB::table('reference_counters')
                    ->where('type', $type)
                    ->update([
                        'current_value' => $newValue,
                        'updated_at' => now(),
                    ]);

                return $newValue;
            });

            return sprintf("%s-%s", $prefix, str_pad($result, $padding, '0', STR_PAD_LEFT));
        } catch (\Exception $e) {
            \Log::error("Erreur lors de la génération de référence", [
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
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
