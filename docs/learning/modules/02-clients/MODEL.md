# Documentation du Modèle Client

## Vue d'Ensemble

Le modèle `Client` représente un prospect converti et gère son suivi complet, incluant les statuts de visa, les paiements et les documents.

## Structure du Modèle

### Traits Utilisés
```php
use HasFactory, SoftDeletes;
```

### Constantes de Statut

#### 1. Statuts de Base
```php
public const STATUS_ACTIVE = 'actif';
public const STATUS_INACTIVE = 'inactif';
public const STATUS_PENDING = 'en_attente';
public const STATUS_ARCHIVED = 'archive';
```

#### 2. Statuts de Paiement
```php
public const PAYMENT_STATUS_PENDING = 'en_attente';
public const PAYMENT_STATUS_PARTIAL = 'partiel';
public const PAYMENT_STATUS_COMPLETED = 'complete';
```

#### 3. Statuts de Visa
```php
public const VISA_STATUS_NOT_STARTED = 'non_demarre';
public const VISA_STATUS_IN_PROGRESS = 'en_cours';
public const VISA_STATUS_OBTAINED = 'obtenu';
public const VISA_STATUS_REJECTED = 'refuse';
```

### Attributs

```php
protected $fillable = [
    'prospect_id',         // ID du prospect d'origine
    'client_number',       // Numéro unique du client
    'passport_number',     // Numéro de passeport
    'passport_expiry',     // Date d'expiration du passeport
    'visa_status',        // Statut du visa
    'travel_preferences', // Préférences de voyage (JSON)
    'payment_status',     // Statut du paiement
    'total_amount',       // Montant total à payer
    'paid_amount',        // Montant déjà payé
    'status'             // Statut général du client
];

protected $casts = [
    'passport_expiry' => 'date',
    'travel_preferences' => 'json',
    'total_amount' => 'decimal:2',
    'paid_amount' => 'decimal:2'
];
```

## Relations

### 1. Prospect
```php
public function prospect(): BelongsTo
{
    return $this->belongsTo(Prospect::class);
}
```
- Relation avec le prospect d'origine
- Permet d'accéder aux informations de base du client

### 2. Activités
```php
public function activities(): MorphMany
{
    return $this->morphMany(Activity::class, 'subject');
}
```
- Relation polymorphique pour le suivi des activités
- Historique des actions sur le client

### 3. Documents
```php
public function documents(): MorphMany
{
    return $this->morphMany(Document::class, 'documentable');
}
```
- Gestion des documents du client
- Relation polymorphique pour flexibilité

## Méthodes Utilitaires

### Gestion des Statuts

#### 1. Statuts de Base
```php
public static function getValidStatuses(): array
{
    return [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
        self::STATUS_PENDING,
        self::STATUS_ARCHIVED,
    ];
}

public function getStatusLabel(): string
{
    return match($this->status) {
        self::STATUS_ACTIVE => 'Actif',
        self::STATUS_INACTIVE => 'Inactif',
        self::STATUS_PENDING => 'En attente',
        self::STATUS_ARCHIVED => 'Archivé',
        default => $this->status,
    };
}
```

#### 2. Statuts de Paiement
```php
public static function getValidPaymentStatuses(): array
{
    return [
        self::PAYMENT_STATUS_PENDING,
        self::PAYMENT_STATUS_PARTIAL,
        self::PAYMENT_STATUS_COMPLETED,
    ];
}

public function getPaymentStatusLabel(): string
{
    return match($this->payment_status) {
        self::PAYMENT_STATUS_PENDING => 'En attente',
        self::PAYMENT_STATUS_PARTIAL => 'Partiel',
        self::PAYMENT_STATUS_COMPLETED => 'Complété',
        default => $this->payment_status,
    };
}
```

#### 3. Statuts de Visa
```php
public static function getValidVisaStatuses(): array
{
    return [
        self::VISA_STATUS_NOT_STARTED,
        self::VISA_STATUS_IN_PROGRESS,
        self::VISA_STATUS_OBTAINED,
        self::VISA_STATUS_REJECTED,
    ];
}

public function getVisaStatusLabel(): string
{
    return match($this->visa_status) {
        self::VISA_STATUS_NOT_STARTED => 'Non démarré',
        self::VISA_STATUS_IN_PROGRESS => 'En cours',
        self::VISA_STATUS_OBTAINED => 'Obtenu',
        self::VISA_STATUS_REJECTED => 'Refusé',
        default => $this->visa_status,
    };
}
```

### Accesseurs
```php
public function getFullNameAttribute(): string
{
    return "{$this->prospect->first_name} {$this->prospect->last_name}";
}
```

## Points d'Apprentissage

### 1. Gestion des Statuts
- Utilisation de constantes pour les statuts
- Méthodes de validation des statuts
- Labels traduits pour l'interface

### 2. Relations
- Relation avec le prospect d'origine
- Relations polymorphiques pour documents et activités
- Accès aux données liées

### 3. Données Financières
- Gestion des montants avec décimales
- Suivi des paiements
- Statuts de paiement

### 4. Documents et Visa
- Suivi du statut de visa
- Gestion des documents
- Dates d'expiration

## Exemples d'Utilisation

### Création d'un Client
```php
$client = Client::create([
    'prospect_id' => $prospect->id,
    'client_number' => 'CL-2024-001',
    'status' => Client::STATUS_ACTIVE,
    'visa_status' => Client::VISA_STATUS_NOT_STARTED,
    'payment_status' => Client::PAYMENT_STATUS_PENDING,
]);
```

### Mise à Jour du Statut
```php
$client->update([
    'visa_status' => Client::VISA_STATUS_IN_PROGRESS,
    'payment_status' => Client::PAYMENT_STATUS_PARTIAL,
]);
```

### Accès aux Relations
```php
// Informations du prospect
$nomClient = $client->full_name;

// Documents
$documents = $client->documents()->latest()->get();

// Activités récentes
$activites = $client->activities()
    ->latest()
    ->take(5)
    ->get();
```
