<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages;
use App\Models\Activity;
use App\Models\Client;
use App\Models\Prospect;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getModelLabel(): string
    {
        return __('Activité');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Activités');
    }

    public static function getNavigationGroup(): ?string
    {
        return 'CRM';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-calendar';
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('activities.view_any');
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()->can('activities.view');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('activities.create');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->can('activities.edit');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->can('activities.delete');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->required()
                    ->options([
                        Activity::TYPE_CALL => 'Call',
                        Activity::TYPE_EMAIL => 'Email',
                        Activity::TYPE_MEETING => 'Meeting',
                        Activity::TYPE_NOTE => 'Note',
                        Activity::TYPE_TASK => 'Task',
                    ])
                    ->in([
                        Activity::TYPE_CALL,
                        Activity::TYPE_EMAIL,
                        Activity::TYPE_MEETING,
                        Activity::TYPE_NOTE,
                        Activity::TYPE_TASK,
                    ]),
                Forms\Components\Select::make('status')
                    ->required()
                    ->options([
                        Activity::STATUS_PENDING => 'Pending',
                        Activity::STATUS_IN_PROGRESS => 'In Progress',
                        Activity::STATUS_COMPLETED => 'Completed',
                        Activity::STATUS_CANCELLED => 'Cancelled',
                    ])
                    ->in([
                        Activity::STATUS_PENDING,
                        Activity::STATUS_IN_PROGRESS,
                        Activity::STATUS_COMPLETED,
                        Activity::STATUS_CANCELLED,
                    ]),
                Forms\Components\DateTimePicker::make('scheduled_at')
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'email')
                    ->required(),
                Forms\Components\Select::make('prospect_id')
                    ->relationship('prospect', 'email')
                    ->label('Prospect')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('client_id')
                    ->relationship('client', 'email')
                    ->label('Client')
                    ->searchable()
                    ->preload(),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        if (! empty($data['prospect_id'])) {
            $data['subject_type'] = \App\Models\Prospect::class;
            $data['subject_id'] = $data['prospect_id'];
        } elseif (! empty($data['client_id'])) {
            $data['subject_type'] = \App\Models\Client::class;
            $data['subject_id'] = $data['client_id'];
        }

        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject_type')
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        Activity::TYPE_CALL => 'Appel',
                        Activity::TYPE_EMAIL => 'Email',
                        Activity::TYPE_MEETING => 'Rendez-vous',
                        Activity::TYPE_NOTE => 'Note',
                        Activity::TYPE_TASK => 'Tâche',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        Activity::STATUS_PENDING => 'En attente',
                        Activity::STATUS_IN_PROGRESS => 'En cours',
                        Activity::STATUS_COMPLETED => 'Terminé',
                        Activity::STATUS_CANCELLED => 'Annulé',
                    ]),
                Tables\Filters\SelectFilter::make('subject_type')
                    ->options([
                        Client::class => 'Client',
                        Prospect::class => 'Prospect',
                    ])
                    ->label('Type de sujet'),
                Tables\Filters\Filter::make('scheduled_at')
                    ->form([
                        Forms\Components\DatePicker::make('since')
                            ->label('Depuis'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Jusqu\'à'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['since'],
                                fn (Builder $query, $date): Builder => $query->whereDate('scheduled_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('scheduled_at', '<=', $date),
                            );
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
