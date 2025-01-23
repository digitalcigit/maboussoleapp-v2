<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestTransactionsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Client::query()
                    ->whereMonth('created_at', now()->month)
                    ->orderByDesc('total_amount')
            )
            ->columns([
                Tables\Columns\TextColumn::make('prospect.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Montant')
                    ->money('XAF')
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('Payé')
                    ->money('XAF')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Paiement')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'complete' => 'success',
                        'partiel' => 'warning',
                        'en_attente' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'complete' => 'Complété',
                        'partiel' => 'Partiel',
                        'en_attente' => 'En attente',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'complete' => 'Complété',
                        'partiel' => 'Partiel',
                        'en_attente' => 'En attente',
                    ]),

                Tables\Filters\Filter::make('high_value')
                    ->label('Haute valeur')
                    ->query(fn (Builder $query): Builder => $query->where('total_amount', '>', 10000)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->paginated([5, 10, 25, 50])
            ->defaultPaginationPageOption(5);
    }
}
