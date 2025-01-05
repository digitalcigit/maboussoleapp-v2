<?php

namespace App\Filament\Resources\ProspectResource\RelationManagers;

use App\Models\Activity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    protected static ?string $recordTitleAttribute = 'description';

    protected static ?string $title = 'Activités';

    protected static ?string $modelLabel = 'activité';

    protected static ?string $pluralModelLabel = 'activités';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->label('Type')
                    ->options([
                        Activity::TYPE_NOTE => 'Note',
                        Activity::TYPE_CALL => 'Appel',
                        Activity::TYPE_EMAIL => 'Email',
                        Activity::TYPE_MEETING => 'Réunion',
                        Activity::TYPE_DOCUMENT => 'Document',
                        Activity::TYPE_CONVERSION => 'Conversion',
                    ])
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Statut')
                    ->options([
                        Activity::STATUS_PENDING => 'En attente',
                        Activity::STATUS_IN_PROGRESS => 'En cours',
                        Activity::STATUS_COMPLETED => 'Terminé',
                        Activity::STATUS_CANCELLED => 'Annulé',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('scheduled_at')
                    ->label('Date prévue'),
                Forms\Components\DateTimePicker::make('completed_at')
                    ->label('Date de réalisation'),
                Forms\Components\Select::make('user_id')
                    ->label('Assigné à')
                    ->relationship('assignedTo', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Hidden::make('created_by')
                    ->default(auth()->id()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'primary' => Activity::TYPE_NOTE,
                        'warning' => Activity::TYPE_CALL,
                        'success' => Activity::TYPE_EMAIL,
                        'info' => Activity::TYPE_MEETING,
                        'secondary' => Activity::TYPE_DOCUMENT,
                        'danger' => Activity::TYPE_CONVERSION,
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Activity::TYPE_NOTE => 'Note',
                        Activity::TYPE_CALL => 'Appel',
                        Activity::TYPE_EMAIL => 'Email',
                        Activity::TYPE_MEETING => 'Réunion',
                        Activity::TYPE_DOCUMENT => 'Document',
                        Activity::TYPE_CONVERSION => 'Conversion',
                        default => $state,
                    }),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'warning' => Activity::STATUS_PENDING,
                        'primary' => Activity::STATUS_IN_PROGRESS,
                        'success' => Activity::STATUS_COMPLETED,
                        'danger' => Activity::STATUS_CANCELLED,
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Activity::STATUS_PENDING => 'En attente',
                        Activity::STATUS_IN_PROGRESS => 'En cours',
                        Activity::STATUS_COMPLETED => 'Terminé',
                        Activity::STATUS_CANCELLED => 'Annulé',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50),
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Date prévue')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Date de réalisation')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Assigné à')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        Activity::STATUS_PENDING => 'En attente',
                        Activity::STATUS_IN_PROGRESS => 'En cours',
                        Activity::STATUS_COMPLETED => 'Terminé',
                        Activity::STATUS_CANCELLED => 'Annulé',
                    ]),
                Tables\Filters\Filter::make('scheduled_at')
                    ->form([
                        Forms\Components\DatePicker::make('scheduled_from')
                            ->label('Date prévue depuis'),
                        Forms\Components\DatePicker::make('scheduled_until')
                            ->label('Date prévue jusqu\'à'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['scheduled_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('scheduled_at', '>=', $date),
                            )
                            ->when(
                                $data['scheduled_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('scheduled_at', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Créer')
                    ->modalHeading('Créer une activité'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Modifier')
                    ->modalHeading('Modifier l\'activité')
                    ->after(function () {
                        Notification::make()
                            ->title('Activité modifiée')
                            ->body('L\'activité a été modifiée avec succès.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->label('Supprimer')
                    ->modalHeading('Supprimer l\'activité')
                    ->after(function () {
                        Notification::make()
                            ->title('Activité supprimée')
                            ->body('L\'activité a été supprimée avec succès.')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Supprimer la sélection')
                        ->modalHeading('Supprimer les activités sélectionnées')
                        ->after(function () {
                            Notification::make()
                                ->title('Activités supprimées')
                                ->body('Les activités sélectionnées ont été supprimées avec succès.')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }
}
