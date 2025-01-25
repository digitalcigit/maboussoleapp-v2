<?php

namespace App\Filament\PortailCandidat\Resources;

use App\Filament\PortailCandidat\Resources\DossierResource\Pages;
use App\Models\Dossier;
use App\Models\DossierDocument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class DossierResource extends Resource
{
    protected static ?string $model = Dossier::class;
    protected static ?string $navigationIcon = 'heroicon-o-folder';
    protected static ?string $navigationLabel = 'Mon Dossier';
    protected static ?string $modelLabel = 'Dossier';
    protected static ?string $slug = 'mon-dossier';
    protected static ?string $policy = \App\Policies\PortailCandidat\DossierPolicy::class;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('portail-candidat.dossier.viewAny');
    }

    public static function canViewAny(): bool
    {
        file_put_contents(
            storage_path('logs/debug.log'),
            "canViewAny called\n",
            FILE_APPEND
        );
        $user = auth()->user();
        Log::debug('DossierResource::canViewAny check', [
            'user_id' => $user->id,
            'roles' => $user->roles->pluck('name'),
            'can_view_any' => $user->can('portail-candidat.dossier.viewAny')
        ]);
        return $user->can('portail-candidat.dossier.viewAny');
    }

    public static function canView(Model $record): bool
    {
        file_put_contents(
            storage_path('logs/debug.log'),
            "canView called for dossier {$record->id}\n",
            FILE_APPEND
        );
        $user = auth()->user();
        Log::debug('DossierResource::canView check', [
            'user_id' => $user->id,
            'roles' => $user->roles->pluck('name'),
            'dossier_id' => $record->id,
            'can_view' => $user->can('portail-candidat.dossier.view', $record)
        ]);
        return $user->can('portail-candidat.dossier.view', $record);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('portail-candidat.dossier.create');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->can('portail-candidat.dossier.update', $record);
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->can('portail-candidat.dossier.delete', $record);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informations de mon dossier')
                ->description('Consultez et mettez à jour les informations de votre dossier')
                ->schema([
                    Forms\Components\TextInput::make('reference_number')
                        ->label('Numéro de référence')
                        ->disabled()
                        ->dehydrated(false),

                    Forms\Components\Section::make('Informations personnelles')
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
                                ->tel()
                                ->required(),
                            Forms\Components\DatePicker::make('prospect_info.birth_date')
                                ->label('Date de naissance')
                                ->required(),
                            Forms\Components\TextInput::make('prospect_info.profession')
                                ->label('Profession')
                                ->required(),
                            Forms\Components\Select::make('prospect_info.education_level')
                                ->label('Niveau d\'études')
                                ->options([
                                    'Baccalauréat' => 'Baccalauréat',
                                    'Bac+2 (DUT, BTS)' => 'Bac+2 (DUT, BTS)',
                                    'Bac+3 (Licence)' => 'Bac+3 (Licence)',
                                    'Bac+4 (Master 1)' => 'Bac+4 (Master 1)',
                                    'Bac+5 (Master 2)' => 'Bac+5 (Master 2)',
                                    'Bac+8 (Doctorat)' => 'Bac+8 (Doctorat)',
                                ])
                                ->required(),
                        ]),

                    Forms\Components\Section::make('Contact d\'urgence')
                        ->schema([
                            Forms\Components\TextInput::make('prospect_info.emergency_contact.name')
                                ->label('Nom complet')
                                ->required(),
                            Forms\Components\TextInput::make('prospect_info.emergency_contact.relationship')
                                ->label('Lien de parenté')
                                ->required(),
                            Forms\Components\TextInput::make('prospect_info.emergency_contact.phone')
                                ->label('Téléphone')
                                ->tel()
                                ->required(),
                        ]),

                    Forms\Components\Section::make('Documents requis')
                        ->description('Téléchargez les documents nécessaires pour votre dossier')
                        ->schema([
                            Forms\Components\Repeater::make('documents')
                                ->label('')
                                ->relationship('documents')
                                ->schema([
                                    Forms\Components\Grid::make()
                                        ->schema([
                                            Forms\Components\Select::make('document_type')
                                                ->label('Type de document')
                                                ->options(DossierDocument::TYPES)
                                                ->required()
                                                ->columnSpan(1),
                                            Forms\Components\TextInput::make('description')
                                                ->label('Description')
                                                ->placeholder('Ex: Relevé de notes du BAC')
                                                ->columnSpan(1),
                                        ])->columns(2),
                                    Forms\Components\FileUpload::make('file_path')
                                        ->label('Fichier')
                                        ->required()
                                        ->disk('public')
                                        ->directory('dossiers/documents')
                                        ->visibility('public')
                                        ->preserveFilenames()
                                        ->maxSize(5120)
                                        ->acceptedFileTypes(['application/pdf', 'image/*'])
                                        ->downloadable()
                                        ->openable()
                                        ->previewable()
                                        ->columnSpanFull()
                                        ->getUploadedFileNameForStorageUsing(
                                            fn (TemporaryUploadedFile $file): string => (string) str($file->getClientOriginalName())
                                                ->prepend(now()->timestamp . '_'),
                                        ),
                                    Forms\Components\Hidden::make('step_number')
                                        ->default(function (Forms\Get $get): int {
                                            return $get('../../current_step') ?? 1;
                                        }),
                                ])
                                ->defaultItems(0)
                                ->addActionLabel('Ajouter un document')
                                ->reorderable(false),
                        ]),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDossiers::route('/'),
            'edit' => Pages\EditDossier::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('prospect', function (Builder $query) {
                $query->where('user_id', auth()->id());
            });
    }
}
