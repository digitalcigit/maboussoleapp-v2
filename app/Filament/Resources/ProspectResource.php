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

    protected static ?int $navigationGroupSort = 2;

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
                        Forms\Components\Select::make('current_status')
                            ->label('Statut')
                            ->options([
                                Prospect::STATUS_NOUVEAU => 'Nouveau',
                                Prospect::STATUS_QUALIFIE => 'Qualifié',
                                Prospect::STATUS_TRAITEMENT => 'En traitement',
                                Prospect::STATUS_BLOQUE => 'Bloqué',
                                Prospect::STATUS_CONVERTI => 'Converti',
                            ])
                            ->default(Prospect::STATUS_NOUVEAU)
                            ->required(),
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
        $query = parent::getEloquentQuery();
        
        if (auth()->user()->hasRole('prospect')) {
            $query->where('assigned_to', auth()->id());
        }
        
        return $query;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Référence')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nom complet')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['last_name', 'first_name']),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('desired_destination')
                    ->label('Destination')
                    ->searchable(),
                Tables\Columns\TextColumn::make('current_status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Prospect::STATUS_NOUVEAU => 'info',
                        Prospect::STATUS_QUALIFIE => 'success',
                        Prospect::STATUS_TRAITEMENT => 'warning',
                        Prospect::STATUS_BLOQUE => 'danger',
                        Prospect::STATUS_CONVERTI => 'primary',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Prospect::STATUS_NOUVEAU => 'Nouveau',
                        Prospect::STATUS_QUALIFIE => 'Qualifié',
                        Prospect::STATUS_TRAITEMENT => 'En traitement',
                        Prospect::STATUS_BLOQUE => 'Bloqué',
                        Prospect::STATUS_CONVERTI => 'Converti',
                        default => $state,
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('converted_to_dossier')
                    ->label('Converti en dossier')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn (Prospect $record): string => $record->converted_to_dossier 
                        ? "Converti le " . $record->converted_at->format('d/m/Y') . "\nDossier : " . $record->dossier_reference
                        : "Non converti"),
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Assigné à')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('current_status')
                    ->label('Statut')
                    ->options([
                        Prospect::STATUS_NOUVEAU => 'Nouveau',
                        Prospect::STATUS_QUALIFIE => 'Qualifié',
                        Prospect::STATUS_TRAITEMENT => 'En traitement',
                        Prospect::STATUS_BLOQUE => 'Bloqué',
                        Prospect::STATUS_CONVERTI => 'Converti',
                    ])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('assigned_to')
                    ->label('Assigné à')
                    ->relationship('assignedTo', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('desired_destination')
                    ->label('Destination')
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
                    ])
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListProspects::route('/'),
            'create' => Pages\CreateProspect::route('/create'),
            'edit' => Pages\EditProspect::route('/{record}/edit'),
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
        return 'Prospect';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Prospects';
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('current_status', '!=', Prospect::STATUS_CONVERTI)->count();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('viewAny', Prospect::class);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create', Prospect::class);
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasAnyRole(['super_admin', 'manager']) || 
               (auth()->user()->hasRole('conseiller') && $record->assigned_to === auth()->id());
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasRole('super_admin') || 
               (auth()->user()->hasRole('manager') && $record->created_by === auth()->id());
    }
}
