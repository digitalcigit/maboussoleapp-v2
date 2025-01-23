# ADR 018 : Réorganisation du Menu de Navigation

Date : 20/01/2025
Statut : Accepté
Décideurs : Équipe de développement

## Contexte
Le menu de navigation nécessitait une restructuration pour une meilleure organisation et une meilleure expérience utilisateur. Les dossiers devaient être regroupés sous leur propre section et placés stratégiquement dans la hiérarchie du menu.

## Décision
Nous avons décidé de :
1. Créer un nouveau groupe de navigation "Gestion des dossiers"
2. Positionner ce groupe directement après le tableau de bord
3. Maintenir la structure existante pour les autres sections (CRM, Administration)

### Nouvelle Structure du Menu
```
1. Tableau de bord
2. Gestion des dossiers
   └── Dossiers
3. CRM
   ├── Prospects
   ├── Clients
   └── Activités
4. Administration
   └── Utilisateurs
```

### Implémentation
- Utilisation de `navigationGroup` pour créer le groupe "Gestion des dossiers"
- Configuration de `navigationGroupSort` pour contrôler l'ordre des groupes
- Maintien des badges et icônes existants

## Conséquences

### Positives
- Meilleure organisation visuelle du menu
- Séparation claire des fonctionnalités
- Navigation plus intuitive
- Cohérence avec la logique métier

### Négatives
- Nécessité de maintenir l'ordre de tri lors de l'ajout de nouvelles sections

## Notes Techniques
```php
// Configuration dans DossierResource
protected static ?string $navigationGroup = 'Gestion des dossiers';
protected static ?int $navigationGroupSort = 1;

// Configuration dans les ressources CRM
protected static ?string $navigationGroup = 'CRM';
protected static ?int $navigationGroupSort = 2;
```
