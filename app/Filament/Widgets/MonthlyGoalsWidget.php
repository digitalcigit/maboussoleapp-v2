<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\Prospect;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MonthlyGoalsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Objectifs mensuels (à terme, ces valeurs viendront de la configuration)
        $revenueGoal = 100000; // 100k€
        $prospectsGoal = 50;   // 50 prospects
        $conversionGoal = 20;   // 20%

        // Valeurs actuelles
        $currentRevenue = Client::whereMonth('created_at', now()->month)
            ->sum('total_amount');

        $currentProspects = Prospect::whereMonth('created_at', now()->month)
            ->count();

        $convertedProspects = Client::whereMonth('created_at', now()->month)
            ->count();
        $totalProspects = Prospect::whereMonth('created_at', now()->month)
            ->count();
        $conversionRate = $totalProspects > 0
            ? round(($convertedProspects / $totalProspects) * 100, 1)
            : 0;

        // Calcul des pourcentages
        $revenueProgress = min(100, round(($currentRevenue / $revenueGoal) * 100));
        $prospectsProgress = min(100, round(($currentProspects / $prospectsGoal) * 100));
        $conversionProgress = min(100, round(($conversionRate / $conversionGoal) * 100));

        return [
            Stat::make('Objectif CA', number_format($revenueGoal, 0, ',', ' ').' €')
                ->description("$revenueProgress% atteint")
                ->descriptionIcon('heroicon-m-chart-bar')
                ->chart([0, $revenueProgress, 100])
                ->color($revenueProgress >= 100 ? 'success' : 'info'),

            Stat::make('Objectif Prospects', "$prospectsGoal prospects")
                ->description("$prospectsProgress% atteint")
                ->descriptionIcon('heroicon-m-user-group')
                ->chart([0, $prospectsProgress, 100])
                ->color($prospectsProgress >= 100 ? 'success' : 'info'),

            Stat::make('Objectif Conversion', "$conversionGoal%")
                ->description("$conversionProgress% atteint")
                ->descriptionIcon('heroicon-m-arrow-path')
                ->chart([0, $conversionProgress, 100])
                ->color($conversionProgress >= 100 ? 'success' : 'info'),
        ];
    }
}
