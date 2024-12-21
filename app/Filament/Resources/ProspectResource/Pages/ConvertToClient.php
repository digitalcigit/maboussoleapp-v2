<?php

namespace App\Filament\Resources\ProspectResource\Pages;

use App\Filament\Resources\ProspectResource;
use App\Models\Client;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Str;

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
                    
                    // Créer un nouveau client
                    $client = Client::create([
                        'client_number' => 'CLI-' . Str::random(5),
                        'first_name' => $prospect->first_name,
                        'last_name' => $prospect->last_name,
                        'email' => $prospect->email,
                        'phone' => $prospect->phone,
                        'birth_date' => $prospect->birth_date,
                        'profession' => $prospect->profession,
                        'education_level' => $prospect->education_level,
                        'current_location' => $prospect->current_location,
                        'current_field' => $prospect->current_field,
                        'desired_field' => $prospect->desired_field,
                        'desired_destination' => $prospect->desired_destination,
                        'emergency_contact' => $prospect->emergency_contact,
                        'status' => 'active',
                        'assigned_to' => $prospect->assigned_to,
                        'commercial_code' => $prospect->commercial_code,
                        'partner_id' => $prospect->partner_id,
                    ]);

                    // Mettre à jour le statut du prospect
                    $prospect->update(['status' => 'converti']);

                    // Rediriger vers la page du nouveau client
                    return redirect()->to(ClientResource::getUrl('edit', ['record' => $client]));
                }),
        ];
    }
}
