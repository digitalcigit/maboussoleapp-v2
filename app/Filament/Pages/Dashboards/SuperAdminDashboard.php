<?php

namespace App\Filament\Pages\Dashboards;

use App\Filament\Widgets\FinancialMetricsWidget;
use App\Filament\Widgets\FinancialPerformanceChart;
use App\Filament\Widgets\LatestTransactionsWidget;
use App\Filament\Widgets\MonthlyGoalsWidget;
use App\Filament\Widgets\ProspectFunnelWidget;
use Filament\Pages\Dashboard;
use Illuminate\Contracts\Support\Htmlable;

class SuperAdminDashboard extends Dashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = 'Tableaux de Bord';

    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'md' => 3,
            'lg' => 4,
        ];
    }

    public static function shouldRegister(): bool
    {
        return auth()->user()->hasRole('super-admin');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Tableau de Bord Administrateur');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            FinancialMetricsWidget::class,
            ProspectFunnelWidget::class,
            FinancialPerformanceChart::class,
            MonthlyGoalsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            LatestTransactionsWidget::class,
        ];
    }

    public function getWidgets(): array
    {
        return [
            FinancialMetricsWidget::class,
            ProspectFunnelWidget::class,
            FinancialPerformanceChart::class,
            MonthlyGoalsWidget::class,
            LatestTransactionsWidget::class,
        ];
    }
}
