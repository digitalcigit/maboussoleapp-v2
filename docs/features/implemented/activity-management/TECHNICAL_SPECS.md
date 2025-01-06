# Spécifications Techniques - Gestion des Activités

## Structure du Modèle

```php
class Activity extends Model
{
    use HasFactory;
    use SoftDeletes;

    // Types d'activités
    public const TYPE_NOTE = 'note';
    public const TYPE_CALL = 'appel';
    public const TYPE_EMAIL = 'email';
    public const TYPE_MEETING = 'reunion';
    public const TYPE_DOCUMENT = 'document';
    public const TYPE_CONVERSION = 'conversion';

    // Statuts d'activités
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'subject_type',
        'subject_id',
        'type',
        'description',
        'scheduled_at',
        'completed_at',
        'status',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
```

## Relations

1. **Sujet polymorphique**
```php
public function subject(): MorphTo
{
    return $this->morphTo();
}
```

2. **Utilisateur assigné**
```php
public function assignedTo(): BelongsTo
{
    return $this->belongsTo(User::class, 'user_id');
}
```

## Filament Resource

Le `ActivityResource` est configuré avec :

1. **Navigation**
```php
protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
protected static ?string $navigationLabel = 'Activités';
protected static ?string $navigationGroup = 'CRM';
protected static ?int $navigationSort = 3;
```

2. **Actions de table**
```php
->actions([
    Tables\Actions\ActionGroup::make([
        Tables\Actions\EditAction::make()
            ->successNotification(
                Notification::make()
                    ->success()
                    ->title('Activité modifiée')
                    ->body('L\'activité a été modifiée avec succès.')
            ),
        Tables\Actions\DeleteAction::make()
            ->successNotification(
                Notification::make()
                    ->success()
                    ->title('Activité supprimée')
                    ->body('L\'activité a été supprimée avec succès.')
            )
    ])
])
```

3. **Actions en masse**
```php
->bulkActions([
    Tables\Actions\BulkActionGroup::make([
        Tables\Actions\DeleteBulkAction::make()
            ->successNotification(
                Notification::make()
                    ->success()
                    ->title('Activités supprimées')
                    ->body('Les activités ont été supprimées avec succès.')
            ),
        BulkAction::make('update-status')
            ->label('Mise à jour du statut')
            ->form([
                Select::make('status')
                    ->label('Statut')
                    ->options(self::getStatuses())
                    ->required()
            ])
            ->action(function (Collection $records, array $data) {
                $records->each(function ($record) use ($data) {
                    $record->update(['status' => $data['status']]);
                });
            })
            ->successNotification(
                Notification::make()
                    ->success()
                    ->title('Statut mis à jour')
                    ->body('Le statut des activités a été mis à jour avec succès.')
            )
    ])
])
```

## Système de Permissions

Implémenté via les méthodes :
```php
public static function canViewAny(): bool
{
    return auth()->user()->can('activities.view');
}

public static function canCreate(): bool
{
    return auth()->user()->can('activities.create');
}

public static function canEdit(Model $record): bool
{
    return auth()->user()->can('activities.edit');
}

public static function canDelete(Model $record): bool
{
    return auth()->user()->can('activities.delete');
}
```

## Validation

Les règles de validation incluent :
```php
public static function form(Form $form): Form
{
    return $form
        ->schema([
            Select::make('subject_type')
                ->label('Type de sujet')
                ->options(['Prospect' => 'Prospect'])
                ->required(),
            Select::make('subject_id')
                ->label('Sujet')
                ->relationship('subject', 'name')
                ->required(),
            Select::make('type')
                ->options(self::getTypes())
                ->required(),
            Select::make('status')
                ->options(self::getStatuses())
                ->required(),
            Textarea::make('description')
                ->required(),
            DateTimePicker::make('scheduled_at')
                ->label('Date prévue')
                ->required(),
            DateTimePicker::make('completed_at')
                ->label('Date de réalisation'),
            Select::make('user_id')
                ->label('Assigné à')
                ->relationship('assignedTo', 'name')
                ->required(),
        ]);
}
```

## Routes

```php
public static function getPages(): array
{
    return [
        'index' => Pages\ListActivities::route('/'),
        'create' => Pages\CreateActivity::route('/create'),
        'edit' => Pages\EditActivity::route('/{record}/edit'),
    ];
}
```

## Configurations Additionnelles

- Tri par défaut : `created_at` descendant
- Persistance du tri en session
- Filtres personnalisés pour type et statut
- Badges colorés pour les différents types et statuts
- Recherche sur les sujets liés (prospects)
