<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\Prospect;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ConseillerStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $user = auth()->user();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Total des dossiers
        $totalDossiers = Prospect::where('assigned_to', $user->id)->count();

        // Nombre de prospects actifs (non convertis)
        $activeProspects = Prospect::where('assigned_to', $user->id)
            ->where('current_status', '!=', Prospect::STATUS_CONVERTI)
            ->count();

        // Nombre de clients (prospects convertis)
        $totalClients = Client::whereHas('prospect', function ($query) use ($user) {
            $query->where('assigned_to', $user->id);
        })->count();

        // Conversions du mois
        $monthlyConversions = Client::whereHas('prospect', function ($query) use ($user) {
            $query->where('assigned_to', $user->id);
        })
        ->whereYear('created_at', $currentYear)
        ->whereMonth('created_at', $currentMonth)
        ->count();

        // Taux de conversion
        $conversionRate = $totalDossiers > 0 
            ? round(($totalClients / $totalDossiers) * 100, 1) 
            : 0;

        return [
            Stat::make('Total Dossiers', $totalDossiers)
                ->description('Nombre total de dossiers gérés')
                ->descriptionIcon('heroicon-m-folder')
                ->color('gray'),

            Stat::make('Prospects Actifs', $activeProspects)
                ->description('Prospects en cours de traitement')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning'),

            Stat::make('Clients', $totalClients)
                ->description('Prospects convertis en clients')
                ->descriptionIcon('heroicon-m-user-circle')
                ->color('success'),

            Stat::make('Conversions du Mois', $monthlyConversions)
                ->description('Conversions pour ' . Carbon::now()->format('F Y'))
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('primary'),

            Stat::make('Taux de Conversion', $conversionRate . '%')
                ->description('Taux de conversion global')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($conversionRate >= 50 ? 'success' : ($conversionRate >= 30 ? 'warning' : 'danger')),
        ];
    }
}
