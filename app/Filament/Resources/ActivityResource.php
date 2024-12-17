<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages;
use App\Filament\Resources\ActivityResource\RelationManagers;
use App\Models\Activity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations de l\'activité')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->label('Titre'),
                        Forms\Components\Select::make('type')
                            ->options([
                                'call' => 'Appel',
                                'email' => 'Email',
                                'meeting' => 'Réunion',
                                'note' => 'Note',
                                'task' => 'Tâche',
                            ])
                            ->required()
                            ->label('Type'),
                        Forms\Components\DateTimePicker::make('scheduled_at')
                            ->required()
                            ->label('Date prévue'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'planifié' => 'Planifié',
                                'en_cours' => 'En cours',
                                'terminé' => 'Terminé',
                                'annulé' => 'Annulé',
                            ])
                            ->required()
                            ->default('planifié')
                            ->label('Statut'),
                    ])->columns(2),

                Forms\Components\Section::make('Relations')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->default(fn () => auth()->id())
                            ->label('Utilisateur'),
                        Forms\Components\Select::make('prospect_id')
                            ->relationship('prospect', 'email')
                            ->searchable()
                            ->preload()
                            ->label('Prospect'),
                        Forms\Components\Select::make('client_id')
                            ->relationship('client', 'email')
                            ->searchable()
                            ->preload()
                            ->label('Client'),
                    ])->columns(3),

                Forms\Components\Section::make('Détails')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'call' => 'success',
                        'email' => 'info',
                        'meeting' => 'warning',
                        'note' => 'gray',
                        'task' => 'primary',
                        default => 'primary',
                    }),
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Date prévue')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\SelectColumn::make('status')
                    ->options([
                        'planifié' => 'Planifié',
                        'en_cours' => 'En cours',
                        'terminé' => 'Terminé',
                        'annulé' => 'Annulé',
                    ])
                    ->label('Statut'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->sortable(),
                Tables\Columns\TextColumn::make('prospect.email')
                    ->label('Prospect')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('client.email')
                    ->label('Client')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('scheduled_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'call' => 'Appel',
                        'email' => 'Email',
                        'meeting' => 'Réunion',
                        'note' => 'Note',
                        'task' => 'Tâche',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'planifié' => 'Planifié',
                        'en_cours' => 'En cours',
                        'terminé' => 'Terminé',
                        'annulé' => 'Annulé',
                    ]),
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Utilisateur'),
                Tables\Filters\SelectFilter::make('subject_type')
                    ->options([
                        \App\Models\Prospect::class => 'Prospect',
                        \App\Models\Client::class => 'Client',
                    ])
                    ->label('Type de sujet'),
                Tables\Filters\Filter::make('scheduled_at')
                    ->form([
                        Forms\Components\DateTimePicker::make('from')
                            ->label('Date de début'),
                        Forms\Components\DateTimePicker::make('until')
                            ->label('Date de fin'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->where('scheduled_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->where('scheduled_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->requiresConfirmation()
                        ->visible(fn (Activity $record) => auth()->user()->can('delete', $record)),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivities::route('/'),
            'create' => Pages\CreateActivity::route('/create'),
            'edit' => Pages\EditActivity::route('/{record}/edit'),
        ];
    }
}
