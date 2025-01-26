<?php

namespace App\Filament\PortailCandidat\Resources\DossierResource\Pages;

use App\Filament\PortailCandidat\Resources\DossierResource;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class ListDossiers extends Page
{
    protected static string $resource = DossierResource::class;

    protected static string $view = 'filament.pages.redirect';

    public function mount(): void
    {
        $user = auth()->user();
        
        if ($user && $user->prospect && $user->prospect->dossier) {
            $this->redirect(DossierResource::getUrl('edit', [
                'record' => $user->prospect->dossier->id
            ]));
        }
    }

    public function getTitle(): string|Htmlable
    {
        return 'Mon Dossier';
    }
}
