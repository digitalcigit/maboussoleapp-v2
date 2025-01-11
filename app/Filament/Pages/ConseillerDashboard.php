<?php

namespace App\Filament\Pages;

use App\Models\Prospect;
use Filament\Pages\Dashboard;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget\StatsOverview;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Colors\Color;
use Filament\Widgets\ChartWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ConseillerDashboard extends Dashboard
{
    protected static ?string $navigationLabel = 'Tableau de Bord';
    protected static ?string $title = 'Tableau de Bord Conseiller';
    protected static ?string $slug = 'conseiller-dashboard';
    protected static ?int $navigationSort = -2;
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    public function getWidgets(): array
    {
        return [
            ConseillerStatsWidget::class,
            ConseillerConversionChart::class,
            ConseillerProspectStatusChart::class,
            ConseillerPriorityProspectsWidget::class,
            ConseillerTasksWidget::class,
        ];
    }

    public static function shouldRegister(): bool
    {
        return auth()->user()->hasRole('conseiller');
    }
}

class ConseillerStatsWidget extends StatsOverview
{
    protected function getStats(): array
    {
        $user = Auth::user();
        
        return [
            Stat::make('Mes Prospects Actifs', Prospect::where('conseiller_id', $user->id)->where('status', 'actif')->count())
                ->description('Prospects en cours de traitement')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info')
                ->chart([3, 7, 5, 4, 8, 6]),

            Stat::make('Taux de Conversion', function() use ($user) {
                $total = Prospect::where('conseiller_id', $user->id)->count();
                $converted = Prospect::where('conseiller_id', $user->id)->where('status', 'converti')->count();
                return $total > 0 ? round(($converted / $total) * 100) . '%' : '0%';
            })
                ->description('Par rapport au mois dernier')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('À Contacter', Prospect::where('conseiller_id', $user->id)
                ->where('next_contact', '<=', now()->addDays(2))
                ->count())
                ->description('Prospects à contacter en priorité')
                ->descriptionIcon('heroicon-m-phone')
                ->color('warning'),
        ];
    }
}

class ConseillerConversionChart extends ChartWidget
{
    protected static ?string $heading = 'Mes Conversions';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $user = Auth::user();
        $data = collect(range(5, 0))->map(function ($month) use ($user) {
            $date = now()->subMonths($month);
            return Prospect::where('conseiller_id', $user->id)
                ->where('status', 'converti')
                ->whereYear('converted_at', $date->year)
                ->whereMonth('converted_at', $date->month)
                ->count();
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Conversions',
                    'data' => $data,
                    'fill' => 'start',
                ],
            ],
            'labels' => collect(range(5, 0))->map(fn ($month) => 
                now()->subMonths($month)->format('M Y')
            )->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}

class ConseillerProspectStatusChart extends ChartWidget
{
    protected static ?string $heading = 'État des Prospects';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $user = Auth::user();
        $statuses = ['Nouveau', 'En cours', 'Prêt', 'Bloqué'];
        
        $data = collect($statuses)->map(function ($status) use ($user) {
            return Prospect::where('conseiller_id', $user->id)
                ->where('status', strtolower($status))
                ->count();
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Prospects',
                    'data' => $data,
                    'backgroundColor' => [
                        Color::Blue,
                        Color::Yellow,
                        Color::Green,
                        Color::Red,
                    ],
                ],
            ],
            'labels' => $statuses,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}

class ConseillerPriorityProspectsWidget extends TableWidget
{
    protected static ?string $heading = 'Prospects Prioritaires';
    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Prospect::query()
                    ->where('conseiller_id', Auth::id())
                    ->where('priority', 'high')
                    ->latest('last_action_at')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
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
    }
}

class ConseillerTasksWidget extends TableWidget
{
    protected static ?string $heading = 'Tâches du Jour';
    protected static ?int $sort = 5;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Prospect::query()
                    ->where('conseiller_id', Auth::id())
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
    }
}
