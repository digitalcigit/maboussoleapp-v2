# Workflow des Dossiers

## Vue d'ensemble

Le workflow des dossiers suit un processus en plusieurs étapes qui détermine le statut de la personne (prospect ou client).

## Étapes du workflow

1. **Création initiale** (STEP_INITIAL)
   - Enregistrement des informations de base
   - La personne est considérée comme prospect

2. **Collecte des documents** (STEP_DOCUMENTS)
   - Rassemblement des documents requis
   - La personne est toujours prospect

3. **Validation du dossier** (STEP_VALIDATION)
   - Vérification des documents et informations
   - Dernière étape en tant que prospect

4. **Paiement des droits** (STEP_PAYMENT)
   - La personne devient client après paiement
   - Première étape en tant que client

5. **Traitement du dossier** (STEP_PROCESSING)
   - Traitement actif du dossier client
   - Suivi des démarches

6. **Dossier terminé** (STEP_COMPLETED)
   - Toutes les démarches sont terminées
   - Le client a atteint ses objectifs

7. **Dossier rejeté** (STEP_REJECTED)
   - Le dossier a été rejeté ou abandonné
   - Peut survenir à n'importe quelle étape

## Statuts à chaque étape

Chaque étape peut avoir l'un des statuts suivants :

- **En attente de documents** (STATUS_WAITING_DOCS)
- **En cours** (STATUS_IN_PROGRESS)
- **Bloqué** (STATUS_BLOCKED)
- **Terminé** (STATUS_COMPLETED)

## États Prospect vs Client

### Prospect
Un dossier est considéré comme prospect dans les étapes :
- Création initiale
- Collecte des documents
- Validation du dossier

### Client
Un dossier devient client à partir de l'étape :
- Paiement des droits
Et le reste pour les étapes :
- Traitement du dossier
- Dossier terminé

## Informations stockées

### Données personnelles
- Nom et prénom
- Email (unique)
- Téléphone
- Date et lieu de naissance
- Nationalité
- Adresse complète

### Données professionnelles
- Profession
- Niveau d'études
- Domaine d'activité
- Expérience professionnelle

### Données de suivi
- Montant payé
- Date de paiement
- Dernière action
- Notes

## Utilisation dans le code

### Vérification du statut
```php
// Vérifier si c'est un prospect
$dossier->isProspect();

// Vérifier si c'est un client
$dossier->isClient();
```

### Filtrage des dossiers
```php
// Obtenir tous les prospects
$prospects = Dossier::prospects()->get();

// Obtenir tous les clients
$clients = Dossier::clients()->get();
```

### Enregistrement d'un paiement
```php
// Marquer le paiement et passer en client
$dossier->enregistrerPaiement(1000.00);
```
