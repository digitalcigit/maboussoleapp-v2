<?php

namespace App\Filament\Resources\DossierResource\Pages;

use App\Filament\Resources\DossierResource;
use App\Models\Dossier;
use App\Models\Prospect;
use App\Filament\Resources\DossierResource\Actions\RejectDossierAction;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditDossier extends EditRecord
{
    protected static string $resource = DossierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            RejectDossierAction::make()
                ->visible(fn (Dossier $record): bool => 
                    $record->current_status === Dossier::STATUS_SUBMITTED
                ),
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        // Mettre à jour la dernière action
        $this->record->update(['last_action_at' => now()]);

        // Récupérer les données du prospect
        $prospectData = $this->data['prospect_info'] ?? null;
        
        if ($prospectData && $this->record->prospect_id) {
            // Mettre à jour le prospect
            $prospect = Prospect::find($this->record->prospect_id);
            
            if ($prospect) {
                $prospect->update([
                    'first_name' => $prospectData['first_name'],
                    'last_name' => $prospectData['last_name'],
                    'email' => $prospectData['email'],
                    'phone' => $prospectData['phone'],
                    'birth_date' => $prospectData['birth_date'],
                    'profession' => $prospectData['profession'],
                    'education_level' => $prospectData['education_level'],
                    'desired_field' => $prospectData['desired_field'],
                    'desired_destination' => $prospectData['desired_destination'],
                    'emergency_contact' => $prospectData['emergency_contact'] ?? null,
                ]);

                // Notification de succès
                Notification::make()
                    ->title('Prospect mis à jour')
                    ->success()
                    ->send();
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
