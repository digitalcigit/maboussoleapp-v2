<?php

namespace App\Filament\PortailCandidat\Resources\DossierResource\Pages;

use App\Filament\PortailCandidat\Resources\DossierResource;
use App\Models\Dossier;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditDossier extends EditRecord
{
    protected static string $resource = DossierResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTitle(): string 
    {
        return "Mon Dossier";
    }

    public function getHeading(): string
    {
        return "Dossier N° {$this->record->reference_number}";
    }

    public function getSubheading(): string
    {
        return match ($this->record->current_step) {
            Dossier::STEP_ANALYSIS => 'Étape 1 : Analyse du dossier',
            Dossier::STEP_ADMISSION => 'Étape 2 : Admission',
            Dossier::STEP_PAYMENT => 'Étape 3 : Paiement',
            Dossier::STEP_VISA => 'Étape 4 : Visa',
            default => 'En cours de traitement',
        };
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Mettre à jour la dernière action
        $data['last_action_at'] = now();

        // Récupérer les données du prospect
        $prospectData = $data['prospect_info'] ?? null;
        
        if ($prospectData) {
            // Mettre à jour le prospect
            $this->record->prospect->update([
                'first_name' => $prospectData['first_name'] ?? null,
                'last_name' => $prospectData['last_name'] ?? null,
                'email' => $prospectData['email'] ?? null,
                'phone' => $prospectData['phone'] ?? null,
                'birth_date' => $prospectData['birth_date'] ?? null,
                'profession' => $prospectData['profession'] ?? null,
                'education_level' => $prospectData['education_level'] ?? null,
                'emergency_contact' => $prospectData['emergency_contact'] ?? null,
            ]);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        Notification::make()
            ->title('Dossier mis à jour')
            ->success()
            ->send();

        // Notifier le conseiller assigné
        if ($this->record->assignedTo) {
            Notification::make()
                ->title('Mise à jour dossier candidat')
                ->body("Le dossier {$this->record->reference_number} a été mis à jour par le candidat.")
                ->sendToDatabase($this->record->assignedTo);
        }
    }
}
