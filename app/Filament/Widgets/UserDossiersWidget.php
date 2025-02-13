<?php

namespace App\Filament\Widgets;

use App\Models\Dossier;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class UserDossiersWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        
        // Nombre total de dossiers créés par l'utilisateur
        $totalDossiers = Dossier::where('created_by', $user->id)->count();
        
        // Dossiers créés ce mois-ci
        $dossiersThisMonth = Dossier::where('created_by', $user->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        // Dossiers créés cette semaine
        $dossiersThisWeek = Dossier::where('created_by', $user->id)
            ->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])
            ->count();

        return [
            Stat::make('Total des dossiers', $totalDossiers)
                ->description('Nombre total de dossiers créés')
                ->descriptionIcon('heroicon-m-folder')
                ->color('success'),

            Stat::make('Dossiers ce mois', $dossiersThisMonth)
                ->description('Créés en ' . now()->format('F Y'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),

            Stat::make('Dossiers cette semaine', $dossiersThisWeek)
                ->description('Du ' . now()->startOfWeek()->format('d/m') . ' au ' . now()->endOfWeek()->format('d/m'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),
        ];
    }
}
