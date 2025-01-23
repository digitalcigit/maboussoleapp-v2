<?php

namespace App\Filament\Resources\ProspectResource\Pages;

use App\Filament\Resources\ProspectResource;
use App\Models\Prospect;
use Filament\Resources\Pages\CreateRecord;

class CreateProspect extends CreateRecord
{
    protected static string $resource = ProspectResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // On force le statut à "En attente de documents" lors de la création
        $data['status'] = Prospect::STATUS_WAITING_DOCS;
        
        // On calcule la date limite d'analyse (5 jours ouvrés)
        $data['analysis_deadline'] = now()->addWeekdays(Prospect::ANALYSIS_WORKING_DAYS);
        
        return $data;
    }
}
