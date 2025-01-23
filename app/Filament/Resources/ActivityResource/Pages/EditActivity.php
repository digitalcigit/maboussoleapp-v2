<?php

namespace App\Filament\Resources\ActivityResource\Pages;

use App\Filament\Resources\ActivityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditActivity extends EditRecord
{
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->after(function () {
                    Notification::make()
                        ->title('Activité supprimée')
                        ->body('L\'activité a été supprimée avec succès.')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function afterSave(): void
    {
        Notification::make()
            ->title('Activité modifiée')
            ->body('L\'activité a été modifiée avec succès.')
            ->success()
            ->send();
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Assurez-vous que subject_type est défini pour le formulaire
        $data['subject_type'] = $this->record->subject_type;
        return $data;
    }
}
