# Documentation de la Resource Filament Document

## Structure des Fichiers

```yaml
DocumentResource/
  ├── DocumentResource.php              # Resource principale
  ├── RelationManagers/
  │   └── DocumentsRelationManager.php  # Pour utilisation dans d'autres ressources
  └── Pages/
      ├── ListDocuments.php            # Liste des documents
      ├── CreateDocument.php           # Upload
      ├── EditDocument.php             # Modification
      └── ViewDocument.php             # Visualisation
```

## Resource Principale

### Configuration Globale
```php
class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;
    protected static ?string $navigationIcon = 'heroicon-o-document';
    protected static ?string $navigationGroup = 'Gestion Documents';
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::pending()->count();
    }
}
```

### Table
```php
public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('name')
                ->label('Nom')
                ->searchable()
                ->sortable(),
                
            BadgeColumn::make('type')
                ->colors([
                    'primary' => 'passport',
                    'success' => 'cv',
                    'warning' => 'diploma',
                    'secondary' => 'other',
                ]),
                
            BadgeColumn::make('status')
                ->colors([
                    'warning' => 'pending',
                    'success' => 'validated',
                    'danger' => 'rejected',
                ]),
                
            TextColumn::make('documentable_type')
                ->label('Type Entité')
                ->formatStateUsing(fn ($state) => class_basename($state)),
                
            TextColumn::make('formatted_size')
                ->label('Taille')
                ->sortable('size'),
                
            TextColumn::make('validator.name')
                ->label('Validé par')
                ->placeholder('Non validé'),
                
            DateTimeColumn::make('validation_date')
                ->label('Date validation'),
        ])
        ->filters([
            SelectFilter::make('type')
                ->options(DocumentType::class),
                
            SelectFilter::make('status')
                ->options(DocumentStatus::class),
                
            Filter::make('validated')
                ->query(fn ($query) => $query->whereNotNull('validation_date')),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
            Action::make('download')
                ->icon('heroicon-o-download')
                ->url(fn (Document $record) => $record->getUrl()),
            Action::make('validate')
                ->icon('heroicon-o-check')
                ->action(fn (Document $record) => $record->validate(Auth::user()))
                ->requiresConfirmation()
                ->visible(fn (Document $record) => $record->status === DocumentStatus::PENDING),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
            BulkAction::make('validate_selected')
                ->action(fn (Collection $records) => $records->each->validate(Auth::user()))
                ->requiresConfirmation()
                ->deselectRecordsAfterCompletion(),
        ]);
}
```

### Formulaire
```php
public static function form(Form $form): Form
{
    return $form
        ->schema([
            FileUpload::make('path')
                ->label('Document')
                ->disk('documents')
                ->directory(fn ($record) => $record?->documentable 
                    ? strtolower(class_basename($record->documentable_type)) . '/' . $record->documentable_id
                    : 'temp'
                )
                ->visibility('private')
                ->acceptedFileTypes([
                    'application/pdf',
                    'image/jpeg',
                    'image/png'
                ])
                ->maxSize(10240), // 10MB
                
            Select::make('type')
                ->options(DocumentType::class)
                ->required(),
                
            Select::make('status')
                ->options(DocumentStatus::class)
                ->default(DocumentStatus::PENDING)
                ->disabled(fn ($record) => !auth()->user()->can('validate_documents')),
                
            Textarea::make('comments')
                ->rows(3)
                ->maxLength(1000),
        ]);
}
```

## RelationManager

```php
class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';
    
    protected static ?string $recordTitleAttribute = 'name';
    
    public static function form(Form $form): Form
    {
        return DocumentResource::form($form);
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                BadgeColumn::make('type'),
                BadgeColumn::make('status'),
                TextColumn::make('formatted_size'),
                DateTimeColumn::make('created_at'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(DocumentType::class),
                SelectFilter::make('status')
                    ->options(DocumentStatus::class),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\DetachBulkAction::make(),
            ]);
    }
}
```

## Points d'Apprentissage

### 1. Upload de Fichiers
```yaml
Configuration:
  - Disk dédié
  - Structure dossiers
  - Types acceptés
  - Taille maximale
```

### 2. Validation
```yaml
Process:
  - Actions contextuelles
  - Permissions utilisateur
  - Bulk actions
```

### 3. Relations
```yaml
Gestion:
  - Relation Manager
  - Attach/Detach
  - Actions spécifiques
```

## Bonnes Pratiques

### 1. Interface Utilisateur
```yaml
Ergonomie:
  - Actions claires
  - Feedback visuel
  - Confirmations
  - Raccourcis
```

### 2. Performance
```yaml
Optimisations:
  - Lazy loading
  - Cache
  - Pagination
```

### 3. Sécurité
```yaml
Contrôles:
  - Permissions
  - Validation input
  - Sanitization
```

## Exemples d'Utilisation

### 1. Upload Multiple
```php
FileUpload::make('documents')
    ->multiple()
    ->maxFiles(5)
    ->disk('documents')
    ->directory('temp')
    ->storeFileNamesIn('original_names');
```

### 2. Prévisualisation
```php
FileUpload::make('path')
    ->image()
    ->imagePreviewHeight('250')
    ->loadingIndicatorPosition('left')
    ->panelAspectRatio('2:1')
    ->panelLayout('integrated');
```

### 3. Actions Personnalisées
```php
Action::make('process')
    ->icon('heroicon-o-cog')
    ->action(function (Document $record) {
        // Traitement spécifique
    })
    ->requiresConfirmation()
    ->modalHeading('Traiter le document')
    ->modalSubheading('Êtes-vous sûr de vouloir traiter ce document ?')
    ->modalButton('Oui, traiter');
```
