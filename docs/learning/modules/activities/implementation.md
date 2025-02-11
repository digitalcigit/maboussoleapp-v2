# Guide Technique - Module Activités

## Structure du Code

### Modèle Activity
```php
class Activity extends Model
{
    // Types d'activités disponibles
    public const TYPE_NOTE = 'note';
    public const TYPE_CALL = 'appel';
    public const TYPE_EMAIL = 'email';
    public const TYPE_MEETING = 'reunion';
    public const TYPE_DOCUMENT = 'document';
    public const TYPE_CONVERSION = 'conversion';

    // Relations principales
    public function assignedTo(): BelongsTo
    {
        return $this->user();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
```

### Resource Filament
Le module utilise Filament pour l'interface d'administration avec :
- Formulaires de création/édition
- Tableaux de liste avec filtres
- Gestion des relations

## Points Techniques Importants

### Relations
1. **assignedTo vs user**
   - `assignedTo` est un alias de `user` pour plus de clarté
   - Utilise la même table en base de données
   - Permet une meilleure lisibilité dans le code

### Dates
- `scheduled_at` : DateTime pour la planification
- `completed_at` : DateTime pour la réalisation
- Les deux champs sont nullable

### Polymorphisme
- La relation `subject` est polymorphe
- Actuellement configurée pour les prospects
- Extensible à d'autres types d'entités

## Bonnes Pratiques

### Création d'Activités
```php
// Exemple de création d'une activité
Activity::create([
    'subject_type' => Prospect::class,
    'subject_id' => $prospectId,
    'type' => Activity::TYPE_CALL,
    'description' => 'Premier contact téléphonique',
    'scheduled_at' => now()->addDays(1),
    'user_id' => $assignedUserId,
    'created_by' => auth()->id(),
]);
```

### Requêtes Courantes
```php
// Activités non réalisées
Activity::whereNull('completed_at')->get();

// Activités par type
Activity::where('type', Activity::TYPE_CALL)->get();

// Activités assignées à un utilisateur
Activity::where('user_id', $userId)->get();
```
