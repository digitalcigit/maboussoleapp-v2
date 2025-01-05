# Gestion des Activités

## Vue d'ensemble
Le système de gestion des activités permet de suivre toutes les interactions avec les prospects et les clients. Il utilise une relation polymorphique pour lier les activités à différents types d'entités (prospects, clients, etc.).

## Structure Technique

### Modèle Activity
```php
class Activity extends Model
{
    use HasFactory;
    use SoftDeletes;

    // Types d'activités disponibles
    public const TYPE_NOTE = 'note';
    public const TYPE_CALL = 'appel';
    public const TYPE_EMAIL = 'email';
    public const TYPE_MEETING = 'reunion';
    public const TYPE_DOCUMENT = 'document';
    public const TYPE_CONVERSION = 'conversion';

    // Statuts possibles
    public const STATUS_PENDING = 'en_attente';
    public const STATUS_IN_PROGRESS = 'en_cours';
    public const STATUS_COMPLETED = 'termine';
    public const STATUS_CANCELLED = 'annule';

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

### Relations
Les activités utilisent une relation polymorphique `subject` qui permet de les lier à différents types d'entités :

```php
// Dans Activity.php
public function subject(): MorphTo
{
    return $this->morphTo();
}

// Dans Prospect.php
public function activities(): MorphMany
{
    return $this->morphMany(Activity::class, 'subject');
}
```

## Interface Utilisateur
L'interface de gestion des activités est implémentée via Filament en utilisant un RelationManager. Cela permet :
- La création d'activités directement depuis la page d'un prospect
- L'affichage des activités dans un tableau avec filtres et tri
- La modification et la suppression d'activités existantes

### Champs du Formulaire
- Type (select) : Note, Appel, Email, Réunion, Document, Conversion
- Statut (select) : En attente, En cours, Terminé, Annulé
- Description (textarea)
- Date prévue (datetime)
- Date de réalisation (datetime)
- Assigné à (select) : Liste des utilisateurs

## Points Techniques Importants

### Relation Polymorphique
La relation polymorphique est gérée automatiquement par Laravel. Les champs `subject_type` et `subject_id` sont remplis automatiquement lors de la création d'une activité via le RelationManager.

### Assignation des Utilisateurs
La relation avec l'utilisateur assigné utilise `assignedTo` plutôt que `user` pour plus de clarté :
```php
public function assignedTo(): BelongsTo
{
    return $this->belongsTo(User::class, 'user_id');
}
```

## Leçons Apprises
1. Ne pas dupliquer les relations dans `getRelations()` pour éviter les conflits
2. Laisser Laravel gérer automatiquement les relations polymorphiques plutôt que de tenter de les gérer manuellement
3. Utiliser des noms de relations explicites (ex: `assignedTo` au lieu de `user`)

## Maintenance et Évolution
Pour ajouter de nouveaux types d'activités ou statuts :
1. Ajouter les constantes dans le modèle `Activity`
2. Mettre à jour les options dans le RelationManager
3. Mettre à jour les colonnes de badge dans le tableau si nécessaire
