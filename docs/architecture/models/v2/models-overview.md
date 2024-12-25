# Modèles v2 - MaBoussole CRM

## Vue d'ensemble
La version 2 du CRM utilise une architecture centrée sur trois modèles principaux :
- `Client` : Représente les clients convertis
- `Prospect` : Représente les prospects en cours de conversion
- `Activity` : Suit toutes les interactions avec les clients et prospects

## Conventions Importantes
1. **Langue** : Français pour tous les statuts et libellés
2. **Soft Deletes** : Activé sur tous les modèles principaux
3. **Timestamps** : created_at, updated_at sur tous les modèles
4. **Relations** : Utilisation de relations polymorphiques pour les activités

## Structure Détaillée

### Client
```php
class Client extends Model
{
    // Statuts de base
    const STATUS_ACTIVE = 'actif';
    const STATUS_INACTIVE = 'inactif';
    const STATUS_PENDING = 'en_attente';
    const STATUS_ARCHIVED = 'archive';

    // Statuts de visa
    const VISA_STATUS_NOT_STARTED = 'non_demarre';
    const VISA_STATUS_IN_PROGRESS = 'en_cours';
    const VISA_STATUS_OBTAINED = 'obtenu';
    const VISA_STATUS_REJECTED = 'refuse';

    // Statuts de paiement
    const PAYMENT_STATUS_PENDING = 'en_attente';
    const PAYMENT_STATUS_PARTIAL = 'partiel';
    const PAYMENT_STATUS_COMPLETED = 'complete';
}
```

### Prospect
```php
class Prospect extends Model
{
    // Statuts
    const STATUS_NEW = 'nouveau';
    const STATUS_ANALYZING = 'en_analyse';
    const STATUS_APPROVED = 'approuve';
    const STATUS_REJECTED = 'rejete';
    const STATUS_CONVERTED = 'converti';
}
```

### Activity
```php
class Activity extends Model
{
    // Types
    const TYPE_NOTE = 'note';
    const TYPE_CALL = 'appel';
    const TYPE_EMAIL = 'email';
    const TYPE_MEETING = 'reunion';
    const TYPE_DOCUMENT = 'document';
    const TYPE_CONVERSION = 'conversion';

    // Statuts
    const STATUS_PENDING = 'en_attente';
    const STATUS_IN_PROGRESS = 'en_cours';
    const STATUS_COMPLETED = 'termine';
    const STATUS_CANCELLED = 'annule';
}
```

## Relations Clés

### Client
- `prospect()`: BelongsTo Prospect
- `activities()`: MorphMany Activity
- `documents()`: MorphMany Document

### Prospect
- `client()`: HasOne Client
- `activities()`: MorphMany Activity
- `documents()`: MorphMany Document
- `assignedTo()`: BelongsTo User
- `partner()`: BelongsTo User

### Activity
- `subject()`: MorphTo (Client/Prospect)
- `user()`: BelongsTo User
- `createdBy()`: BelongsTo User

## Validation et Règles Métier

### Client
1. Doit avoir un prospect associé
2. Numéro de client unique
3. Montant payé ≤ Montant total

### Prospect
1. Référence unique
2. Email ou téléphone requis
3. Contact d'urgence validé

### Activity
1. Description requise
2. Date planifiée ≥ Date actuelle
3. Type et statut valides

## Notes pour l'IA
1. Toujours vérifier les constantes de statut lors des mises à jour
2. Maintenir la cohérence des libellés en français
3. Respecter les relations polymorphiques pour les activités
4. Vérifier les dépendances lors des suppressions
