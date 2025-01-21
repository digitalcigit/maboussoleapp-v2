<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Models\Activity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    protected static ?string $recordTitleAttribute = 'description';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->label('Assigné à'),

                Forms\Components\Select::make('type')
                    ->options([
                        Activity::TYPE_NOTE => 'Note',
                        Activity::TYPE_CALL => 'Appel',
                        Activity::TYPE_EMAIL => 'Email',
                        Activity::TYPE_MEETING => 'Réunion',
                        Activity::TYPE_DOCUMENT => 'Document',
                    ])
                    ->required()
                    ->label('Type'),

                Forms\Components\Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\DateTimePicker::make('scheduled_at')
                    ->required()
                    ->label('Planifié pour'),

                Forms\Components\DateTimePicker::make('completed_at')
                    ->label('Terminé le'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Assigné à')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Activity::TYPE_NOTE => 'Note',
                        Activity::TYPE_CALL => 'Appel',
                        Activity::TYPE_EMAIL => 'Email',
                        Activity::TYPE_MEETING => 'Réunion',
                        Activity::TYPE_DOCUMENT => 'Document',
                    })
                    ->colors([
                        'primary' => Activity::TYPE_NOTE,
                        'success' => Activity::TYPE_CALL,
                        'info' => Activity::TYPE_EMAIL,
                        'warning' => Activity::TYPE_MEETING,
                        'danger' => Activity::TYPE_DOCUMENT,
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getLimit()) {
                            return null;
                        }

                        return $state;
                    }),

                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Planifié pour')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Terminé le')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        Activity::TYPE_NOTE => 'Note',
                        Activity::TYPE_CALL => 'Appel',
                        Activity::TYPE_EMAIL => 'Email',
                        Activity::TYPE_MEETING => 'Réunion',
                        Activity::TYPE_DOCUMENT => 'Document',
                    ]),

                Tables\Filters\SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Assigné à'),

                Tables\Filters\TrashedFilter::make()
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }
}
