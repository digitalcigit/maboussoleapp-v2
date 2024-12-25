<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use Filament\Widgets\ChartWidget;

class FinancialPerformanceChart extends ChartWidget
{
    protected static ?string $heading = 'Performance Financière';
    
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(function ($month) {
            return now()->subMonths($month)->format('M');
        })->toArray();

        $revenue = collect(range(5, 0))
            ->map(function ($month) {
                return Client::whereMonth('created_at', now()->subMonths($month))
                    ->sum('total_amount');
            })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Chiffre d\'Affaires',
                    'data' => $revenue,
                    'borderColor' => '#10B981',
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => '(value) => value + " €"',
                    ],
                ],
            ],
            'elements' => [
                'line' => [
                    'tension' => 0.3, // Courbes plus douces
                ],
            ],
        ];
    }
}
