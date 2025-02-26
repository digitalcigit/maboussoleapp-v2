<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
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
                Forms\Components\Section::make('Informations du client')
                    ->schema([
                        Forms\Components\TextInput::make('client_number')
                            ->label('Numéro client')
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\TextInput::make('prospect.first_name')
                            ->label('Prénom')
                            ->required()
                            ->disabled(),

                        Forms\Components\TextInput::make('prospect.last_name')
                            ->label('Nom')
                            ->required()
                            ->disabled(),

                        Forms\Components\TextInput::make('prospect.email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->disabled(),

                        Forms\Components\TextInput::make('prospect.phone')
                            ->label('Téléphone')
                            ->tel()
                            ->disabled(),

                        Forms\Components\TextInput::make('prospect.city')
                            ->label('Ville')
                            ->disabled(),

                        Forms\Components\TextInput::make('prospect.country')
                            ->label('Pays')
                            ->disabled(),
                    ]),

                Forms\Components\Section::make('Documents')
                    ->schema([
                        Forms\Components\TextInput::make('passport_number')
                            ->label('Numéro de passeport')
                            ->required(),

                        Forms\Components\DatePicker::make('passport_expiry')
                            ->label('Date d\'expiration du passeport')
                            ->required(),

                        Forms\Components\Select::make('visa_status')
                            ->label('Statut du visa')
                            ->options([
                                Client::VISA_STATUS_NOT_STARTED => 'Non commencé',
                                Client::VISA_STATUS_IN_PROGRESS => 'En cours',
                                Client::VISA_STATUS_APPROVED => 'Approuvé',
                                Client::VISA_STATUS_REJECTED => 'Rejeté',
                            ])
                            ->required(),
                    ]),

                Forms\Components\Section::make('Paiement')
                    ->schema([
                        Forms\Components\TextInput::make('total_amount')
                            ->label('Montant total')
                            ->numeric()
                            ->required(),

                        Forms\Components\TextInput::make('paid_amount')
                            ->label('Montant payé')
                            ->numeric()
                            ->required(),

                        Forms\Components\Select::make('payment_status')
                            ->label('Statut du paiement')
                            ->options([
                                Client::PAYMENT_STATUS_PENDING => 'En attente',
                                Client::PAYMENT_STATUS_PARTIAL => 'Partiel',
                                Client::PAYMENT_STATUS_COMPLETE => 'Complet',
                            ])
                            ->required(),
                    ]),
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
                    ->label('Numéro client')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('prospect.first_name')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('prospect.last_name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('prospect.email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('prospect.phone')
                    ->label('Téléphone'),

                Tables\Columns\TextColumn::make('visa_status')
                    ->label('Statut visa')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Client::VISA_STATUS_APPROVED => 'success',
                        Client::VISA_STATUS_IN_PROGRESS => 'warning',
                        Client::VISA_STATUS_REJECTED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Client::VISA_STATUS_NOT_STARTED => 'Non commencé',
                        Client::VISA_STATUS_IN_PROGRESS => 'En cours',
                        Client::VISA_STATUS_APPROVED => 'Approuvé',
                        Client::VISA_STATUS_REJECTED => 'Rejeté',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Statut paiement')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Client::PAYMENT_STATUS_COMPLETE => 'success',
                        Client::PAYMENT_STATUS_PARTIAL => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Client::PAYMENT_STATUS_PENDING => 'En attente',
                        Client::PAYMENT_STATUS_PARTIAL => 'Partiel',
                        Client::PAYMENT_STATUS_COMPLETE => 'Complet',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('visa_status')
                    ->label('Statut visa')
                    ->options([
                        Client::VISA_STATUS_NOT_STARTED => 'Non commencé',
                        Client::VISA_STATUS_IN_PROGRESS => 'En cours',
                        Client::VISA_STATUS_APPROVED => 'Approuvé',
                        Client::VISA_STATUS_REJECTED => 'Rejeté',
                    ]),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Statut paiement')
                    ->options([
                        Client::PAYMENT_STATUS_PENDING => 'En attente',
                        Client::PAYMENT_STATUS_PARTIAL => 'Partiel',
                        Client::PAYMENT_STATUS_COMPLETE => 'Complet',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
