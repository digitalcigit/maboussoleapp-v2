<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProspectResource\Pages;
use App\Filament\Resources\ProspectResource\RelationManagers;
use App\Models\Client;
use App\Models\Prospect;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Facades\Storage;

class ProspectResource extends Resource
{
    protected static ?string $model = Prospect::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        
        return $form
            ->schema([
                Forms\Components\Section::make('Informations Personnelles')
                    ->description('Informations de base du prospect')
                    ->schema([
                        Forms\Components\TextInput::make('reference_number')
                            ->label('Numéro dossier')
                            ->default('PROS-' . random_int(10000, 99999))
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('last_name')
                                    ->label('Nom')
                                    ->required(),
                                Forms\Components\TextInput::make('first_name')
                                    ->label('Prénom')
                                    ->required(),
                            ]),
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
                        Forms\Components\TextInput::make('desired_field')
                            ->label('Filière souhaitée')
                            ->maxLength(255),
                        Forms\Components\Select::make('desired_destination')
                            ->label('Destination souhaitée')
                            ->options([
                                'france' => 'France',
                                'canada' => 'Canada',
                                'belgique' => 'Belgique',
                                'suisse' => 'Suisse',
                                'luxembourg' => 'Luxembourg',
                                'maroc' => 'Maroc',
                                'tunisie' => 'Tunisie',
                                'senegal' => 'Sénégal',
                                'cote_ivoire' => 'Côte d\'Ivoire',
                                'cameroun' => 'Cameroun',
                                'gabon' => 'Gabon',
                                'congo' => 'Congo',
                                'mali' => 'Mali',
                                'burkina_faso' => 'Burkina Faso',
                                'benin' => 'Bénin',
                                'togo' => 'Togo',
                                'guinee' => 'Guinée',
                                'niger' => 'Niger',
                                'tchad' => 'Tchad',
                                'madagascar' => 'Madagascar',
                            ])
                            ->searchable()
                            ->native(false)
                            ->preload(),
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
                            ->label('Statut')
                            ->options([
                                Prospect::STATUS_WAITING_DOCS => 'En attente de documents',
                                Prospect::STATUS_ANALYZING => 'Analyse en cours',
                                Prospect::STATUS_ANALYZED => 'Analyse terminée',
                            ])
                            ->native(false)
                            ->searchable()
                            ->required()
                            ->default(Prospect::STATUS_WAITING_DOCS)
                            ->disabled() // On désactive la modification lors de la création
                            ->dehydrated(), // On s'assure que la valeur est bien envoyée
                        Forms\Components\Select::make('assigned_to')
                            ->relationship('assignedTo', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Assigné à')
                            ->visible(fn () => $user->can('assign', Prospect::class))
                            ->default(fn () => $user->can('assign', Prospect::class) ? null : $user->id)
                            ->disabled(fn () => !$user->can('assign', Prospect::class))
                            ->required(),
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
                            ->disabled()
                            ->helperText('Automatiquement fixée à 5 jours ouvrés après la création')
                            ->native(false),
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Section::make('Documents fournis')
                            ->schema([
                                Forms\Components\Repeater::make('documents')
                                    ->label(false)
                                    ->schema([
                                        Forms\Components\Grid::make()
                                            ->schema([
                                                Forms\Components\Select::make('type')
                                                    ->label('Type de document')
                                                    ->required()
                                                    ->options([
                                                        'diploma' => 'Diplôme',
                                                        'id_card' => 'Pièce d\'identité',
                                                        'cv' => 'CV',
                                                        'motivation' => 'Lettre de motivation',
                                                        'passport' => 'Passeport',
                                                        'birth_certificate' => 'Acte de naissance',
                                                        'other' => 'Autre'
                                                    ])
                                                    ->columnSpan(1),
                                                Forms\Components\TextInput::make('description')
                                                    ->label('Description')
                                                    ->placeholder('Ex: Diplôme de licence en informatique')
                                                    ->columnSpan(1),
                                            ])->columns(2),
                                        Forms\Components\FileUpload::make('file')
                                            ->label('Fichier')
                                            ->required()
                                            ->disk('public')
                                            ->directory('prospects/documents')
                                            ->visibility('public')
                                            ->preserveFilenames()
                                            ->maxSize(5120)
                                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                                            ->downloadable()
                                            ->openable()
                                            ->previewable()
                                            ->columnSpanFull()
                                            ->getUploadedFileNameForStorageUsing(
                                                fn (TemporaryUploadedFile $file): string => (string) Str::of($file->getClientOriginalName())
                                                    ->prepend(now()->timestamp . '_'),
                                            )
                                    ])
                                    ->defaultItems(0)
                                    ->columnSpanFull()
                                    ->reorderableWithButtons()
                                    ->collapsible()
                                    ->collapsed(false)
                                    ->itemLabel(fn (array $state): ?string => 
                                        isset($state['type']) ? 
                                            collect([
                                                'diploma' => 'Diplôme',
                                                'id_card' => 'Pièce d\'identité',
                                                'cv' => 'CV',
                                                'motivation' => 'Lettre de motivation',
                                                'passport' => 'Passeport',
                                                'birth_certificate' => 'Acte de naissance',
                                                'other' => 'Autre'
                                            ])->get($state['type']) . 
                                            (isset($state['description']) ? " - {$state['description']}" : '') : null
                                    )
                                    ->afterStateHydrated(function (Forms\Components\Repeater $component, $state) {
                                        if (empty($state)) {
                                            return;
                                        }
                                        $documents = is_string($state) ? json_decode($state, true) : $state;
                                        if (!is_array($documents)) {
                                            return;
                                        }
                                        $component->state($documents);
                                    })
                                    ->beforeStateDehydrated(function (Forms\Components\Repeater $component, $state) {
                                        if (empty($state)) {
                                            return [];
                                        }
                                        return collect($state)->map(function ($document) {
                                            if (isset($document['file'])) {
                                                if ($document['file'] instanceof TemporaryUploadedFile) {
                                                    $document['file'] = $document['file']->store('prospects/documents', 'public');
                                                } elseif (is_string($document['file']) && !str_starts_with($document['file'], 'prospects/documents/')) {
                                                    $document['file'] = 'prospects/documents/' . basename($document['file']);
                                                }
                                            }
                                            return $document;
                                        })->all();
                                    })
                            ])
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
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
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Prospect::STATUS_WAITING_DOCS => 'En attente de documents',
                        Prospect::STATUS_ANALYZING => 'Analyse en cours',
                        Prospect::STATUS_ANALYZED => 'Analyse terminée',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        Prospect::STATUS_WAITING_DOCS => 'warning',
                        Prospect::STATUS_ANALYZING => 'primary',
                        Prospect::STATUS_ANALYZED => 'success',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        Prospect::STATUS_WAITING_DOCS => 'heroicon-o-clock',
                        Prospect::STATUS_ANALYZING => 'heroicon-o-document-magnifying-glass',
                        Prospect::STATUS_ANALYZED => 'heroicon-o-check-circle',
                        default => '',
                    }),
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
                        Prospect::STATUS_WAITING_DOCS => 'En attente de documents',
                        Prospect::STATUS_ANALYZING => 'Analyse en cours',
                        Prospect::STATUS_ANALYZED => 'Analyse terminée',
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
                        ->modalHeading('Conversion en client')
                        ->modalDescription('Voulez-vous vraiment convertir ce prospect en client ? Cette action est irréversible.')
                        ->modalSubmitActionLabel('Oui, convertir')
                        ->action(function (Prospect $record) {
                            if ($record->status !== Prospect::STATUS_ANALYZED) {
                                FilamentNotification::make()
                                    ->warning()
                                    ->title('Le prospect doit être analysé avant la conversion')
                                    ->send();
                                return;
                            }

                            // Créer le client
                            $client = new Client();
                            $client->client_number = 'CLI-' . random_int(10000, 99999);
                            $client->first_name = $record->first_name;
                            $client->last_name = $record->last_name;
                            $client->email = $record->email;
                            $client->phone = $record->phone;
                            $client->birth_date = $record->birth_date;
                            $client->education_level = $record->education_level;
                            $client->assigned_to = $record->assigned_to;
                            $client->prospect_id = $record->id;
                            $client->status = 'actif';
                            $client->save();

                            FilamentNotification::make()
                                ->success()
                                ->title('Prospect converti en client avec succès')
                                ->send();

                            // Rediriger vers la page du client
                            return redirect()->route('filament.admin.resources.clients.edit', ['record' => $client->id]);
                        })
                        ->visible(fn (Prospect $record): bool => 
                            $record->status === Prospect::STATUS_ANALYZED && 
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
                                    Prospect::STATUS_WAITING_DOCS => 'En attente de documents',
                                    Prospect::STATUS_ANALYZING => 'Analyse en cours',
                                    Prospect::STATUS_ANALYZED => 'Analyse terminée',
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
            // Retrait de la relation ActivitiesRelationManager
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
