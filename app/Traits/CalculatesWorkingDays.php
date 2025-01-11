<?php

namespace App\Traits;

use Carbon\Carbon;

trait CalculatesWorkingDays
{
    /**
     * Calcule une date dans le futur en ajoutant un nombre spécifique de jours ouvrés
     *
     * @param Carbon $startDate Date de début
     * @param int $workingDays Nombre de jours ouvrés à ajouter
     * @return Carbon
     */
    public function addWorkingDays(Carbon $startDate, int $workingDays): Carbon
    {
        $endDate = $startDate->copy();
        $addedDays = 0;

        while ($addedDays < $workingDays) {
            $endDate->addDay();
            
            // Si ce n'est pas un weekend, on compte le jour
            if (!$endDate->isWeekend()) {
                $addedDays++;
            }
        }

        return $endDate;
    }
}
