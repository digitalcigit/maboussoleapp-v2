<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function getModelLabel(): string
    {
        return __('Client');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Clients');
    }

    public static function canEdit(Model $record): bool
    {
        return true; // Temporairement autorisé pour tous
    }

    public static function canDelete(Model $record): bool
    {
        return true; // Temporairement autorisé pour tous
    }

    public static function canViewAny(): bool
    {
        return true; // Temporairement autorisé pour tous
    }

    public static function canCreate(): bool
    {
        return true; // Temporairement autorisé pour tous
    }

    public static function canView(Model $record): bool
    {
        return true; // Temporairement autorisé pour tous
    }

    public static function getNavigationGroup(): ?string
    {
        return 'CRM';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-users';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true; // Temporairement autorisé pour tous
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations Personnelles')
                    ->schema([
                        Forms\Components\TextInput::make('client_number')
                            ->label('Numéro Client')
                            ->default('CLI-' . random_int(10000, 99999))
                            ->disabled()
                            ->dehydrated()
                            ->required(),
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
                            ->unique(ignoreRecord: true)
                            ->rules(['email']),
                        Forms\Components\TextInput::make('phone')
                            ->required()
                            ->maxLength(255)
                            ->label('Téléphone')
                            ->tel()
                            ->rules(['regex:/^([0-9\s\-\+\(\)]*)$/']),
                    ])->columns(2),

                Forms\Components\Section::make('Suivi')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'actif' => 'Actif',
                                'inactif' => 'Inactif',
                                'en_attente' => 'En attente',
                                'archive' => 'Archivé',
                            ])
                            ->required()
                            ->default('actif')
                            ->label('Statut')
                            ->rules(['in:actif,inactif,en_attente,archive']),
                        Forms\Components\Select::make('assigned_to')
                            ->relationship('assignedTo', 'name')
                            ->options(function () {
                                $user = auth()->user();
                                
                                // Si conseiller, assignation automatique
                                if ($user->hasRole('conseiller')) {
                                    return [$user->id => $user->name];
                                }
                                
                                // Si manager, montrer uniquement les conseillers
                                if ($user->hasRole('manager')) {
                                    return User::role('conseiller')->pluck('name', 'id');
                                }
                                
                                // Si super-admin, montrer managers et conseillers
                                return User::role(['manager', 'conseiller'])->pluck('name', 'id');
                            })
                            ->default(function() {
                                $user = auth()->user();
                                return $user->hasRole('conseiller') ? $user->id : null;
                            })
                            ->disabled(fn() => auth()->user()->hasRole('conseiller'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Assigné à'),
                        Forms\Components\Select::make('prospect_id')
                            ->relationship('prospect', 'email')
                            ->searchable()
                            ->preload()
                            ->label('Prospect d\'origine'),
                        Forms\Components\TextInput::make('total_amount')
                            ->numeric()
                            ->label('Montant total')
                            ->required()
                            ->suffix('FCFA')
                            ->rules(['numeric', 'min:0']),
                        Forms\Components\TextInput::make('paid_amount')
                            ->numeric()
                            ->label('Montant payé')
                            ->required()
                            ->suffix('FCFA')
                            ->rules(['numeric', 'min:0'])
                            ->rules([
                                function (Forms\Get $get) {
                                    return function (string $attribute, $value, $fail) use ($get) {
                                        $totalAmount = (float) $get('total_amount');
                                        if ((float) $value > $totalAmount) {
                                            $fail('Le montant payé ne peut pas être supérieur au montant total.');
                                        }
                                    };
                                },
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
                        'actif' => 'success',
                        'inactif' => 'danger',
                        'en_attente' => 'warning',
                        'archive' => 'secondary',
                        default => 'secondary',
                    })
                    ->label('Statut'),
                Tables\Columns\TextColumn::make('prospect.email')
                    ->label('Prospect'),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('XAF')
                    ->label('Montant total'),
                Tables\Columns\TextColumn::make('paid_amount')
                    ->money('XAF')
                    ->label('Montant payé'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Créé le'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('client_number')
                    ->label('Numéro Client'),
                Infolists\Components\TextEntry::make('full_name')
                    ->label('Nom Complet'),
                Infolists\Components\TextEntry::make('email'),
                Infolists\Components\TextEntry::make('phone')
                    ->label('Téléphone'),
                Infolists\Components\TextEntry::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'actif' => 'success',
                        'inactif' => 'danger',
                        'en_attente' => 'warning',
                        'archive' => 'secondary',
                        default => 'secondary',
                    })
                    ->label('Statut'),
                Infolists\Components\TextEntry::make('prospect.email')
                    ->label('Prospect'),
                Infolists\Components\TextEntry::make('total_amount')
                    ->money('XAF')
                    ->label('Montant total'),
                Infolists\Components\TextEntry::make('paid_amount')
                    ->money('XAF')
                    ->label('Montant payé'),
                Infolists\Components\TextEntry::make('created_at')
                    ->dateTime()
                    ->label('Créé le'),
            ]);
    }
}
