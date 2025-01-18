<?php

namespace App\Filament\Resources;

use App\Models\Dossier;
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
use Filament\Notifications\Notification;

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
                            ->default(fn () => 'DOS-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT))
                            ->disabled()
                            ->required(),
                            
                        Forms\Components\Select::make('prospect_id')
                            ->relationship(
                                'prospect',
                                titleAttribute: 'full_name',
                                modifyQueryUsing: fn (Builder $query) => $query
                                    ->whereDoesntHave('dossier')
                                    ->selectRaw('*, CONCAT(first_name, " ", last_name) as full_name')
                                    ->orderByRaw('CONCAT(first_name, " ", last_name)')
                            )
                            ->label('Prospect')
                            ->searchable(['first_name', 'last_name', 'email'])
                            ->preload()
                            ->required(),

                        Forms\Components\Grid::make(2)
                            ->schema([
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
                                    ->required()
                                    ->live(),

                                Forms\Components\Select::make('current_status')
                                    ->label('Statut actuel')
                                    ->options(fn (Forms\Get $get): array => 
                                        Dossier::getValidStatusesForStepWithLabels($get('current_step') ?? Dossier::STEP_ANALYSIS)
                                    )
                                    ->required()
                                    ->live()
                                    ->visible(fn (Forms\Get $get) => filled($get('current_step'))),
                            ]),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
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
                    ->options(fn () => collect(Dossier::getValidStatusesForStep(Dossier::STEP_ANALYSIS))
                        ->merge(Dossier::getValidStatusesForStep(Dossier::STEP_ADMISSION))
                        ->merge(Dossier::getValidStatusesForStep(Dossier::STEP_PAYMENT))
                        ->merge(Dossier::getValidStatusesForStep(Dossier::STEP_VISA))
                        ->mapWithKeys(fn ($status) => [$status => ucfirst(str_replace('_', ' ', $status))])),
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
