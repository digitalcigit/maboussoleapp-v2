# Documentation Filament Resource - Module Workflow Admission

## Vue d'Ensemble

La ressource Filament pour le Workflow Admission fournit une interface complète pour gérer les demandes d'admission, les établissements, les programmes et le suivi du processus.

## Structure des Resources

```yaml
Resources:
  - InstitutionResource
  - ProgramResource
  - AdmissionApplicationResource
  - AdmissionRequirementResource
  - AdmissionDocumentResource
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

### 1. InstitutionResource

```php
class InstitutionResource extends Resource
{
    protected static ?string $model = Institution::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $recordTitleAttribute = 'name';

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
                            
                        RichEditor::make('description')
                            ->columnSpan(2),
                            
                        TextInput::make('website')
                            ->url(),
                            
                        TextInput::make('contact_email')
                            ->email()
                            ->required(),
                            
                        TextInput::make('contact_phone')
                            ->tel(),
                            
                        KeyValue::make('admission_requirements')
                            ->keyLabel('Requirement')
                            ->valueLabel('Description')
                            ->columnSpan(2),
                            
                        KeyValue::make('metadata')
                            ->columnSpan(2)
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
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('country')
                    ->sortable(),
                    
                TextColumn::make('city')
                    ->sortable(),
                    
                TextColumn::make('contact_email')
                    ->searchable(),
                    
                TextColumn::make('programs_count')
                    ->counts('programs')
                    ->label('Programs')
            ])
            ->filters([
                SelectFilter::make('country')
                    ->options(Countries::all())
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
```

### 2. ProgramResource

```php
class ProgramResource extends Resource
{
    protected static ?string $model = Program::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Select::make('institution_id')
                            ->relationship('institution', 'name')
                            ->searchable()
                            ->required(),
                            
                        TextInput::make('name')
                            ->required(),
                            
                        TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true),
                            
                        Select::make('level')
                            ->options([
                                'bachelor' => 'Bachelor',
                                'master' => 'Master',
                                'phd' => 'PhD',
                                'certificate' => 'Certificate'
                            ])
                            ->required(),
                            
                        TextInput::make('duration')
                            ->required(),
                            
                        Grid::make(2)
                            ->schema([
                                TextInput::make('tuition_fee')
                                    ->numeric()
                                    ->required(),
                                    
                                Select::make('currency')
                                    ->options([
                                        'EUR' => 'EUR',
                                        'USD' => 'USD',
                                        'GBP' => 'GBP'
                                    ])
                                    ->required()
                            ]),
                            
                        RichEditor::make('description')
                            ->columnSpan(2),
                            
                        KeyValue::make('prerequisites')
                            ->columnSpan(2),
                            
                        KeyValue::make('admission_requirements')
                            ->columnSpan(2),
                            
                        KeyValue::make('key_dates')
                            ->columnSpan(2)
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
                    
                TextColumn::make('institution.name')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('level')
                    ->sortable(),
                    
                TextColumn::make('duration'),
                
                TextColumn::make('tuition_fee')
                    ->money(fn ($record) => $record->currency)
                    ->sortable(),
                    
                TextColumn::make('applications_count')
                    ->counts('admissionApplications')
                    ->label('Applications')
            ])
            ->filters([
                SelectFilter::make('level'),
                SelectFilter::make('institution')
                    ->relationship('institution', 'name')
            ]);
    }
}
```

### 3. AdmissionApplicationResource

```php
class AdmissionApplicationResource extends Resource
{
    protected static ?string $model = AdmissionApplication::class;
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
                            
                        Select::make('institution_id')
                            ->relationship('institution', 'name')
                            ->searchable()
                            ->required()
                            ->reactive(),
                            
                        Select::make('program_id')
                            ->relationship('program', 'name')
                            ->searchable()
                            ->required()
                            ->options(function (callable $get) {
                                $institutionId = $get('institution_id');
                                if (!$institutionId) return [];
                                
                                return Program::where('institution_id', $institutionId)
                                    ->pluck('name', 'id');
                            }),
                            
                        TextInput::make('reference_number')
                            ->default(fn () => 'ADM-' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT))
                            ->disabled()
                            ->required(),
                            
                        Select::make('status')
                            ->options([
                                'initiated' => 'Initié',
                                'in_progress' => 'En Cours',
                                'documents_required' => 'Documents Requis',
                                'documents_validated' => 'Documents Validés',
                                'submitted_institution' => 'Soumis Établissement',
                                'pending_decision' => 'En Attente Décision',
                                'accepted' => 'Accepté',
                                'conditional_accepted' => 'Accepté Conditionnel',
                                'additional_documents' => 'Documents Additionnels',
                                'rejected' => 'Rejeté',
                                'final_registration' => 'Inscription Finale'
                            ])
                            ->required(),
                            
                        DatePicker::make('intake_date')
                            ->required(),
                            
                        DatePicker::make('submission_deadline')
                            ->required(),
                            
                        DatePicker::make('submitted_date'),
                        
                        DatePicker::make('decision_date'),
                        
                        Textarea::make('rejection_reason')
                            ->visible(fn ($get) => $get('status') === 'rejected'),
                            
                        Textarea::make('conditional_requirements')
                            ->visible(fn ($get) => $get('status') === 'conditional_accepted'),
                            
                        KeyValue::make('academic_history')
                            ->columnSpan(2)
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
                    
                TextColumn::make('institution.name')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('program.name')
                    ->searchable()
                    ->sortable(),
                    
                SelectColumn::make('status')
                    ->options([
                        'initiated' => 'Initié',
                        'in_progress' => 'En Cours',
                        'documents_required' => 'Documents Requis',
                        'documents_validated' => 'Documents Validés',
                        'submitted_institution' => 'Soumis Établissement',
                        'pending_decision' => 'En Attente Décision',
                        'accepted' => 'Accepté',
                        'conditional_accepted' => 'Accepté Conditionnel',
                        'additional_documents' => 'Documents Additionnels',
                        'rejected' => 'Rejeté',
                        'final_registration' => 'Inscription Finale'
                    ])
                    ->sortable(),
                    
                DateColumn::make('intake_date')
                    ->sortable(),
                    
                DateColumn::make('submission_deadline')
                    ->sortable()
                    ->color(fn ($record) => 
                        $record->isDeadlineApproaching() ? 'danger' : 'success'
                    )
            ])
            ->filters([
                SelectFilter::make('status'),
                SelectFilter::make('institution')
                    ->relationship('institution', 'name'),
                SelectFilter::make('program')
                    ->relationship('program', 'name')
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('validateDocuments')
                    ->icon('heroicon-o-check-circle')
                    ->action(function (AdmissionApplication $record) {
                        // Logique de validation
                    })
                    ->requiresConfirmation()
                    ->visible(fn ($record) => 
                        $record->status === AdmissionApplication::STATUS_DOCUMENTS_REQUIRED
                    )
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
            'index' => Pages\ListAdmissionApplications::route('/'),
            'create' => Pages\CreateAdmissionApplication::route('/create'),
            'view' => Pages\ViewAdmissionApplication::route('/{record}'),
            'edit' => Pages\EditAdmissionApplication::route('/{record}/edit'),
        ];
    }
}
```

### 4. Relation Managers

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
                Action::make('validate')
                    ->icon('heroicon-o-check-circle')
                    ->action(fn ($record) => $record->validate(auth()->user()))
                    ->visible(fn ($record) => $record->status === 'pending'),
                Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->form([
                        Textarea::make('reason')
                            ->label('Raison du rejet')
                            ->required()
                    ])
                    ->action(fn ($record, array $data) => 
                        $record->reject(auth()->user(), $data['reason'])
                    )
                    ->visible(fn ($record) => $record->status === 'pending')
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

### ViewAdmissionApplication

```php
class ViewAdmissionApplication extends ViewRecord
{
    protected static string $resource = AdmissionApplicationResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            AdmissionApplicationStatsOverview::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            AdmissionApplicationTimeline::class,
        ];
    }
}
```

## Widgets

### AdmissionApplicationStatsOverview

```php
class AdmissionApplicationStatsOverview extends Widget
{
    protected static string $view = 'filament.resources.admission-application.widgets.stats-overview';

    public ?AdmissionApplication $record = null;

    protected function getViewData(): array
    {
        return [
            'totalDocuments' => $this->record->documents()->count(),
            'validatedDocuments' => $this->record->documents()->where('status', 'validated')->count(),
            'pendingDocuments' => $this->record->documents()->where('status', 'pending')->count(),
            'daysUntilDeadline' => now()->diffInDays($this->record->submission_deadline, false)
        ];
    }
}
```

### AdmissionApplicationTimeline

```php
class AdmissionApplicationTimeline extends Widget
{
    protected static string $view = 'filament.resources.admission-application.widgets.timeline';

    public ?AdmissionApplication $record = null;

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
    ->action(function (AdmissionDocument $record) {
        $record->validate(auth()->user());
    })
    ->requiresConfirmation()
    ->visible(fn ($record) => $record->status === 'pending');
```

### 2. Soumission Admission
```php
Action::make('submitToInstitution')
    ->icon('heroicon-o-paper-airplane')
    ->action(function (AdmissionApplication $record) {
        $record->updateStatus(AdmissionApplication::STATUS_SUBMITTED_INSTITUTION);
    })
    ->requiresConfirmation()
    ->visible(fn ($record) => $record->canBeSubmitted());
```
