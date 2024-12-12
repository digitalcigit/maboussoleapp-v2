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
                                'contacté' => 'Contacté',
                                'qualifié' => 'Qualifié',
                                'non_qualifié' => 'Non Qualifié',
                                'converti' => 'Converti',
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
                        'new' => 'gray',
                        'analyzing' => 'warning',
                        'qualified' => 'success',
                        'converted' => 'info',
                        'rejected' => 'danger',
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
                        'new' => 'Nouveau',
                        'analyzing' => 'En analyse',
                        'qualified' => 'Qualifié',
                        'converted' => 'Converti',
                        'rejected' => 'Rejeté',
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
                    ->url(fn ($record) => route('filament.admin.resources.prospects.convert', $record))
                    ->visible(fn ($record) => $record->status !== 'converted'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
}
