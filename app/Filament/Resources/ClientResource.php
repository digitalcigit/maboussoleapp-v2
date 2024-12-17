<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('manage clients');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('manage clients');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('manage clients');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->can('manage clients');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->can('manage clients');
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()->can('manage clients');
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()->can('manage clients');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations Personnelles')
                    ->schema([
                        Forms\Components\TextInput::make('client_number')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->label('Numéro Client'),
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
                            ->required()
                            ->maxLength(255)
                            ->label('Téléphone'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Suivi')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Actif',
                                'inactive' => 'Inactif',
                                'completed' => 'Terminé',
                            ])
                            ->required()
                            ->default('active')
                            ->label('Statut'),
                        Forms\Components\Select::make('assigned_to')
                            ->relationship('assignedTo', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Assigné à'),
                        Forms\Components\Select::make('prospect_id')
                            ->relationship('prospect', 'email')
                            ->searchable()
                            ->preload()
                            ->label('Prospect d\'origine')
                            ->required(),
                        Forms\Components\TextInput::make('total_amount')
                            ->numeric()
                            ->label('Montant total')
                            ->required()
                            ->rules(['numeric', 'min:0']),
                        Forms\Components\TextInput::make('paid_amount')
                            ->numeric()
                            ->label('Montant payé')
                            ->required()
                            ->rules(['numeric', 'min:0'])
                            ->rules([
                                function (Forms\Get $get) {
                                    return function (string $attribute, $value, $fail) use ($get) {
                                        $totalAmount = (float) $get('total_amount');
                                        if ((float) $value > $totalAmount) {
                                            $fail("Le montant payé ne peut pas être supérieur au montant total.");
                                        }
                                    };
                                }
                            ]),
                    ])->columns(2),
            ]);
    }

    public static function afterCreate(Model $record): void
    {
        if ($record->prospect) {
            $record->prospect->update(['status' => 'converted']);
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client_number')
                    ->searchable()
                    ->sortable()
                    ->label('Numéro Client'),
                Tables\Columns\TextColumn::make('full_name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable()
                    ->label('Nom Complet'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->label('Téléphone'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        'completed' => 'info',
                        default => 'secondary',
                    })
                    ->label('Statut'),
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->searchable()
                    ->sortable()
                    ->label('Assigné à'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Date de création'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Actif',
                        'inactive' => 'Inactif',
                        'completed' => 'Terminé',
                    ])
                    ->label('Statut'),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'paid' => 'Payé',
                        'partially_paid' => 'Partiellement payé',
                        'unpaid' => 'Non payé',
                    ])
                    ->query(function (EloquentBuilder $query, array $data): EloquentBuilder {
                        return match ($data['value']) {
                            'paid' => $query->whereColumn('paid_amount', '=', 'total_amount'),
                            'partially_paid' => $query->whereColumn('paid_amount', '<', 'total_amount')
                                ->where('paid_amount', '>', 0),
                            'unpaid' => $query->where('paid_amount', 0),
                            default => $query,
                        };
                    })
                    ->label('Statut de paiement'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('assign')
                        ->label('Assigner')
                        ->form([
                            Forms\Components\Select::make('assigned_to')
                                ->label('Assigné à')
                                ->relationship('assignedTo', 'name')
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            // Debug avant la mise à jour
                            Log::info('Starting bulk assign action', [
                                'records' => $records->pluck('id', 'assigned_to')->toArray(),
                                'assigned_to' => $data['assigned_to'],
                                'raw_data' => $data,
                            ]);

                            // Récupérer l'ID de l'utilisateur depuis les données du formulaire
                            $assignedToId = $data['assigned_to'];
                            if (!$assignedToId) {
                                return;
                            }

                            // Mise à jour via Eloquent
                            foreach ($records as $client) {
                                // Log avant la mise à jour de chaque client
                                Log::info('Updating client', [
                                    'client_id' => $client->id,
                                    'old_assigned_to' => $client->assigned_to,
                                    'new_assigned_to' => $assignedToId,
                                ]);

                                $client->update(['assigned_to' => $assignedToId]);

                                // Vérifier la mise à jour
                                $client->refresh();
                                Log::info('Client updated', [
                                    'client_id' => $client->id,
                                    'assigned_to' => $client->assigned_to,
                                    'raw_attributes' => $client->getAttributes(),
                                ]);
                            }
                        }),
                ]),
            ])
            ->defaultSort('client_number', 'desc');
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
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'view' => Pages\ViewClient::route('/{record}'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informations Personnelles')
                    ->schema([
                        Infolists\Components\TextEntry::make('client_number')
                            ->label('Numéro Client'),
                        Infolists\Components\TextEntry::make('first_name')
                            ->label('Prénom'),
                        Infolists\Components\TextEntry::make('last_name')
                            ->label('Nom'),
                        Infolists\Components\TextEntry::make('email')
                            ->label('Email'),
                        Infolists\Components\TextEntry::make('phone')
                            ->label('Téléphone'),
                    ])->columns(2),
                Infolists\Components\Section::make('Suivi')
                    ->schema([
                        Infolists\Components\TextEntry::make('status')
                            ->label('Statut'),
                        Infolists\Components\TextEntry::make('assignedTo.name')
                            ->label('Assigné à'),
                        Infolists\Components\TextEntry::make('prospect.email')
                            ->label('Prospect d\'origine'),
                    ])->columns(2),
            ]);
    }
}
