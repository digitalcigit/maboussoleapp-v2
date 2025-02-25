# Concepts Fondamentaux du Workflow des Dossiers

## 1. Principes de base

### Cycle de vie d'un dossier

Un dossier dans MaBoussole suit un cycle de vie prédéfini qui reflète le processus métier de l'entreprise :

```
[Création] -> [Documents] -> [Validation] -> [Paiement] -> [Traitement] -> [Complétion]
                                                                      \-> [Rejet]
```

### États et transitions

Chaque dossier possède :
- Une étape courante (current_step)
- Un statut dans cette étape (current_status)
- Un ensemble de documents requis
- Des informations personnelles

## 2. Logique métier

### Distinction Prospect/Client

```php
public function isProspect(): bool
{
    return in_array($this->current_step, [
        self::STEP_INITIAL,
        self::STEP_DOCUMENTS,
        self::STEP_VALIDATION
    ]);
}

public function isClient(): bool
{
    return in_array($this->current_step, [
        self::STEP_PAYMENT,
        self::STEP_PROCESSING,
        self::STEP_COMPLETED
    ]);
}
```

### Statuts possibles

Les statuts sont transversaux aux étapes :
- STATUS_WAITING_DOCS : En attente de documents
- STATUS_IN_PROGRESS : En cours de traitement
- STATUS_BLOCKED : Bloqué (nécessite une intervention)
- STATUS_COMPLETED : Terminé (pour l'étape courante)

## 3. Validation et contrôles

### Règles de validation

Chaque étape a ses propres règles de validation :
1. Documents requis complets et valides
2. Informations personnelles complètes
3. Paiements effectués (si applicable)
4. Validations administratives nécessaires

### Progression du workflow

La progression n'est possible que si :
- Toutes les validations de l'étape courante sont satisfaites
- L'utilisateur a les permissions nécessaires
- Aucun blocage n'est actif

## 4. Gestion des documents

### Types de documents

Les documents requis varient selon l'étape :
- Documents d'identité
- Justificatifs administratifs
- Documents financiers
- Autres pièces justificatives

### Validation des documents

Chaque document téléchargé passe par un processus de validation :
1. Vérification du format
2. Validation de la lisibilité
3. Vérification des informations
4. Approbation administrative

## 5. Sécurité et permissions

### Niveaux d'accès

Différents rôles ont différents niveaux d'accès :
- Administrateurs : accès complet
- Agents : modification limitée
- Clients : consultation et upload
- Prospects : consultation limitée

### Audit et traçabilité

Toutes les actions sont tracées :
- Changements d'étape
- Modifications de statut
- Uploads de documents
- Validations administratives

## 6. Intégration système

### Points d'intégration

Le workflow s'intègre avec :
1. Système d'authentification
2. Gestion documentaire
3. Système de paiement
4. Système de notification
5. Module de reporting

### Events système

Des événements sont émis à chaque :
- Changement d'étape
- Modification de statut
- Upload de document
- Validation importante
