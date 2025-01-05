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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;

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
                    ->description('Informations de base du prospect')
                    ->schema([
                        Forms\Components\TextInput::make('reference_number')
                            ->label('Reference number')
                            ->default('PROS-' . random_int(10000, 99999))
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        Forms\Components\TextInput::make('first_name')
                            ->label('Prénom')
                            ->required(),
                        Forms\Components\TextInput::make('last_name')
                            ->label('Nom')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required(),
                        Forms\Components\TextInput::make('phone')
                            ->label('Téléphone')
                            ->tel()
                            ->required(),
                        Forms\Components\DatePicker::make('birth_date')
                            ->label('Date de naissance')
                            ->required(),
                        Forms\Components\TextInput::make('profession')
                            ->label('Profession actuelle'),
                        Forms\Components\Select::make('education_level')
                            ->label('Niveau d\'études')
                            ->options([
                                'bac' => 'Baccalauréat',
                                'bac+2' => 'Bac+2 (DUT, BTS)',
                                'bac+3' => 'Bac+3 (Licence)',
                                'bac+4' => 'Bac+4 (Master 1)',
                                'bac+5' => 'Bac+5 (Master 2)',
                                'bac+8' => 'Bac+8 (Doctorat)',
                            ])
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Situation Professionnelle')
                    ->description('Informations sur la situation actuelle et les objectifs')
                    ->schema([
                        Forms\Components\TextInput::make('current_location')
                            ->label('Localisation actuelle')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('current_field')
                            ->label('Domaine actuel')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('desired_field')
                            ->label('Domaine souhaité')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('desired_destination')
                            ->label('Destination souhaitée')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Contact d\'urgence')
                    ->schema([
                        Forms\Components\TextInput::make('emergency_contact.name')
                            ->label('Nom du contact')
                            ->required(),
                        Forms\Components\TextInput::make('emergency_contact.relationship')
                            ->label('Relation')
                            ->required(),
                        Forms\Components\TextInput::make('emergency_contact.phone')
                            ->label('Téléphone')
                            ->tel()
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Suivi Commercial')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                Prospect::STATUS_NEW => 'Nouveau',
                                Prospect::STATUS_ANALYZING => 'En analyse',
                                Prospect::STATUS_APPROVED => 'Approuvé',
                                Prospect::STATUS_REJECTED => 'Refusé',
                                Prospect::STATUS_CONVERTED => 'Converti',
                            ])
                            ->required()
                            ->default(Prospect::STATUS_NEW)
                            ->label('Statut')
                            ->native(false)
                            ->selectablePlaceholder(false),
                        Forms\Components\Select::make('assigned_to')
                            ->relationship('assignedTo', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Assigné à'),
                        Forms\Components\Select::make('partner_id')
                            ->relationship('partner', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Partenaire'),
                        Forms\Components\TextInput::make('commercial_code')
                            ->label('Code commercial')
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('analysis_deadline')
                            ->label('Date limite d\'analyse')
                            ->native(false),
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Référence')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'secondary' => Prospect::STATUS_NEW,
                        'warning' => Prospect::STATUS_ANALYZING,
                        'primary' => Prospect::STATUS_APPROVED,
                        'danger' => Prospect::STATUS_REJECTED,
                        'success' => Prospect::STATUS_CONVERTED,
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Prospect::STATUS_NEW => 'Nouveau',
                        Prospect::STATUS_ANALYZING => 'En analyse',
                        Prospect::STATUS_APPROVED => 'Approuvé',
                        Prospect::STATUS_REJECTED => 'Refusé',
                        Prospect::STATUS_CONVERTED => 'Converti',
                        default => $state,
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Assigné à')
                    ->searchable(),
                Tables\Columns\TextColumn::make('partner.name')
                    ->label('Partenaire')
                    ->searchable(),
                Tables\Columns\TextColumn::make('analysis_deadline')
                    ->label('Date limite')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        Prospect::STATUS_NEW => 'Nouveau',
                        Prospect::STATUS_ANALYZING => 'En analyse',
                        Prospect::STATUS_APPROVED => 'Approuvé',
                        Prospect::STATUS_REJECTED => 'Refusé',
                        Prospect::STATUS_CONVERTED => 'Converti',
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
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('convert_to_client')
                        ->label('Convertir en client')
                        ->icon('heroicon-o-user-plus')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Prospect $record) {
                            if ($record->status === Prospect::STATUS_CONVERTED) {
                                Notification::make()
                                    ->warning()
                                    ->title('Ce prospect est déjà converti en client')
                                    ->send();
                                return;
                            }

                            // Créer le client
                            $client = new Client();
                            $client->first_name = $record->first_name;
                            $client->last_name = $record->last_name;
                            $client->email = $record->email;
                            $client->phone = $record->phone;
                            $client->birth_date = $record->birth_date;
                            $client->education_level = $record->education_level;
                            $client->assigned_to = $record->assigned_to;
                            $client->save();

                            // Mettre à jour le statut du prospect
                            $record->status = Prospect::STATUS_CONVERTED;
                            $record->save();

                            Notification::make()
                                ->success()
                                ->title('Prospect converti en client avec succès')
                                ->send();

                            // Rediriger vers la page du client
                            return redirect()->route('filament.admin.resources.clients.edit', ['record' => $client->id]);
                        })
                        ->visible(fn (Prospect $record): bool => 
                            $record->status !== Prospect::STATUS_CONVERTED && 
                            auth()->user()->can('clients.create')
                        ),
                ])
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
                                    Prospect::STATUS_NEW => 'Nouveau',
                                    Prospect::STATUS_ANALYZING => 'En analyse',
                                    Prospect::STATUS_APPROVED => 'Approuvé',
                                    Prospect::STATUS_REJECTED => 'Refusé',
                                    Prospect::STATUS_CONVERTED => 'Converti',
                                ])
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(fn ($record) => $record->update(['status' => $data['status']]));
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->persistSortInSession();
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
