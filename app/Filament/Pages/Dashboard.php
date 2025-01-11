<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\FinancialMetricsWidget;
use App\Filament\Widgets\FinancialPerformanceChart;
use App\Filament\Widgets\MonthlyGoalsWidget;
use App\Filament\Widgets\ProspectFunnelWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\AccountWidget;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = -2;

    protected static ?string $navigationLabel = 'Tableau de Bord';

    protected function getHeaderWidgets(): array
    {
        $user = auth()->user();
        $widgets = [];

        // Widget de compte en premier
        $widgets[] = AccountWidget::class;

        // Widgets communs à tous les rôles
        $widgets[] = ProspectFunnelWidget::class;

        // Widgets spécifiques aux rôles super_admin et manager
        if ($user && ($user->role === 'super_admin' || $user->role === 'manager')) {
            $widgets[] = FinancialMetricsWidget::class;
        }

        return $widgets;
    }

    protected function getFooterWidgets(): array
    {
        $user = auth()->user();
        $widgets = [];

        // Widgets spécifiques aux rôles super_admin et manager
        if ($user && ($user->role === 'super_admin' || $user->role === 'manager')) {
            $widgets[] = FinancialPerformanceChart::class;
            $widgets[] = MonthlyGoalsWidget::class;
        }

        return $widgets;
    }

    public function getColumns(): int | string | array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'md' => 3,
            'lg' => 4,
        ];
    }

    public static function canView(): bool
    {
        return auth()->check(); // Autorise tous les utilisateurs connectés
    }
}
