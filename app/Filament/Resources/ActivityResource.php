<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages;
use App\Models\Activity;
use App\Models\Prospect;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    
    protected static ?string $navigationLabel = 'Activités';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('subject_type')
                    ->label('Type de sujet')
                    ->options([
                        Prospect::class => 'Prospect',
                    ])
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('subject_id', null)),
                Forms\Components\Select::make('subject_id')
                    ->label('Sujet')
                    ->options(function (Forms\Get $get) {
                        $type = $get('subject_type');
                        if (!$type) {
                            return [];
                        }
                        
                        if ($type === Prospect::class) {
                            return Prospect::query()
                                ->get()
                                ->mapWithKeys(function ($prospect) {
                                    $fullName = trim($prospect->first_name . ' ' . $prospect->last_name);
                                    return [$prospect->id => $fullName ?: "Prospect #{$prospect->id}"];
                                })
                                ->toArray();
                        }
                        
                        return [];
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->visible(fn (Forms\Get $get) => filled($get('subject_type'))),
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
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('scheduled_at')
                    ->label('Date prévue')
                    ->required(),
                Forms\Components\DateTimePicker::make('completed_at')
                    ->label('Date de réalisation'),
                Forms\Components\Select::make('user_id')
                    ->label('Assigné à')
                    ->relationship('assignedTo', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Hidden::make('created_by')
                    ->default(auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subject')
                    ->label('Sujet')
                    ->formatStateUsing(function ($record) {
                        if ($record->subject instanceof Prospect) {
                            $fullName = trim($record->subject->first_name . ' ' . $record->subject->last_name);
                            return $fullName ?: "Prospect #{$record->subject->id}";
                        }
                        return $record->subject ? class_basename($record->subject) . ' #' . $record->subject->id : '';
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHasMorph(
                            'subject',
                            [Prospect::class],
                            function (Builder $query) use ($search): Builder {
                                return $query->where('first_name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%");
                            }
                        );
                    }),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Activity::TYPE_NOTE => 'primary',
                        Activity::TYPE_CALL => 'warning',
                        Activity::TYPE_EMAIL => 'success',
                        Activity::TYPE_MEETING => 'info',
                        Activity::TYPE_DOCUMENT => 'secondary',
                        Activity::TYPE_CONVERSION => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Activity::TYPE_NOTE => 'Note',
                        Activity::TYPE_CALL => 'Appel',
                        Activity::TYPE_EMAIL => 'Email',
                        Activity::TYPE_MEETING => 'Réunion',
                        Activity::TYPE_DOCUMENT => 'Document',
                        Activity::TYPE_CONVERSION => 'Conversion',
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
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->label('Modifier')
                        ->icon('heroicon-o-pencil')
                        ->color('primary')
                        ->modalHeading('Modifier l\'activité')
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Activité modifiée avec succès')
                        ),
                    Tables\Actions\DeleteAction::make()
                        ->label('Supprimer')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Supprimer l\'activité')
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Activité supprimée avec succès')
                        )
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->tooltip('Actions')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Activités supprimées avec succès')
                        ),
                ])
            ])
            ->defaultSort('created_at', 'desc')
            ->persistSortInSession();
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

    public static function getNavigationGroup(): ?string
    {
        return 'CRM';
    }

    public static function getModelLabel(): string
    {
        return __('Activité');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Activités');
    }

    public static function canViewAny(): bool
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
}
