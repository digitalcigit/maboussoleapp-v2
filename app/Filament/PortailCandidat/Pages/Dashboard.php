<?php

namespace App\Filament\PortailCandidat\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\PortailCandidat\Widgets\DossierProgressWidget;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    
    protected static string $view = 'filament.portail-candidat.pages.dashboard';
    
    protected static ?string $title = 'Tableau de bord';
    
    protected static ?int $navigationSort = -2;
    
    public function getHeading(): string
    {
        return 'Bienvenue ' . auth()->user()->name;
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            DossierProgressWidget::class,
        ];
    }
}
