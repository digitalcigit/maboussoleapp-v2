<?php

namespace App\Filament\Resources\ProspectResource\Pages;

use App\Filament\Resources\ProspectResource;
use App\Filament\Resources\ClientResource;
use App\Models\Client;
use App\Models\Prospect;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;

class ConvertToClient extends Page
{
    protected static string $resource = ProspectResource::class;

    protected static string $view = 'filament.resources.prospect-resource.pages.convert-to-client';

    public function mount(): void
    {
        $this->authorize('create', Client::class);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('convert')
                ->label('Convertir en client')
                ->action(function () {
                    $prospect = $this->getRecord();

                    // Vérifier si l'analyse est terminée
                    if ($prospect->status !== Prospect::STATUS_ANALYZED) {
                        Notification::make()
                            ->warning()
                            ->title('L\'analyse du prospect doit être terminée avant la conversion')
                            ->send();
                        return;
                    }

                    try {
                        // Créer un nouveau client
                        $client = new Client();
                        $client->first_name = $prospect->first_name;
                        $client->last_name = $prospect->last_name;
                        $client->email = $prospect->email;
                        $client->phone = $prospect->phone;
                        $client->birth_date = $prospect->birth_date;
                        $client->education_level = $prospect->education_level;
                        $client->assigned_to = $prospect->assigned_to;
                        $client->client_number = 'CLI-' . Str::random(5);
                        $client->save();

                        // Mettre à jour le statut du prospect
                        $prospect->status = Prospect::STATUS_CONVERTED;
                        $prospect->save();

                        // Notification de succès
                        Notification::make()
                            ->success()
                            ->title('Prospect converti en client avec succès')
                            ->send();

                        // Rediriger vers la page d'édition du client
                        return redirect()->to(ClientResource::getUrl('edit', ['record' => $client->id]));
                    } catch (\Exception $e) {
                        // En cas d'erreur, afficher une notification
                        Notification::make()
                            ->danger()
                            ->title('Erreur lors de la conversion')
                            ->body($e->getMessage())
                            ->send();
                        
                        return null;
                    }
                })
                ->requiresConfirmation()
                ->color('success')
                ->icon('heroicon-o-user-plus')
        ];
    }
}
