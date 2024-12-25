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

        // Taux de conversion global
        $totalConversions = Client::whereMonth('created_at', now()->month)->count();
        $totalProspectsThisMonth = Prospect::whereMonth('created_at', now()->month)->count();
        $conversionRate = $totalProspectsThisMonth > 0
            ? round(($totalConversions / $totalProspectsThisMonth) * 100, 2)
            : 0;

        // Chiffre d'affaires du mois
        $currentMonthRevenue = Client::whereMonth('created_at', now()->month)
            ->sum('contract_value');
        $lastMonthRevenue = Client::whereMonth('created_at', now()->subMonth()->month)
            ->sum('contract_value');
        $revenueTrend = $currentMonthRevenue - $lastMonthRevenue;

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
                ->chart([2, 4, 6, 8, 7, 6, $totalProspects])
                ->icon('heroicon-o-user-group')
                ->extraAttributes([
                    'data-testid' => 'kpi-prospects',
                    'data-trend' => $prospectsTrend,
                    'dusk' => 'total-prospects-kpi',
                ]),

            Stat::make('Taux Conversion', $conversionRate.'%')
                ->description('Ce mois-ci')
                ->chart([4, 5, 3, 6, 3, 4, $conversionRate])
                ->color($conversionRate >= 20 ? 'success' : 'warning')
                ->icon('heroicon-o-arrow-path')
                ->extraAttributes([
                    'data-testid' => 'kpi-conversion',
                    'data-value' => $conversionRate,
                    'dusk' => 'conversion-rate-kpi',
                ]),

            Stat::make('CA du Mois', number_format($currentMonthRevenue, 0, ',', ' ').' €')
                ->description($revenueTrend >= 0 ? "+$revenueTrend € vs mois dernier" : "$revenueTrend € vs mois dernier")
                ->descriptionIcon($revenueTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueTrend >= 0 ? 'success' : 'danger')
                ->chart([8, 9, 7, 8, 6, 9, $currentMonthRevenue / 1000])
                ->icon('heroicon-o-currency-euro')
                ->extraAttributes([
                    'data-testid' => 'kpi-revenue',
                    'data-trend' => $revenueTrend,
                    'data-testid-parent' => 'live-updates',
                    'dusk' => 'monthly-revenue-kpi',
                ]),
        ];
    }
}
