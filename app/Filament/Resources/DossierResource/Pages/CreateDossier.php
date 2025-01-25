<?php

namespace App\Filament\Resources\DossierResource\Pages;

use App\Filament\Resources\DossierResource;
use App\Models\Dossier; 
use App\Models\Prospect;
use App\Services\ReferenceGeneratorService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class CreateDossier extends CreateRecord
{
    protected static string $resource = DossierResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Assignation automatique si non spécifié
        if (!isset($data['assigned_to'])) {
            $data['assigned_to'] = auth()->id();
        }

        // Si un prospect est sélectionné, on utilise ses informations
        if (!empty($data['prospect_id'])) {
            $referenceGenerator = app(ReferenceGeneratorService::class);
            $data['reference_number'] = $referenceGenerator->generateReference('dossier');
            $data['last_action_at'] = now();
            $data['current_step'] = Dossier::STEP_ANALYSIS;
            $data['current_status'] = Dossier::STATUS_WAITING_DOCS;
            return $data;
        }

        // Sinon, on crée un nouveau prospect avec les informations fournies
        $referenceGenerator = app(ReferenceGeneratorService::class);
        $prospect = Prospect::create([
            'reference_number' => 'PROS-' . $referenceGenerator->generateReference('prospect'),
            'first_name' => $data['prospect_info']['first_name'],
            'last_name' => $data['prospect_info']['last_name'],
            'email' => $data['prospect_info']['email'],
            'phone' => $data['prospect_info']['phone'],
            'birth_date' => $data['prospect_info']['birth_date'],
            'profession' => $data['prospect_info']['profession'],
            'education_level' => $data['prospect_info']['education_level'],
            'desired_field' => $data['prospect_info']['desired_field'],
            'desired_destination' => $data['prospect_info']['desired_destination'],
            'emergency_contact' => $data['prospect_info']['emergency_contact'] ?? null,
            'current_status' => 'nouveau',
            'assigned_to' => $data['assigned_to'] ?? auth()->id(),
        ]);

        // On lie le nouveau prospect au dossier
        $data['prospect_id'] = $prospect->id;

        $data['reference_number'] = $referenceGenerator->generateReference('dossier');
        $data['last_action_at'] = now();
        $data['current_step'] = Dossier::STEP_ANALYSIS;
        $data['current_status'] = Dossier::STATUS_WAITING_DOCS;

        Notification::make('prospect-created')
            ->title('Prospect créé')
            ->success()
            ->body('Un nouveau prospect a été créé et lié au dossier.')
            ->send();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return DossierResource::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $dossier = $this->record;
        $prospect = $dossier->prospect;
        
        if ($prospect) {
            // Copier les données du prospect vers le dossier
            $dossier->copyFromProspect($prospect);
            
            // Marquer le prospect comme converti
            $prospect->markAsConverted($dossier->reference_number);
        }
    }
}
