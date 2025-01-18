<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DossierResource\Pages;
use App\Models\Dossier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DossierResource extends Resource
{
    protected static ?string $model = Dossier::class;
    protected static ?string $navigationIcon = 'heroicon-o-folder';
    protected static ?string $navigationGroup = 'Gestion des Dossiers';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'reference_number';
    protected static ?string $navigationLabel = 'Dossiers';
    protected static ?string $modelLabel = 'Dossier';
    protected static ?string $pluralModelLabel = 'Dossiers';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('current_step', '<=', 4)->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations du dossier')
                    ->schema([
                        Forms\Components\TextInput::make('reference_number')
                            ->label('Numéro de référence')
                            ->default(fn () => 'DOS-' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT))
                            ->disabled()
                            ->required(),
                            
                        Forms\Components\Select::make('prospect_id')
                            ->relationship('prospect', 'reference_number')
                            ->label('Prospect')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('current_step')
                            ->label('Étape actuelle')
                            ->options([
                                Dossier::STEP_ANALYSIS => 'Analyse de dossier',
                                Dossier::STEP_ADMISSION => 'Ouverture & Admission',
                                Dossier::STEP_PAYMENT => 'Paiement',
                                Dossier::STEP_VISA => 'Accompagnement Visa',
                            ])
                            ->default(Dossier::STEP_ANALYSIS)
                            ->disabled(fn ($record) => $record !== null)
                            ->required(),

                        Forms\Components\Select::make('current_status')
                            ->label('Statut actuel')
                            ->options(fn (Forms\Get $get) => match ($get('current_step')) {
                                Dossier::STEP_ANALYSIS => [
                                    Dossier::STATUS_WAITING_DOCS => 'En attente de documents',
                                    Dossier::STATUS_ANALYZING => 'Analyse en cours',
                                    Dossier::STATUS_ANALYZED => 'Analyse terminée',
                                ],
                                Dossier::STEP_ADMISSION => [
                                    Dossier::STATUS_DOCS_RECEIVED => 'Documents physiques reçus',
                                    Dossier::STATUS_ADMISSION_PAID => 'Frais d\'admission payés',
                                    Dossier::STATUS_SUBMITTED => 'Dossier soumis',
                                    Dossier::STATUS_SUBMISSION_ACCEPTED => 'Soumission acceptée',
                                    Dossier::STATUS_SUBMISSION_REJECTED => 'Soumission rejetée',
                                ],
                                Dossier::STEP_PAYMENT => [
                                    Dossier::STATUS_AGENCY_PAID => 'Frais d\'agence payés',
                                    Dossier::STATUS_PARTIAL_TUITION => 'Paiement partiel scolarité',
                                    Dossier::STATUS_FULL_TUITION => 'Paiement total scolarité',
                                    Dossier::STATUS_ABANDONED => 'Dossier abandonné',
                                ],
                                Dossier::STEP_VISA => [
                                    Dossier::STATUS_VISA_DOCS_READY => 'Dossier visa prêt',
                                    Dossier::STATUS_VISA_FEES_PAID => 'Frais visa payés',
                                    Dossier::STATUS_VISA_SUBMITTED => 'Visa soumis',
                                    Dossier::STATUS_VISA_ACCEPTED => 'Visa obtenu',
                                    Dossier::STATUS_VISA_REJECTED => 'Visa refusé',
                                    Dossier::STATUS_FINAL_FEES_PAID => 'Frais finaux payés',
                                ],
                                default => [],
                            })
                            ->required()
                            ->live(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
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

                Tables\Columns\TextColumn::make('prospect.full_name')
                    ->label('Prospect')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('current_step')
                    ->label('Étape')
                    ->badge()
                    ->formatStateUsing(fn (Model $record) => match ($record->current_step) {
                        Dossier::STEP_ANALYSIS => 'Analyse',
                        Dossier::STEP_ADMISSION => 'Admission',
                        Dossier::STEP_PAYMENT => 'Paiement',
                        Dossier::STEP_VISA => 'Visa',
                        default => 'Inconnu',
                    })
                    ->color(fn (Model $record) => match ($record->current_step) {
                        Dossier::STEP_ANALYSIS => 'info',
                        Dossier::STEP_ADMISSION => 'warning',
                        Dossier::STEP_PAYMENT => 'success',
                        Dossier::STEP_VISA => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('current_status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (Model $record) => $record->currentStepHistory->first()?->getStatusLabel() ?? $record->current_status)
                    ->color(fn (Model $record) => match ($record->current_status) {
                        Dossier::STATUS_WAITING_DOCS, 
                        Dossier::STATUS_DOCS_RECEIVED => 'warning',
                        
                        Dossier::STATUS_ANALYZING,
                        Dossier::STATUS_ADMISSION_PAID,
                        Dossier::STATUS_SUBMITTED,
                        Dossier::STATUS_AGENCY_PAID,
                        Dossier::STATUS_PARTIAL_TUITION,
                        Dossier::STATUS_VISA_DOCS_READY,
                        Dossier::STATUS_VISA_FEES_PAID,
                        Dossier::STATUS_VISA_SUBMITTED => 'info',
                        
                        Dossier::STATUS_ANALYZED,
                        Dossier::STATUS_SUBMISSION_ACCEPTED,
                        Dossier::STATUS_FULL_TUITION,
                        Dossier::STATUS_VISA_ACCEPTED,
                        Dossier::STATUS_FINAL_FEES_PAID => 'success',
                        
                        Dossier::STATUS_SUBMISSION_REJECTED,
                        Dossier::STATUS_ABANDONED,
                        Dossier::STATUS_VISA_REJECTED => 'danger',
                        
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('last_action_at')
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
                    ->options(fn () => collect([
                        ...Dossier::getModel()::getValidStatusesForCurrentStep(),
                    ])->mapWithKeys(fn ($status) => [$status => ucfirst(str_replace('_', ' ', $status))])),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('last_action_at', 'desc');
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
            'view' => Pages\ViewDossier::route('/{record}'),
            'edit' => Pages\EditDossier::route('/{record}/edit'),
        ];
    }
}
