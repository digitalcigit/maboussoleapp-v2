# Spécifications Techniques - Gestion des Prospects

## Structure du Modèle

```php
class Prospect extends Model
{
    use HasFactory;
    use SoftDeletes;

    // Constantes de statut
    public const STATUS_NEW = 'nouveau';
    public const STATUS_ANALYZING = 'en_analyse';
    public const STATUS_APPROVED = 'approuve';
    public const STATUS_REJECTED = 'refuse';
    public const STATUS_CONVERTED = 'converti';

    protected $fillable = [
        'reference_number',
        'first_name',
        'last_name',
        'email',
        'phone',
        'birth_date',
        'profession',
        'education_level',
        'current_location',
        'current_field',
        'desired_field',
        'desired_destination',
        'emergency_contact',
        'status',
        'assigned_to',
        'commercial_code',
        'partner_id',
        'last_action_at',
        'analysis_deadline',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'last_action_at' => 'datetime',
        'analysis_deadline' => 'datetime',
        'emergency_contact' => 'json',
    ];
}
```

## Relations

1. **Utilisateur assigné**
```php
public function assignedTo(): BelongsTo
{
    return $this->belongsTo(User::class, 'assigned_to');
}
```

2. **Partenaire**
```php
public function partner(): BelongsTo
{
    return $this->belongsTo(User::class, 'partner_id');
}
```

## Filament Resource

Le `ProspectResource` est configuré avec :

1. **Navigation**
```php
protected static ?string $navigationIcon = 'heroicon-o-user-group';
protected static ?string $navigationGroup = 'CRM';
protected static ?int $navigationSort = 1;
```

2. **Actions de table**
```php
->actions([
    Tables\Actions\ActionGroup::make([
        Tables\Actions\EditAction::make(),
        Tables\Actions\Action::make('convert_to_client')
    ])
])
```

3. **Actions en masse**
```php
->bulkActions([
    Tables\Actions\BulkActionGroup::make([
        Tables\Actions\DeleteBulkAction::make(),
        Tables\Actions\BulkAction::make('bulk-update')
    ])
])
```

## Système de Permissions

Implémenté via les méthodes :
```php
public static function canViewAny(): bool
public static function canCreate(): bool
public static function canEdit(Model $record): bool
public static function canDelete(Model $record): bool
```

## Routes

```php
public static function getPages(): array
{
    return [
        'index' => Pages\ListProspects::route('/'),
        'create' => Pages\CreateProspect::route('/create'),
        'edit' => Pages\EditProspect::route('/{record}/edit'),
        'convert' => Pages\ConvertToClient::route('/{record}/convert'),
    ];
}
```

## Processus de Conversion

La conversion d'un prospect en client suit ces étapes :

1. Vérification du statut actuel
2. Création d'un nouveau client
3. Mise à jour du statut du prospect
4. Envoi de notification
5. Redirection

## Configurations Additionnelles

- Tri par défaut : `created_at` descendant
- Persistance du tri en session
- Filtres personnalisés pour statut et source
- Badges colorés pour les différents statuts
