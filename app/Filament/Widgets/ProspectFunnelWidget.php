<?php

namespace App\Filament\Widgets;

use App\Models\Prospect;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ProspectFunnelWidget extends ChartWidget
{
    protected static ?string $heading = 'Entonnoir de Conversion';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $stages = [
            'nouveau' => 'Nouveaux',
            'contacté' => 'Contactés',
            'qualifié' => 'Qualifiés',
            'proposition' => 'Proposition',
            'négociation' => 'Négociation',
            'gagné' => 'Gagnés',
        ];

        $data = Prospect::query()
            ->select('status', DB::raw('count(*) as count'))
            ->whereMonth('created_at', now()->month)
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $colors = [
            '#3B82F6', // Bleu
            '#10B981', // Vert
            '#F59E0B', // Orange
            '#8B5CF6', // Violet
            '#EC4899', // Rose
            '#06B6D4', // Cyan
        ];

        return [
            'datasets' => [
                [
                    'data' => array_values($data),
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => array_values($stages),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'enabled' => true,
                    'callbacks' => [
                        'label' => '(context) => `${context.parsed.y} prospects`',
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Nombre de prospects',
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Étapes',
                    ],
                ],
            ],
            'maintainAspectRatio' => false,
            'height' => 300,
        ];
    }
}
