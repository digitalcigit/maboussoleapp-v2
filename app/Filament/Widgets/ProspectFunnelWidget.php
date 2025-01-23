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
            Prospect::STATUS_NOUVEAU => 'Nouveaux',
            Prospect::STATUS_QUALIFIE => 'Qualifiés',
            Prospect::STATUS_TRAITEMENT => 'En traitement',
            Prospect::STATUS_BLOQUE => 'Bloqués',
            Prospect::STATUS_CONVERTI => 'Convertis',
        ];

        $query = Prospect::query()
            ->select('current_status', DB::raw('count(*) as count'))
            ->whereMonth('created_at', now()->month);

        $user = auth()->user();
        
        // Filtrer les prospects selon le rôle
        if ($user->role === 'conseiller') {
            $query->where('conseiller_id', $user->id);
        } elseif ($user->role === 'manager') {
            $query->whereHas('conseiller', function ($q) use ($user) {
                $q->where('manager_id', $user->id);
            });
        }
        // super_admin voit tous les prospects

        $data = $query->groupBy('current_status')
            ->pluck('count', 'current_status')
            ->toArray();

        $colors = [
            '#3B82F6', // Bleu - Nouveaux
            '#10B981', // Vert - Qualifiés
            '#F59E0B', // Orange - En traitement
            '#EC4899', // Rose - Bloqués
            '#06B6D4', // Cyan - Convertis
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
