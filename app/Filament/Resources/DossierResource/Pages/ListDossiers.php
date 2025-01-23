<?php

namespace App\Filament\Resources\DossierResource\Pages;

use App\Filament\Resources\DossierResource;
use App\Models\Dossier;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;

class ListDossiers extends ListRecords
{
    protected static string $resource = DossierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nouveau Dossier')
                ->icon('heroicon-o-plus'),
            ActionGroup::make([
                Action::make('export_selected')
                    ->label('Exporter la sélection')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (array $records): void {
                        // TODO: Implémenter l'export
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Exporter les dossiers sélectionnés')
                    ->modalDescription('Êtes-vous sûr de vouloir exporter les dossiers sélectionnés ?')
                    ->modalSubmitActionLabel('Oui, exporter')
                    ->modalCancelActionLabel('Annuler'),
                
                Action::make('archive_selected')
                    ->label('Archiver la sélection')
                    ->icon('heroicon-o-archive-box')
                    ->action(function (array $records): void {
                        // TODO: Implémenter l'archivage
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Archiver les dossiers sélectionnés')
                    ->modalDescription('Êtes-vous sûr de vouloir archiver les dossiers sélectionnés ?')
                    ->modalSubmitActionLabel('Oui, archiver')
                    ->modalCancelActionLabel('Annuler')
                    ->color('warning'),
            ])
                ->label('Plus d\'actions')
                ->icon('heroicon-m-chevron-down'),
        ];
    }
}
