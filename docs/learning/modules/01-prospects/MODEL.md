# Documentation du Modèle Prospect Existant

## Vue d'Ensemble du Modèle

Le modèle `Prospect` est la pierre angulaire du module de gestion des prospects dans MaBoussole CRM.

### Traits Utilisés
```php
use HasFactory, SoftDeletes;
```
- `HasFactory` : Permet la création facile d'instances pour les tests
- `SoftDeletes` : Implémente la suppression douce des prospects

### Constantes de Statut
```php
public const STATUS_NEW = 'nouveau';
public const STATUS_ANALYZING = 'en_analyse';
public const STATUS_APPROVED = 'approuve';
public const STATUS_REJECTED = 'refuse';
public const STATUS_CONVERTED = 'converti';
```

### Attributs Principaux
```php
protected $fillable = [
    'reference_number',    // Numéro de référence unique
    'first_name',         // Prénom
    'last_name',          // Nom
    'email',              // Email de contact
    'phone',              // Téléphone
    'birth_date',         // Date de naissance
    'profession',         // Profession actuelle
    'education_level',    // Niveau d'études
    'current_location',   // Localisation actuelle
    'current_field',      // Domaine actuel
    'desired_field',      // Domaine souhaité
    'desired_destination',// Destination souhaitée
    'emergency_contact',  // Contact d'urgence (JSON)
    'status',            // Statut actuel
    'assigned_to',       // ID du conseiller assigné
    'commercial_code',   // Code commercial
    'partner_id',        // ID du partenaire
    'last_action_at',    // Date dernière action
    'analysis_deadline'  // Date limite d'analyse
];
```

### Casts Automatiques
```php
protected $casts = [
    'birth_date' => 'date',
    'last_action_at' => 'datetime',
    'analysis_deadline' => 'datetime',
    'emergency_contact' => 'json'
];
```

## Relations

### 1. Conseiller Assigné
```php
public function assignedTo(): BelongsTo
{
    return $this->belongsTo(User::class, 'assigned_to');
}
```
- Relation avec l'utilisateur responsable du prospect
- Clé étrangère : `assigned_to`

### 2. Partenaire
```php
public function partner(): BelongsTo
{
    return $this->belongsTo(User::class, 'partner_id');
}
```
- Relation avec le partenaire commercial
- Clé étrangère : `partner_id`

### 3. Activités
```php
public function activities(): MorphMany
{
    return $this->morphMany(Activity::class, 'subject');
}
```
- Relation polymorphique pour le suivi des activités
- Permet de tracer toutes les actions sur le prospect

## Méthodes Utilitaires

### Gestion des Statuts
```php
public static function getValidStatuses(): array
{
    // Liste des statuts valides
    return [
        self::STATUS_NEW,
        self::STATUS_ANALYZING,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_CONVERTED,
    ];
}

public function getStatusLabel(): string
{
    // Traduction des statuts en français
    return match($this->status) {
        self::STATUS_NEW => 'Nouveau',
        self::STATUS_ANALYZING => 'En analyse',
        self::STATUS_APPROVED => 'Approuvé',
        self::STATUS_REJECTED => 'Refusé',
        self::STATUS_CONVERTED => 'Converti',
        default => $this->status,
    };
}
```

## Points d'Apprentissage

### 1. Structure du Modèle
- Utilisation de constantes pour les statuts
- Gestion multilingue intégrée
- Organisation claire des attributs

### 2. Relations
- Utilisation de types de retour stricts (PHP 7.4+)
- Relations polymorphiques pour les activités
- Relations simples pour les utilisateurs

### 3. Bonnes Pratiques
- Soft Deletes pour la traçabilité
- Factory pour les tests
- Casts automatiques pour les dates et JSON

## Exemples d'Utilisation

### Création d'un Prospect
```php
$prospect = Prospect::create([
    'first_name' => 'Jean',
    'last_name' => 'Dupont',
    'email' => 'jean.dupont@email.com',
    'status' => Prospect::STATUS_NEW
]);
```

### Mise à Jour du Statut
```php
$prospect->update([
    'status' => Prospect::STATUS_ANALYZING,
    'assigned_to' => $conseiller->id
]);
```

### Accès aux Relations
```php
// Obtenir le conseiller
$conseiller = $prospect->assignedTo;

// Obtenir les activités récentes
$activites = $prospect->activities()
    ->latest()
    ->take(5)
    ->get();
