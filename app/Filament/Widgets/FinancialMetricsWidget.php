<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\Prospect;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinancialMetricsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        // CA Global
        $currentMonthRevenue = Client::whereMonth('created_at', now()->month)
            ->sum('total_amount');
        $lastMonthRevenue = Client::whereMonth('created_at', now()->subMonth()->month)
            ->sum('total_amount');
        $revenueTrend = $currentMonthRevenue - $lastMonthRevenue;

        // Commissions (20% du CA)
        $currentMonthCommissions = $currentMonthRevenue * 0.20;
        $lastMonthCommissions = $lastMonthRevenue * 0.20;
        $commissionsTrend = $currentMonthCommissions - $lastMonthCommissions;

        // Pipeline prévisionnel
        $qualifiedProspects = Prospect::where('current_status', 'qualifié')
            ->whereMonth('created_at', now()->month)
            ->count();
        $averageContractValue = Client::whereMonth('created_at', now()->month)
            ->avg('total_amount') ?? 0;
        $projectedRevenue = $qualifiedProspects * $averageContractValue;

        return [
            Stat::make('Chiffre d\'Affaires', number_format($currentMonthRevenue, 0, ',', ' ').' FCFA')
                ->description($revenueTrend >= 0 ? '+'.number_format($revenueTrend, 0, ',', ' ').' FCFA' : number_format($revenueTrend, 0, ',', ' ').' FCFA')
                ->descriptionIcon($revenueTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart([
                    $lastMonthRevenue / 1000,
                    $currentMonthRevenue / 1000,
                ])
                ->color($revenueTrend >= 0 ? 'success' : 'danger'),

            Stat::make('Commissions', number_format($currentMonthCommissions, 0, ',', ' ').' FCFA')
                ->description($commissionsTrend >= 0 ? '+'.number_format($commissionsTrend, 0, ',', ' ').' FCFA' : number_format($commissionsTrend, 0, ',', ' ').' FCFA')
                ->descriptionIcon($commissionsTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart([
                    $lastMonthCommissions / 1000,
                    $currentMonthCommissions / 1000,
                ])
                ->color($commissionsTrend >= 0 ? 'success' : 'danger'),

            Stat::make('Pipeline Prévisionnel', number_format($projectedRevenue, 0, ',', ' ').' FCFA')
                ->description($qualifiedProspects.' prospects qualifiés')
                ->descriptionIcon('heroicon-m-calculator')
                ->chart([
                    $averageContractValue / 1000,
                    $projectedRevenue / 1000,
                ])
                ->color('info'),
        ];
    }
}
