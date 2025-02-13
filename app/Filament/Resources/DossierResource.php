<?php

namespace App\Filament\Resources;

use App\Models\Dossier;
use App\Models\Prospect;
use App\Models\User;
use App\Filament\Resources\DossierResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use App\Services\ReferenceGeneratorService;

class DossierResource extends Resource
{
    protected static ?string $model = Dossier::class;
    protected static ?string $navigationIcon = 'heroicon-o-folder';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'reference_number';
    protected static ?string $navigationLabel = 'Dossiers';
    protected static ?string $modelLabel = 'Dossier';
    protected static ?string $pluralModelLabel = 'Dossiers';
    protected static ?string $navigationGroup = 'Gestion des dossiers';
    protected static ?int $navigationGroupSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('current_step', '<=', 4)->count();
    }

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        
        return $form
            ->schema([
                Forms\Components\Section::make('Informations du dossier')
                    ->schema([
                        Forms\Components\TextInput::make('reference_number')
                            ->label('Numéro de référence')
                            ->default(function () {
                                $generator = app(ReferenceGeneratorService::class);
                                return $generator->generateReference('dossier');
                            })
                            ->required()
                            ->maxLength(255)
                            ->unique(ignorable: fn ($record) => $record)
                            ->disabled(),

                        Forms\Components\Select::make('prospect_id')
                            ->label('Prospect')
                            ->relationship('prospect', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                            ->searchable(['first_name', 'last_name', 'email'])
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if (empty($state)) {
                                    $set('prospect_info', null);
                                    return;
                                }

                                $prospect = Prospect::find($state);
                                if (!$prospect) return;

                                // Informations de base
                                $set('prospect_info.first_name', $prospect->first_name);
                                $set('prospect_info.last_name', $prospect->last_name);
                                $set('prospect_info.email', $prospect->email);
                                $set('prospect_info.phone', $prospect->phone);
                                $set('prospect_info.birth_date', $prospect->birth_date?->format('Y-m-d'));
                                $set('prospect_info.profession', $prospect->profession);
                                $set('prospect_info.education_level', $prospect->education_level);
                                $set('prospect_info.desired_field', $prospect->desired_field);
                                $set('prospect_info.desired_destination', $prospect->desired_destination);

                                // Contact d'urgence
                                if ($prospect->emergency_contact) {
                                    $set('prospect_info.emergency_contact', $prospect->emergency_contact);
                                }

                                // Documents
                                if (!empty($prospect->documents)) {
                                    $formattedDocuments = collect($prospect->documents)->map(function ($doc) {
                                        return [
                                            'type' => $doc['type'] ?? 'autre',
                                            'description' => $doc['description'] ?? '',
                                            'file' => $doc['file'] ?? null
                                        ];
                                    })->toArray();
                                    
                                    $set('prospect_info.documents', $formattedDocuments);
                                }
                            }),

                        Forms\Components\Select::make('assigned_to')
                            ->label('Assigné à')
                            ->relationship('assignedTo', 'name')
                            ->options(function () {
                                // Si super-admin, montrer tous les utilisateurs
                                if (auth()->user()->hasRole('super-admin')) {
                                    return User::all()->pluck('name', 'id');
                                }
                                
                                // Si manager, montrer les conseillers et soi-même
                                if (auth()->user()->hasRole('manager')) {
                                    return User::role(['conseiller', 'manager'])
                                        ->where(function ($query) {
                                            $query->role('conseiller')
                                                ->orWhere('id', auth()->id());
                                        })
                                        ->pluck('name', 'id');
                                }
                                
                                // Pour les conseillers, uniquement eux-mêmes
                                return User::where('id', auth()->id())->pluck('name', 'id');
                            })
                            ->default(fn () => auth()->id())
                            ->required()
                            ->visible(fn () => auth()->user()->can('assign', Dossier::class))
                            ->disabled(fn (string $operation, ?Model $record) => 
                                $operation === 'edit' && 
                                !auth()->user()->can('reassign', $record ?? Dossier::class)
                            ),

                        Forms\Components\Select::make('current_step')
                            ->label('Étape actuelle')
                            ->options([
                                Dossier::STEP_ANALYSIS => 'Analyse de dossier',
                                Dossier::STEP_ADMISSION => 'Admission',
                                Dossier::STEP_PAYMENT => 'Paiement',
                                Dossier::STEP_VISA => 'Visa',
                            ])
                            ->default(Dossier::STEP_ANALYSIS)
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                $set('current_status', Dossier::getValidStatusesForStep($state)[0] ?? null);
                            })
                            ->required(),

                        Forms\Components\Select::make('current_status')
                            ->label('Statut actuel')
                            ->options(function (Forms\Get $get) {
                                $step = $get('current_step');
                                if (!$step) return [];
                                
                                return collect(Dossier::getValidStatusesForStep($step))
                                    ->mapWithKeys(fn ($status) => [
                                        $status => Dossier::getStatusLabel($status)
                                    ])
                                    ->toArray();
                            })
                            ->required()
                            ->live(),

                        Forms\Components\TextInput::make('agency_payment_amount')
                            ->label('Montant des frais d\'agence')
                            ->numeric()
                            ->prefix('FCFA')
                            ->step(1)
                            ->inputMode('numeric')
                            ->minValue(0)
                            ->maxValue(999999999)
                            ->visible(fn (Forms\Get $get): bool => 
                                $get('current_status') === Dossier::STATUS_AGENCY_PAID
                            )
                            ->required(fn (Forms\Get $get): bool => 
                                $get('current_status') === Dossier::STATUS_AGENCY_PAID
                            ),
                    ]),

                Forms\Components\Section::make('Paiement')
                    ->schema([
                        Forms\Components\TextInput::make('tuition_total_amount')
                            ->label('Montant total de la scolarité')
                            ->numeric()
                            ->prefix('FCFA')
                            ->step(1)
                            ->inputMode('numeric')
                            ->helperText('Montant indicatif de la scolarité totale')
                            ->visible(fn (Forms\Get $get): bool => 
                                $get('current_status') === Dossier::STATUS_TUITION_PAYMENT
                            )
                            ->required(fn (Forms\Get $get): bool => 
                                $get('current_status') === Dossier::STATUS_TUITION_PAYMENT
                            )
                            ->afterStateHydrated(function ($state, Forms\Set $set) {
                                $set('tuition_total_amount', (int)$state);
                            }),

                        Forms\Components\TextInput::make('down_payment_amount')
                            ->label('Montant de l\'accompte')
                            ->numeric()
                            ->prefix('FCFA')
                            ->step(1)
                            ->inputMode('numeric')
                            ->required()
                            ->rules(['required', 'integer', 'min:0'])
                            ->helperText('Montant de l\'accompte à payer pour valider l\'inscription')
                            ->visible(fn (Forms\Get $get): bool => 
                                $get('current_status') === Dossier::STATUS_TUITION_PAYMENT
                            )
                            ->afterStateHydrated(function ($state, Forms\Set $set) {
                                $set('down_payment_amount', (int)$state);
                            }),

                        Forms\Components\TextInput::make('tuition_paid_amount')
                            ->label('Montant payé')
                            ->numeric()
                            ->prefix('FCFA')
                            ->step(1)
                            ->inputMode('numeric')
                            ->visible(fn (Forms\Get $get): bool => 
                                $get('current_status') === Dossier::STATUS_TUITION_PAYMENT
                            )
                            ->afterStateHydrated(function ($state, Forms\Set $set) {
                                $set('tuition_paid_amount', (int)$state);
                            }),

                        Forms\Components\Placeholder::make('payment_progress')
                            ->label('Progression du paiement')
                            ->content(function ($record) {
                                if (!$record || !$record->down_payment_amount || !$record->tuition_paid_amount) {
                                    return '0%';
                                }
                                $progress = min(100, round(($record->tuition_paid_amount / $record->down_payment_amount) * 100));
                                return "{$progress}%";
                            })
                            ->visible(fn (Forms\Get $get): bool => 
                                $get('current_status') === Dossier::STATUS_TUITION_PAYMENT
                            )
                    ])
                    ->columns(1)
                    ->visible(fn (Forms\Get $get) => $get('current_status') === Dossier::STATUS_TUITION_PAYMENT),

                Forms\Components\Section::make('Informations du Prospect')
                    ->schema([
                        Forms\Components\TextInput::make('prospect_info.first_name')
                            ->label('Prénom')
                            ->required(),

                        Forms\Components\TextInput::make('prospect_info.last_name')
                            ->label('Nom')
                            ->required(),

                        Forms\Components\TextInput::make('prospect_info.email')
                            ->label('Email')
                            ->email()
                            ->required(),

                        Forms\Components\TextInput::make('prospect_info.phone')
                            ->label('Téléphone')
                            ->tel(),

                        Forms\Components\DatePicker::make('prospect_info.birth_date')
                            ->label('Date de naissance'),

                        Forms\Components\TextInput::make('prospect_info.profession')
                            ->label('Profession actuelle'),

                        Forms\Components\Select::make('prospect_info.education_level')
                            ->label('Niveau d\'études')
                            ->options([
                                'Baccalauréat' => 'Baccalauréat',
                                'Bac+2 (DUT, BTS)' => 'Bac+2 (DUT, BTS)',
                                'Bac+3 (Licence)' => 'Bac+3 (Licence)',
                                'Bac+4 (Master 1)' => 'Bac+4 (Master 1)',
                                'Bac+5 (Master 2)' => 'Bac+5 (Master 2)',
                                'Bac+8 (Doctorat)' => 'Bac+8 (Doctorat)',
                            ]),

                        Forms\Components\TextInput::make('prospect_info.desired_field')
                            ->label('Filière souhaitée'),

                        Forms\Components\Select::make('prospect_info.desired_destination')
                            ->label('Destination souhaitée')
                            ->options([
                                'france' => 'France',
                                'canada' => 'Canada',
                                'belgique' => 'Belgique',
                                'suisse' => 'Suisse',
                                'allemagne' => 'Allemagne',
                                'espagne' => 'Espagne',
                                'italie' => 'Italie',
                                'royaume_uni' => 'Royaume-Uni',
                                'etats_unis' => 'États-Unis',
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
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Contact d\'urgence')
                    ->schema([
                        Forms\Components\TextInput::make('prospect_info.emergency_contact.name')
                            ->label('Nom du contact'),

                        Forms\Components\TextInput::make('prospect_info.emergency_contact.relation')
                            ->label('Relation'),

                        Forms\Components\TextInput::make('prospect_info.emergency_contact.phone')
                            ->label('Téléphone')
                            ->tel(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Documents fournis')
                    ->schema([
                        Forms\Components\Repeater::make('prospect_info.documents')
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
                                    ->directory('dossiers/documents')
                                    ->visibility('public')
                                    ->preserveFilenames()
                                    ->maxSize(5120)
                                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                                    ->downloadable()
                                    ->openable()
                                    ->previewable()
                                    ->columnSpanFull()
                                    ->getUploadedFileNameForStorageUsing(
                                        fn (TemporaryUploadedFile $file): string => (string) str($file->getClientOriginalName())
                                            ->prepend(now()->timestamp . '_'),
                                    )
                            ])
                            ->defaultItems(0)
                            ->columnSpanFull()
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 
                                $state['type'] ? ucfirst($state['type']) . (isset($state['description']) ? " - {$state['description']}" : '') : null
                            )
                            ->deleteAction(
                                fn (Forms\Components\Actions\Action $action) => $action
                                    ->requiresConfirmation()
                                    ->modalHeading('Supprimer le document')
                                    ->modalDescription('Êtes-vous sûr de vouloir supprimer ce document ? Cette action est irréversible.')
                                    ->modalSubmitActionLabel('Oui, supprimer')
                                    ->modalCancelActionLabel('Annuler')
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
                                            $document['file'] = $document['file']->store('dossiers/documents', 'public');
                                        } elseif (is_string($document['file']) && !str_starts_with($document['file'], 'dossiers/documents/')) {
                                            $document['file'] = 'dossiers/documents/' . basename($document['file']);
                                        }
                                    }
                                    return $document;
                                })->all();
                            })
                    ])
                    ->columnSpanFull(),

                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3),
                    ]),
                Forms\Components\Hidden::make('created_by')
                    ->default(auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_number')
                    ->label('Référence')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('prospect.full_name')
                    ->label('Prospect')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                TextColumn::make('current_step')
                    ->label('Étape')
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        Dossier::STEP_ANALYSIS => 'gray',
                        Dossier::STEP_ADMISSION => 'warning',
                        Dossier::STEP_PAYMENT => 'success',
                        Dossier::STEP_VISA => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (int $state): string => Dossier::getStepLabel($state))
                    ->sortable(),
                TextColumn::make('current_status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Dossier::STATUS_ANALYZED => 'success',
                        Dossier::STATUS_SUBMISSION_ACCEPTED => 'success',
                        Dossier::STATUS_FULL_TUITION => 'success',
                        Dossier::STATUS_VISA_ACCEPTED => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => Dossier::getStatusLabel($state))
                    ->sortable(),
                TextColumn::make('last_action_at')
                    ->label('Dernière action')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('current_step')
                    ->label('Étape')
                    ->options([
                        Dossier::STEP_ANALYSIS => 'Analyse de dossier',
                        Dossier::STEP_ADMISSION => 'Ouverture & Admission',
                        Dossier::STEP_PAYMENT => 'Paiement',
                        Dossier::STEP_VISA => 'Accompagnement Visa',
                    ]),
                    
                Tables\Filters\SelectFilter::make('current_status')
                    ->label('Statut')
                    ->options(fn () => [
                        Dossier::STATUS_WAITING_DOCS => 'En attente de documents',
                        Dossier::STATUS_ANALYZING => 'Analyse en cours',
                        Dossier::STATUS_ANALYZED => 'Analyse terminée',
                        // ... autres statuts selon l'étape
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('continuer')
                    ->label('Continuer')
                    ->icon('heroicon-o-arrow-right')
                    ->color('success')
                    ->visible(fn (Dossier $record): bool => $record->canProgressToNextStep())
                    ->requiresConfirmation()
                    ->modalHeading(fn (Dossier $record): string => "Passer à l'étape suivante ?")
                    ->modalDescription(fn (Dossier $record): string => match($record->current_step) {
                        Dossier::STEP_ANALYSIS => "Le dossier passera à l'étape 'Ouverture & Admission'. Cette action est irréversible.",
                        Dossier::STEP_ADMISSION => "Le dossier passera à l'étape 'Paiement'. Cette action est irréversible.",
                        Dossier::STEP_PAYMENT => "Le dossier passera à l'étape 'Accompagnement Visa'. Cette action est irréversible.",
                        default => "Êtes-vous sûr de vouloir continuer ?",
                    })
                    ->modalSubmitActionLabel("Oui, continuer")
                    ->modalCancelActionLabel("Annuler")
                    ->action(function (Dossier $record): void {
                        if ($record->progressToNextStep()) {
                            Notification::make()
                                ->title('Étape mise à jour')
                                ->success()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Supprimer')
                        ->modalHeading('Supprimer les dossiers sélectionnés')
                        ->modalDescription('Êtes-vous sûr de vouloir supprimer ces dossiers ? Cette action est irréversible.')
                        ->modalSubmitActionLabel('Oui, supprimer')
                        ->modalCancelActionLabel('Annuler'),
                        
                    Tables\Actions\BulkAction::make('updateStatus')
                        ->label('Mettre à jour le statut')
                        ->icon('heroicon-o-arrow-path')
                        ->action(function (Collection $records, array $data): void {
                            $records->each(function ($record) use ($data) {
                                $record->update([
                                    'current_status' => $data['status'],
                                    'last_action_at' => now(),
                                ]);
                            });
                        })
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('Nouveau statut')
                                ->options(fn () => [
                                    Dossier::STATUS_WAITING_DOCS => 'En attente de documents',
                                    Dossier::STATUS_ANALYZING => 'Analyse en cours',
                                    Dossier::STATUS_ANALYZED => 'Analyse terminée',
                                    // ... autres statuts selon l'étape
                                ])
                                ->required(),
                        ]),
                ]),
            ])
            ->defaultSort('last_action_at', 'desc');
    }

    protected static function getStatusOptions(): array
    {
        return [
            // Étape 1 - Analyse
            'attente_documents' => 'En attente de documents',
            'analyse_en_cours' => 'Analyse en cours',
            'analyse_terminee' => 'Analyse terminée',

            // Étape 2 - Admission
            'reception_documents_physiques' => 'Documents physiques reçus',
            'paiement_frais_admission' => 'Frais d\'admission payés',
            'dossier_soumis' => 'Dossier soumis',
            'soumission_acceptee' => 'Soumission acceptée',
            'soumission_rejetee' => 'Soumission rejetée',

            // Étape 3 - Paiement
            'paiement_frais_agence' => 'Frais d\'agence payés',
            'paiement_scolarite_partiel' => 'Scolarité partiellement payée',
            'paiement_scolarite_total' => 'Scolarité totalement payée',
            'abandonne' => 'Abandonné',

            // Étape 4 - Visa
            'dossier_visa_pret' => 'Dossier visa prêt',
            'frais_visa_payes' => 'Frais de visa payés',
            'visa_soumis' => 'Visa soumis',
            'visa_obtenu' => 'Visa obtenu',
            'visa_refuse' => 'Visa refusé',
            'frais_finaux_payes' => 'Frais finaux payés'
        ];
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
            'index' => Pages\ListDossiers::route('/'),
            'create' => Pages\CreateDossier::route('/create'),
            'edit' => Pages\EditDossier::route('/{record}/edit'),
        ];
    }
}
