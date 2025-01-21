<?php

namespace App\Filament\Pages;

use App\Models\Client;
use App\Models\Prospect;
use Filament\Pages\Dashboard;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\TableWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ConseillerDashboard extends Dashboard
{
    protected static ?string $navigationLabel = 'Tableau de bord';

    protected static ?string $title = 'Tableau de bord';

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = -2;

    protected function getHeaderWidgets(): array
    {
        $user = auth()->user();

        // Statistiques des prospects
        $totalProspects = Prospect::where('conseiller_id', $user->id)->count();
        $newProspects = Prospect::where('conseiller_id', $user->id)
            ->whereMonth('created_at', now()->month)
            ->count();
        $prospectsTrend = $newProspects;

        // Statistiques des conversions
        $totalConversions = Client::whereMonth('created_at', now()->month)
            ->whereHas('prospect', function ($query) use ($user) {
                $query->where('conseiller_id', $user->id);
            })
            ->count();

        $conversionRate = $totalProspects > 0
            ? round(($totalConversions / $totalProspects) * 100, 1)
            : 0;

        // Chiffre d'affaires
        $currentMonthRevenue = Client::whereMonth('created_at', now()->month)
            ->whereHas('prospect', function ($query) use ($user) {
                $query->where('conseiller_id', $user->id);
            })
            ->sum('total_amount');

        $lastMonthRevenue = Client::whereMonth('created_at', now()->subMonth()->month)
            ->whereHas('prospect', function ($query) use ($user) {
                $query->where('conseiller_id', $user->id);
            })
            ->sum('total_amount');

        $revenueTrend = $currentMonthRevenue - $lastMonthRevenue;

        return [
            StatsOverviewWidget::make([
                Stat::make('Mes Prospects', $totalProspects)
                    ->description($prospectsTrend >= 0 ? "+$prospectsTrend ce mois" : "$prospectsTrend ce mois")
                    ->descriptionIcon($prospectsTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                    ->color($prospectsTrend >= 0 ? 'success' : 'danger')
                    ->chart([7, 4, 6, 8, 5, 3, $totalProspects]),

                Stat::make('Taux de Conversion', $conversionRate.'%')
                    ->description($totalConversions.' conversions ce mois')
                    ->descriptionIcon('heroicon-m-arrow-path')
                    ->color($conversionRate >= 20 ? 'success' : 'warning')
                    ->chart([4, 5, 3, 6, 3, 4, $conversionRate]),

                Stat::make('Chiffre d\'Affaires', number_format($currentMonthRevenue, 0, ',', ' ').' FCFA')
                    ->description($revenueTrend >= 0 ? '+'.number_format($revenueTrend, 0, ',', ' ').' FCFA' : number_format($revenueTrend, 0, ',', ' ').' FCFA')
                    ->descriptionIcon($revenueTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                    ->color($revenueTrend >= 0 ? 'success' : 'danger')
                    ->chart([8, 9, 7, 8, 6, 9, $currentMonthRevenue / 1000]),
            ]),
        ];
    }

    protected function getFooterWidgets(): array
    {
        $user = auth()->user();

        // Statistiques par étape
        $stages = [
            Prospect::STATUS_NOUVEAU => 'Nouveaux',
            Prospect::STATUS_QUALIFIE => 'Qualifiés',
            Prospect::STATUS_TRAITEMENT => 'En traitement',
            Prospect::STATUS_BLOQUE => 'Bloqués',
            Prospect::STATUS_CONVERTI => 'Convertis',
        ];

        $data = collect($stages)->map(function ($label, $status) use ($user) {
            return Prospect::where('conseiller_id', $user->id)
                ->where('current_status', $status)
                ->count();
        })->values()->toArray();

        return [
            // Widget de l'entonnoir de conversion
            ChartWidget::make()
                ->heading('Entonnoir de Conversion')
                ->description('Répartition des prospects par étape')
                ->chart([
                    'type' => 'bar',
                    'data' => [
                        'labels' => array_values($stages),
                        'datasets' => [
                            [
                                'label' => 'Prospects',
                                'data' => $data,
                                'backgroundColor' => [
                                    '#3B82F6', // Bleu - Nouveaux
                                    '#10B981', // Vert - Qualifiés
                                    '#F59E0B', // Orange - En traitement
                                    '#EC4899', // Rose - Bloqués
                                    '#06B6D4', // Cyan - Convertis
                                ],
                            ],
                        ],
                    ],
                    'options' => [
                        'plugins' => [
                            'legend' => [
                                'display' => false,
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
                    ],
                ])
                ->columns(12),

            // Widget des prospects prioritaires
            TableWidget::make()
                ->heading('Prospects Prioritaires')
                ->table(function (Table $table) use ($user) {
                    return $table
                        ->query(
                            Prospect::query()
                                ->where('conseiller_id', $user->id)
                                ->where('priority', 'high')
                                ->latest('last_action_at')
                        )
                        ->columns([
                            TextColumn::make('name')
                                ->label('Nom')
                                ->searchable()
                                ->sortable(),
                            TextColumn::make('current_status')
                                ->label('Statut')
                                ->badge()
                                ->sortable(),
                            TextColumn::make('last_action_at')
                                ->label('Dernière Action')
                                ->dateTime()
                                ->sortable(),
                            TextColumn::make('priority')
                                ->label('Priorité')
                                ->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    'high' => 'danger',
                                    'medium' => 'warning',
                                    default => 'info',
                                })
                        ])
                        ->defaultSort('last_action_at', 'desc');
                })
                ->columns(12),

            // Widget des tâches du jour
            TableWidget::make()
                ->heading('Tâches du Jour')
                ->table(function (Table $table) use ($user) {
                    return $table
                        ->query(
                            Prospect::query()
                                ->where('conseiller_id', $user->id)
                                ->where('next_action_date', '<=', now()->endOfDay())
                                ->whereNotNull('next_action')
                        )
                        ->columns([
                            TextColumn::make('name')
                                ->label('Prospect')
                                ->searchable()
                                ->sortable(),
                            TextColumn::make('next_action')
                                ->label('Action')
                                ->wrap(),
                            TextColumn::make('next_action_date')
                                ->label('Échéance')
                                ->dateTime()
                                ->sortable(),
                        ])
                        ->defaultSort('next_action_date', 'asc');
                })
                ->columns(12),
        ];
    }

    public static function shouldRegister(): bool
    {
        return auth()->user()->hasRole('conseiller');
    }
}
