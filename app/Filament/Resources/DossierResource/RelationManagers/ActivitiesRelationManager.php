<?php

namespace App\Filament\Resources\DossierResource\RelationManagers;

use App\Models\Activity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    protected static ?string $title = 'Activités';

    protected static ?string $recordTitleAttribute = 'type';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->label('Type d\'activité')
                    ->options([
                        Activity::TYPE_NOTE => 'Note',
                        Activity::TYPE_CALL => 'Appel',
                        Activity::TYPE_EMAIL => 'Email',
                        Activity::TYPE_MEETING => 'Réunion',
                        Activity::TYPE_DOCUMENT => 'Document',
                    ])
                    ->required(),

                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->required()
                    ->maxLength(65535),

                Forms\Components\DateTimePicker::make('scheduled_at')
                    ->label('Date planifiée')
                    ->nullable(),

                Forms\Components\DateTimePicker::make('completed_at')
                    ->label('Date de réalisation')
                    ->nullable(),

                Forms\Components\Hidden::make('created_by')
                    ->default(auth()->id()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Activity::TYPE_NOTE => 'Note',
                        Activity::TYPE_CALL => 'Appel',
                        Activity::TYPE_EMAIL => 'Email',
                        Activity::TYPE_MEETING => 'Réunion',
                        Activity::TYPE_DOCUMENT => 'Document',
                        Activity::TYPE_CONVERSION => 'Conversion',
                        default => ucfirst($state),
                    })
                    ->colors([
                        'primary' => Activity::TYPE_NOTE,
                        'warning' => Activity::TYPE_CALL,
                        'success' => Activity::TYPE_EMAIL,
                        'danger' => Activity::TYPE_MEETING,
                        'info' => Activity::TYPE_DOCUMENT,
                        'gray' => Activity::TYPE_CONVERSION,
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Créé par')
                    ->sortable(),

                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Planifié pour')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Réalisé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        Activity::TYPE_NOTE => 'Note',
                        Activity::TYPE_CALL => 'Appel',
                        Activity::TYPE_EMAIL => 'Email',
                        Activity::TYPE_MEETING => 'Réunion',
                        Activity::TYPE_DOCUMENT => 'Document',
                        Activity::TYPE_CONVERSION => 'Conversion',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataBeforeCreate(function (array $data): array {
                        $data['created_by'] = auth()->id();
                        return $data;
                    }),
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
            ->defaultSort('created_at', 'desc');
    }
}
