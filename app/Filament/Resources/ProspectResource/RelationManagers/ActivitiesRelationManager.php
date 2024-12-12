<?php

namespace App\Filament\Resources\ProspectResource\RelationManagers;

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

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $title = 'Activités';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->label('Titre'),
                Forms\Components\Select::make('type')
                    ->options([
                        'appel' => 'Appel',
                        'email' => 'Email',
                        'reunion' => 'Réunion',
                        'note' => 'Note',
                        'autre' => 'Autre',
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
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'appel' => 'success',
                        'email' => 'info',
                        'reunion' => 'warning',
                        'note' => 'gray',
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
                        'appel' => 'Appel',
                        'email' => 'Email',
                        'reunion' => 'Réunion',
                        'note' => 'Note',
                        'autre' => 'Autre',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'planifié' => 'Planifié',
                        'en_cours' => 'En cours',
                        'terminé' => 'Terminé',
                        'annulé' => 'Annulé',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }
}
