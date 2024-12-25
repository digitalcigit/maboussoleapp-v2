# Documentation Filament Resource - Module Workflow Visa

## Vue d'Ensemble

La ressource Filament pour le Workflow Visa fournit une interface complète pour gérer les demandes de visa, les documents requis et le suivi du processus.

## Structure des Resources

```yaml
Resources:
  - VisaApplicationResource
  - VisaRequirementResource
  - VisaDocumentResource
  - Pages:
      - List
      - Create
      - Edit
      - View
  - RelationManagers:
      - Documents
      - Requirements
      - Timeline
```

## Implémentation

### 1. VisaApplicationResource

```php
class VisaApplicationResource extends Resource
{
    protected static ?string $model = VisaApplication::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $recordTitleAttribute = 'reference_number';

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
                            
                        TextInput::make('reference_number')
                            ->default(fn () => 'VISA-' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT))
                            ->disabled()
                            ->required(),
                            
                        Select::make('status')
                            ->options([
                                'initiated' => 'Initié',
                                'in_progress' => 'En Cours',
                                'documents_required' => 'Documents Requis',
                                'documents_validated' => 'Documents Validés',
                                'submitted_embassy' => 'Soumis Ambassade',
                                'pending_decision' => 'En Attente Décision',
                                'approved' => 'Approuvé',
                                'rejected' => 'Rejeté',
                                'revision' => 'Révision'
                            ])
                            ->required(),
                            
                        Select::make('visa_type')
                            ->options([
                                'tourist' => 'Tourisme',
                                'business' => 'Affaires',
                                'student' => 'Étudiant',
                                'work' => 'Travail'
                            ])
                            ->required(),
                            
                        Select::make('embassy')
                            ->options([
                                'France' => 'France',
                                'Canada' => 'Canada',
                                'USA' => 'États-Unis'
                            ])
                            ->required(),
                            
                        DatePicker::make('planned_travel_date')
                            ->required(),
                            
                        DatePicker::make('submission_date'),
                        
                        DatePicker::make('decision_date'),
                        
                        Textarea::make('rejection_reason')
                            ->visible(fn ($get) => $get('status') === 'rejected')
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
                    
                SelectColumn::make('status')
                    ->options([
                        'initiated' => 'Initié',
                        'in_progress' => 'En Cours',
                        'documents_required' => 'Documents Requis',
                        'documents_validated' => 'Documents Validés',
                        'submitted_embassy' => 'Soumis Ambassade',
                        'pending_decision' => 'En Attente Décision',
                        'approved' => 'Approuvé',
                        'rejected' => 'Rejeté',
                        'revision' => 'Révision'
                    ])
                    ->sortable(),
                    
                TextColumn::make('visa_type')
                    ->sortable(),
                    
                TextColumn::make('embassy')
                    ->sortable(),
                    
                DateColumn::make('planned_travel_date')
                    ->sortable(),
                    
                DateColumn::make('submission_date')
                    ->sortable(),
                    
                DateColumn::make('decision_date')
                    ->sortable()
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'initiated' => 'Initié',
                        'in_progress' => 'En Cours',
                        'documents_required' => 'Documents Requis',
                        'documents_validated' => 'Documents Validés',
                        'submitted_embassy' => 'Soumis Ambassade',
                        'pending_decision' => 'En Attente Décision',
                        'approved' => 'Approuvé',
                        'rejected' => 'Rejeté',
                        'revision' => 'Révision'
                    ]),
                    
                SelectFilter::make('visa_type')
                    ->options([
                        'tourist' => 'Tourisme',
                        'business' => 'Affaires',
                        'student' => 'Étudiant',
                        'work' => 'Travail'
                    ]),
                    
                SelectFilter::make('embassy')
                    ->options([
                        'France' => 'France',
                        'Canada' => 'Canada',
                        'USA' => 'États-Unis'
                    ])
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            DocumentsRelationManager::class,
            RequirementsRelationManager::class,
            TimelineRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVisaApplications::route('/'),
            'create' => Pages\CreateVisaApplication::route('/create'),
            'view' => Pages\ViewVisaApplication::route('/{record}'),
            'edit' => Pages\EditVisaApplication::route('/{record}/edit'),
        ];
    }
}
```

### 2. Relation Managers

#### DocumentsRelationManager

```php
class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('requirement_id')
                    ->relationship('requirement', 'name')
                    ->required(),
                    
                FileUpload::make('document')
                    ->required(),
                    
                Select::make('status')
                    ->options([
                        'pending' => 'En Attente',
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
                TextColumn::make('status'),
                TextColumn::make('validated_at'),
                TextColumn::make('validator.name')
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'En Attente',
                        'validated' => 'Validé',
                        'rejected' => 'Rejeté'
                    ])
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }    
}
```

#### TimelineRelationManager

```php
class TimelineRelationManager extends RelationManager
{
    protected static string $relationship = 'timeline';

    protected static ?string $recordTitleAttribute = 'title';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event_type')
                    ->badge()
                    ->color(fn ($record) => $record->color),
                    
                TextColumn::make('title'),
                
                TextColumn::make('created_at')
                    ->dateTime(),
                    
                TextColumn::make('creator.name')
            ])
            ->filters([
                SelectFilter::make('event_type')
                    ->options([
                        'status_changed' => 'Changement Statut',
                        'document_uploaded' => 'Document Uploadé',
                        'document_validated' => 'Document Validé',
                        'document_rejected' => 'Document Rejeté'
                    ])
            ])
            ->reorderable(false)
            ->defaultSort('created_at', 'desc');
    }
}
```

## Pages Personnalisées

### ViewVisaApplication

```php
class ViewVisaApplication extends ViewRecord
{
    protected static string $resource = VisaApplicationResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            VisaApplicationStatsOverview::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            VisaApplicationTimeline::class,
        ];
    }
}
```

## Widgets

### VisaApplicationStatsOverview

```php
class VisaApplicationStatsOverview extends Widget
{
    protected static string $view = 'filament.resources.visa-application.widgets.stats-overview';

    public ?VisaApplication $record = null;

    protected function getViewData(): array
    {
        return [
            'totalDocuments' => $this->record->documents()->count(),
            'validatedDocuments' => $this->record->documents()->where('status', 'validated')->count(),
            'pendingDocuments' => $this->record->documents()->where('status', 'pending')->count(),
            'daysUntilTravel' => now()->diffInDays($this->record->planned_travel_date, false)
        ];
    }
}
```

### VisaApplicationTimeline

```php
class VisaApplicationTimeline extends Widget
{
    protected static string $view = 'filament.resources.visa-application.widgets.timeline';

    public ?VisaApplication $record = null;

    protected function getViewData(): array
    {
        return [
            'events' => $this->record->getTimelineEvents()
        ];
    }
}
```

## Points d'Apprentissage

### 1. Structure Interface
```yaml
Components:
  - Form Layouts
  - Table Columns
  - Filters
  - Actions
  - Widgets
```

### 2. Relations
```yaml
Gestion:
  - Documents
  - Requirements
  - Timeline
  - Stats
```

### 3. Validation
```yaml
Process:
  - Form Rules
  - State Management
  - File Upload
  - User Feedback
```

## Exemples de Vues

### 1. Stats Overview
```blade
<div class="grid gap-4 lg:grid-cols-4">
    <x-filament::card>
        <div class="flex items-center gap-4">
            <div>
                <h2 class="text-lg font-medium">Total Documents</h2>
                <p class="text-3xl font-bold">{{ $totalDocuments }}</p>
            </div>
            <x-heroicon-o-document class="w-8 h-8 text-primary-500" />
        </div>
    </x-filament::card>
    
    <!-- Similar cards for other stats -->
</div>
```

### 2. Timeline
```blade
<div class="space-y-4">
    @foreach($events as $event)
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
                <x-dynamic-component
                    :component="$event->icon"
                    class="w-6 h-6"
                />
            </div>
            
            <div>
                <h3 class="font-medium">{{ $event->title }}</h3>
                <p class="text-sm text-gray-500">
                    {{ $event->created_at->diffForHumans() }}
                    par {{ $event->creator->name }}
                </p>
            </div>
        </div>
    @endforeach
</div>
```

## Actions Personnalisées

### 1. Validation Document
```php
Action::make('validateDocument')
    ->icon('heroicon-o-check-circle')
    ->action(function (VisaDocument $record) {
        $record->validate(auth()->user());
    })
    ->requiresConfirmation()
    ->visible(fn ($record) => $record->status === 'pending');
```

### 2. Soumission Visa
```php
Action::make('submitToEmbassy')
    ->icon('heroicon-o-paper-airplane')
    ->action(function (VisaApplication $record) {
        $record->updateStatus(VisaApplication::STATUS_SUBMITTED_EMBASSY);
    })
    ->requiresConfirmation()
    ->visible(fn ($record) => $record->canBeSubmitted());
```
