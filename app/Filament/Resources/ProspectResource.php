<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProspectResource\Pages;
use App\Filament\Resources\ProspectResource\RelationManagers;
use App\Models\Prospect;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class ProspectResource extends Resource
{
    protected static ?string $model = Prospect::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations Personnelles')
                    ->schema([
                        Forms\Components\TextInput::make('reference_number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn () => 'PROS-' . random_int(10000, 99999)),
                        Forms\Components\TextInput::make('first_name')
                            ->required()
                            ->maxLength(255)
                            ->label('Prénom'),
                        Forms\Components\TextInput::make('last_name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nom'),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->required()
                            ->maxLength(255)
                            ->label('Téléphone'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Suivi')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'nouveau' => 'Nouveau',
                                'en_cours' => 'En cours',
                                'converti' => 'Converti',
                                'rejeté' => 'Rejeté',
                                'autre' => 'Autre',
                            ])
                            ->required()
                            ->default('nouveau')
                            ->label('Statut'),
                        Forms\Components\Select::make('assigned_to')
                            ->relationship('assignedTo', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Assigné à'),
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')
                    ->searchable()
                    ->sortable()
                    ->label('Référence'),
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable()
                    ->sortable()
                    ->label('Prénom'),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable()
                    ->sortable()
                    ->label('Nom'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->label('Téléphone'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'nouveau' => 'gray',
                        'en_cours' => 'warning',
                        'converti' => 'success',
                        'rejeté' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable()
                    ->label('Statut'),
                Tables\Columns\TextColumn::make('source')
                    ->searchable()
                    ->sortable()
                    ->label('Source'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'nouveau' => 'Nouveau',
                        'en_cours' => 'En cours',
                        'converti' => 'Converti',
                        'rejeté' => 'Rejeté',
                        'autre' => 'Autre',
                    ])
                    ->label('Statut'),
                Tables\Filters\SelectFilter::make('source')
                    ->options([
                        'website' => 'Site web',
                        'referral' => 'Parrainage',
                        'social' => 'Réseaux sociaux',
                        'other' => 'Autre',
                    ])
                    ->label('Source'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('convert')
                    ->label('Convertir en client')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->action(function ($record) {
                        $record->update(['status' => 'converti']);
                        $client = $record->convertToClient();
                        return redirect()->route('filament.admin.resources.clients.edit', ['record' => $client]);
                    })
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status !== 'converti'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('bulk-update')
                        ->label('Mise à jour en masse')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('Statut')
                                ->options([
                                    'nouveau' => 'Nouveau',
                                    'en_cours' => 'En cours',
                                    'converti' => 'Converti',
                                    'rejeté' => 'Rejeté',
                                    'autre' => 'Autre',
                                ])
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(fn ($record) => $record->update(['status' => $data['status']]));
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
    
    public static function getRelations(): array
    {
        return [
            RelationManagers\ActivitiesRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProspects::route('/'),
            'create' => Pages\CreateProspect::route('/create'),
            'edit' => Pages\EditProspect::route('/{record}/edit'),
            'convert' => Pages\ConvertToClient::route('/{record}/convert'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'CRM';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-user-group';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function getModelLabel(): string
    {
        return __('Prospect');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Prospects');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('prospects.view');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('prospects.create');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->can('prospects.edit');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->can('prospects.delete');
    }
}
