<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\Prospect;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        // Utilisateurs actifs
        $activeUsers = User::where('last_login_at', '>=', now()->subDay())->count();
        $yesterdayActiveUsers = User::where('last_login_at', '>=', now()->subDays(2))
            ->where('last_login_at', '<', now()->subDay())
            ->count();
        $usersTrend = $activeUsers - $yesterdayActiveUsers;

        // Total des prospects
        $totalProspects = Prospect::count();
        $lastMonthProspects = Prospect::where('created_at', '<', now()->startOfMonth())->count();
        $prospectsTrend = $totalProspects - $lastMonthProspects;

        // Chiffre d'affaires
        $query = Client::query();
        
        // Si conseiller, filtrer par ses clients
        if (auth()->user()->hasRole('conseiller')) {
            $query->where('assigned_to', auth()->id());
        }
        
        // Calculer le total des paiements
        $totalPayments = $query->sum('paid_amount');
        
        // Calculer le CA du mois
        $monthlyRevenue = Client::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('paid_amount');
        
        // Calculer le CA du mois précédent pour la tendance
        $lastMonthRevenue = Client::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('paid_amount');
        
        $revenueTrend = $monthlyRevenue - $lastMonthRevenue;

        return [
            Stat::make('Utilisateurs Actifs', $activeUsers)
                ->description($usersTrend >= 0 ? "+$usersTrend depuis hier" : "$usersTrend depuis hier")
                ->descriptionIcon($usersTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($usersTrend >= 0 ? 'success' : 'danger')
                ->chart([7, 3, 4, 5, 6, 3, $activeUsers])
                ->icon('heroicon-o-users')
                ->extraAttributes([
                    'data-testid' => 'kpi-active-users',
                    'data-trend' => $usersTrend,
                    'dusk' => 'active-users-kpi',
                ]),

            Stat::make('Total Prospects', $totalProspects)
                ->description($prospectsTrend >= 0 ? "+$prospectsTrend ce mois" : "$prospectsTrend ce mois")
                ->descriptionIcon($prospectsTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($prospectsTrend >= 0 ? 'success' : 'danger')
                ->chart([7, 3, 4, 5, 6, 3, $totalProspects])
                ->icon('heroicon-o-user-group')
                ->extraAttributes([
                    'data-testid' => 'kpi-prospects',
                    'data-trend' => $prospectsTrend,
                    'dusk' => 'total-prospects-kpi',
                ]),

            Stat::make(
                auth()->user()->hasRole('conseiller') ? 'Mes Paiements Totaux' : 'Chiffre d\'Affaires Total', 
                number_format($totalPayments, 0, ',', ' ') . ' FCFA'
            )
                ->description($revenueTrend >= 0 
                    ? '+' . number_format($revenueTrend, 0, ',', ' ') . ' FCFA ce mois' 
                    : number_format($revenueTrend, 0, ',', ' ') . ' FCFA ce mois'
                )
                ->descriptionIcon($revenueTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueTrend >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-banknotes')
                ->extraAttributes([
                    'data-testid' => 'kpi-revenue',
                    'data-trend' => $revenueTrend,
                    'dusk' => 'total-revenue-kpi',
                ]),
        ];
    }
}
