# Documentation des Ressources Filament - Module Workflow Logement

## Vue d'Ensemble

Le module Workflow Logement utilise Filament pour créer une interface administrative intuitive et puissante pour gérer les demandes de logement.

## Ressources Principales

### 1. HousingApplicationResource

```php
class HousingApplicationResource extends Resource
{
    protected static ?string $model = HousingApplication::class;
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationGroup = 'Logement';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Select::make('client_id')
                            ->relationship('client', 'name')
                            ->searchable()
                            ->required(),

                        Select::make('housing_id')
                            ->relationship('housing', 'reference')
                            ->searchable()
                            ->required(),

                        TextInput::make('reference_number')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Select::make('status')
                            ->options([
                                'initiated' => 'Initié',
                                'searching' => 'En recherche',
                                'housing_identified' => 'Logement identifié',
                                'documents_required' => 'Documents requis',
                                'documents_validated' => 'Documents validés',
                                'application_submitted' => 'Demande soumise',
                                'pending_decision' => 'En attente de décision',
                                'accepted' => 'Accepté',
                                'rejected' => 'Rejeté',
                                'contract_signed' => 'Contrat signé',
                                'deposit_paid' => 'Dépôt payé',
                                'move_in_planned' => 'Emménagement planifié',
                                'moved_in' => 'Emménagé'
                            ])
                            ->required(),

                        DatePicker::make('desired_move_in_date')
                            ->required(),

                        DatePicker::make('submission_date'),
                        DatePicker::make('decision_date'),
                        DatePicker::make('contract_date'),
                        DatePicker::make('move_in_date'),

                        Textarea::make('rejection_reason')
                            ->visible(fn ($get) => $get('status') === 'rejected'),

                        TextInput::make('monthly_budget')
                            ->numeric()
                            ->required(),

                        KeyValue::make('guarantor_info')
                            ->keyLabel('Information')
                            ->valueLabel('Détail'),

                        KeyValue::make('metadata')
                            ->keyLabel('Clé')
                            ->valueLabel('Valeur')
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_number')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('client.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('housing.reference')
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'rejected',
                        'warning' => 'pending_decision',
                        'success' => 'moved_in',
                        'primary' => 'initiated'
                    ]),

                TextColumn::make('desired_move_in_date')
                    ->date()
                    ->sortable(),

                TextColumn::make('monthly_budget')
                    ->money('eur')
                    ->sortable()
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'initiated' => 'Initié',
                        'searching' => 'En recherche',
                        'pending_decision' => 'En attente',
                        'accepted' => 'Accepté',
                        'rejected' => 'Rejeté',
                        'moved_in' => 'Emménagé'
                    ]),

                Filter::make('pending')
                    ->query(fn ($query) => $query->where('status', '!=', 'moved_in')
                        ->where('status', '!=', 'rejected'))
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Action::make('documents')
                    ->icon('heroicon-o-document')
                    ->url(fn (HousingApplication $record) => 
                        route('filament.resources.housing-applications.documents', $record))
            ]);
    }

    public static function getRelations(): array
    {
        return [
            DocumentsRelationManager::class,
            TimelineRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHousingApplications::route('/'),
            'create' => Pages\CreateHousingApplication::route('/create'),
            'edit' => Pages\EditHousingApplication::route('/{record}/edit'),
            'view' => Pages\ViewHousingApplication::route('/{record}'),
            'documents' => Pages\ManageHousingDocuments::route('/{record}/documents')
        ];
    }
}
```

### 2. ResidenceResource

```php
class ResidenceResource extends Resource
{
    protected static ?string $model = Residence::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Logement';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        TextInput::make('name')
                            ->required(),

                        TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Select::make('country')
                            ->options(Countries::all())
                            ->required(),

                        TextInput::make('city')
                            ->required(),

                        TextInput::make('address')
                            ->required(),

                        RichEditor::make('description'),

                        TextInput::make('website')
                            ->url(),

                        TextInput::make('contact_email')
                            ->email()
                            ->required(),

                        TextInput::make('contact_phone'),

                        TagsInput::make('amenities')
                            ->separator(','),

                        KeyValue::make('rules')
                            ->keyLabel('Règle')
                            ->valueLabel('Description'),

                        KeyValue::make('metadata')
                            ->keyLabel('Clé')
                            ->valueLabel('Valeur')
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->searchable(),

                TextColumn::make('city')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('country')
                    ->searchable()
                    ->sortable(),

                BooleanColumn::make('hasAvailableHousings')
                    ->label('Disponible')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
            ])
            ->filters([
                SelectFilter::make('country')
                    ->options(Countries::all()),

                Filter::make('has_available')
                    ->query(fn ($query) => $query->whereHas('housings', function ($q) {
                        $q->where('is_available', true);
                    }))
            ]);
    }

    public static function getRelations(): array
    {
        return [
            HousingsRelationManager::class
        ];
    }
}
```

### 3. LandlordResource

```php
class LandlordResource extends Resource
{
    protected static ?string $model = Landlord::class;
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationGroup = 'Logement';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        TextInput::make('name')
                            ->required(),

                        TextInput::make('email')
                            ->email()
                            ->required(),

                        TextInput::make('phone'),

                        Select::make('preferred_contact_method')
                            ->options([
                                'email' => 'Email',
                                'phone' => 'Téléphone'
                            ])
                            ->required(),

                        Textarea::make('notes'),

                        KeyValue::make('preferences')
                            ->keyLabel('Préférence')
                            ->valueLabel('Détail'),

                        KeyValue::make('metadata')
                            ->keyLabel('Clé')
                            ->valueLabel('Valeur')
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable(),

                TextColumn::make('phone')
                    ->searchable(),

                TextColumn::make('preferred_contact_method')
                    ->enum([
                        'email' => 'Email',
                        'phone' => 'Téléphone'
                    ])
            ])
            ->filters([
                SelectFilter::make('preferred_contact_method')
                    ->options([
                        'email' => 'Email',
                        'phone' => 'Téléphone'
                    ])
            ]);
    }

    public static function getRelations(): array
    {
        return [
            HousingsRelationManager::class
        ];
    }
}
```

### 4. HousingResource

```php
class HousingResource extends Resource
{
    protected static ?string $model = Housing::class;
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationGroup = 'Logement';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Select::make('residence_id')
                            ->relationship('residence', 'name')
                            ->searchable(),

                        Select::make('landlord_id')
                            ->relationship('landlord', 'name')
                            ->searchable()
                            ->required(),

                        TextInput::make('reference')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Select::make('type')
                            ->options([
                                'studio' => 'Studio',
                                'room' => 'Chambre',
                                'apartment' => 'Appartement',
                                'shared' => 'Colocation',
                                'residence' => 'Résidence'
                            ])
                            ->required(),

                        TextInput::make('address')
                            ->required(),

                        TextInput::make('city')
                            ->required(),

                        TextInput::make('postal_code')
                            ->required(),

                        TextInput::make('surface')
                            ->numeric()
                            ->required(),

                        TextInput::make('rooms')
                            ->numeric()
                            ->required(),

                        TextInput::make('rent')
                            ->numeric()
                            ->required(),

                        TextInput::make('deposit')
                            ->numeric()
                            ->required(),

                        TextInput::make('agency_fees')
                            ->numeric(),

                        RichEditor::make('description'),

                        Toggle::make('is_furnished')
                            ->required(),

                        DatePicker::make('available_from')
                            ->required(),

                        DatePicker::make('available_until'),

                        Toggle::make('is_available')
                            ->required(),

                        TagsInput::make('amenities')
                            ->separator(','),

                        KeyValue::make('requirements')
                            ->keyLabel('Exigence')
                            ->valueLabel('Description'),

                        KeyValue::make('metadata')
                            ->keyLabel('Clé')
                            ->valueLabel('Valeur')
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->enum([
                        'studio' => 'Studio',
                        'room' => 'Chambre',
                        'apartment' => 'Appartement',
                        'shared' => 'Colocation',
                        'residence' => 'Résidence'
                    ]),

                TextColumn::make('city')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('rent')
                    ->money('eur')
                    ->sortable(),

                BooleanColumn::make('is_available')
                    ->label('Disponible')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                TextColumn::make('available_from')
                    ->date()
                    ->sortable()
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'studio' => 'Studio',
                        'room' => 'Chambre',
                        'apartment' => 'Appartement',
                        'shared' => 'Colocation',
                        'residence' => 'Résidence'
                    ]),

                Filter::make('available')
                    ->query(fn ($query) => $query->where('is_available', true)),

                Filter::make('price_range')
                    ->form([
                        TextInput::make('min_price')
                            ->numeric(),
                        TextInput::make('max_price')
                            ->numeric()
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['min_price'],
                                fn ($q, $price) => $q->where('rent', '>=', $price)
                            )
                            ->when(
                                $data['max_price'],
                                fn ($q, $price) => $q->where('rent', '<=', $price)
                            );
                    })
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ApplicationsRelationManager::class
        ];
    }
}
```

## Relations Managers

### 1. DocumentsRelationManager

```php
class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('housing_requirement_id')
                    ->relationship('requirement', 'name')
                    ->required(),

                FileUpload::make('document')
                    ->required(),

                Select::make('status')
                    ->options([
                        'pending' => 'En attente',
                        'validated' => 'Validé',
                        'rejected' => 'Rejeté'
                    ])
                    ->required(),

                Textarea::make('rejection_reason')
                    ->visible(fn ($get) => $get('status') === 'rejected')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('requirement.name'),
                TextColumn::make('document.name'),
                BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'rejected',
                        'warning' => 'pending',
                        'success' => 'validated'
                    ]),
                TextColumn::make('validated_at')
                    ->dateTime(),
                TextColumn::make('validator.name')
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'En attente',
                        'validated' => 'Validé',
                        'rejected' => 'Rejeté'
                    ])
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
            ]);
    }
}
```

### 2. TimelineRelationManager

```php
class TimelineRelationManager extends RelationManager
{
    protected static string $relationship = 'timeline';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event_type')
                    ->label('Type')
                    ->enum([
                        'status_changed' => 'Changement statut',
                        'document_uploaded' => 'Document ajouté',
                        'document_validated' => 'Document validé',
                        'document_rejected' => 'Document rejeté'
                    ]),

                TextColumn::make('title')
                    ->label('Titre'),

                TextColumn::make('description')
                    ->label('Description'),

                TextColumn::make('creator.name')
                    ->label('Créé par'),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('event_type')
                    ->options([
                        'status_changed' => 'Changement statut',
                        'document_uploaded' => 'Document ajouté',
                        'document_validated' => 'Document validé',
                        'document_rejected' => 'Document rejeté'
                    ])
            ]);
    }
}
```

## Points d'Apprentissage

### 1. Structure Interface
```yaml
Composants:
  - Forms
  - Tables
  - Relations
  - Actions
  - Filters
```

### 2. Validation
```yaml
Types:
  - Required fields
  - Unique values
  - Date formats
  - Number ranges
```

### 3. Relations
```yaml
Gestion:
  - One-to-Many
  - Many-to-Many
  - Nested forms
  - Inline editing
```

## Bonnes Pratiques

### 1. Organisation
```yaml
Structure:
  - Groupes navigation
  - Pages logiques
  - Actions contextuelles
  - Filtres pertinents
```

### 2. UX
```yaml
Éléments:
  - Feedback visuel
  - États clairs
  - Actions rapides
  - Recherche efficace
```

### 3. Performance
```yaml
Optimisations:
  - Lazy loading
  - Pagination
  - Caching
  - Indexes
```
