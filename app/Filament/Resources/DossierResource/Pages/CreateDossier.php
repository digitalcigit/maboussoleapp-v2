<?php

namespace App\Filament\Resources\DossierResource\Pages;

use App\Filament\Resources\DossierResource;
use App\Models\Dossier; // Add this line
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateDossier extends CreateRecord
{
    protected static string $resource = DossierResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['reference_number'] = 'DOS-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $data['last_action_at'] = now();
        $data['current_step'] = Dossier::STEP_ANALYSIS;
        $data['current_status'] = Dossier::STATUS_WAITING_DOCS;
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
