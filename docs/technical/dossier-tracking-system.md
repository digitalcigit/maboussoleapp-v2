# Système de suivi des dossiers (Dossier Tracking System)

## Vue d'ensemble
Le système de suivi des dossiers permet de tracer qui a créé et qui gère chaque dossier dans l'application. Cette fonctionnalité améliore la responsabilisation, facilite la gestion des équipes et permet de générer des métriques précises sur les performances.

## Composants clés

### 1. Champ `created_by`
- **Objectif** : Enregistrer l'identifiant de l'utilisateur qui a créé le dossier
- **Type** : `foreignId` référençant la table `users`
- **Comportement** : Automatiquement rempli lors de la création d'un dossier

### 2. Relation `assignedTo`
- **Objectif** : Référencer l'utilisateur actuellement responsable du dossier
- **Type** : Relation BelongsTo vers le modèle User
- **Comportement** : Peut être mis à jour au fil du temps lorsque la responsabilité du dossier change

## Implémentation technique

### Migration de base de données

La migration `2025_02_13_093638_add_created_by_to_dossiers_table.php` ajoute le champ `created_by` à la table `dossiers` :

```php
public function up(): void
{
    Schema::table('dossiers', function (Blueprint $table) {
        $table->foreignId('created_by')
            ->nullable()
            ->after('id')
            ->constrained('users')
            ->nullOnDelete();
    });

    // Mettre à jour les dossiers existants avec l'ID du super admin
    DB::table('dossiers')
        ->whereNull('created_by')
        ->update(['created_by' => 1]); // Supposant que l'ID 1 est le super admin
}
```

### Modèle Dossier

Le modèle `Dossier` a été mis à jour pour inclure les relations avec les utilisateurs :

```php
/**
 * Get the user that the dossier is assigned to
 */
public function assignedTo(): BelongsTo
{
    return $this->belongsTo(User::class, 'assigned_to');
}

/**
 * Get the user who created the dossier
 */
public function creator(): BelongsTo
{
    return $this->belongsTo(User::class, 'created_by');
}
```

### Configuration dans Filament

Dans la classe `DossierResource.php`, les champs correspondants ont été ajoutés :

```php
Forms\Components\Select::make('assigned_to')
    ->label('Assigné à')
    ->relationship('assignedTo', 'name')
    ->required(),
Forms\Components\Hidden::make('created_by')
    ->default(auth()->id()),
```

Le champ `created_by` est défini comme un champ caché qui prend automatiquement l'ID de l'utilisateur authentifié lors de la création.

## Flux de travail

### Création d'un dossier
1. Un utilisateur crée un nouveau dossier via le formulaire Filament
2. Le champ `created_by` est automatiquement rempli avec l'ID de l'utilisateur authentifié
3. Le champ `assigned_to` est spécifié manuellement par l'utilisateur

### Réassignation d'un dossier
1. Un utilisateur avec les permissions nécessaires modifie le dossier
2. Le champ `assigned_to` est mis à jour pour refléter le nouvel utilisateur responsable
3. Le champ `created_by` reste inchangé, préservant l'historique de création

## Avantages et utilisations

### Métriques et tableaux de bord
- Nombre de dossiers créés par utilisateur
- Performances des gestionnaires de dossiers
- Répartition des charges de travail entre les équipes

### Responsabilité et traçabilité
- Identification claire du créateur initial de chaque dossier
- Suivi des responsabilités actuelles
- Audit trail pour la conformité et la sécurité

### Gestion des équipes
- Équilibrage de la charge de travail entre les membres de l'équipe
- Identification des goulets d'étranglement dans le traitement des dossiers
- Support pour les évaluations de performance

## Considérations de performance

### Indexation
- Les champs `created_by` et `assigned_to` sont indexés pour des performances optimales
- Les jointures avec la table des utilisateurs sont optimisées

### Requêtes fréquentes
Les requêtes les plus fréquentes incluent :
```php
// Dossiers créés par un utilisateur spécifique
Dossier::where('created_by', $userId)->get();

// Dossiers assignés à un utilisateur spécifique
Dossier::where('assigned_to', $userId)->get();

// Dossiers sans assignation
Dossier::whereNull('assigned_to')->get();
```

## Extensions futures

1. **Historique d'assignation** : Enregistrer l'historique complet des assignations pour chaque dossier
2. **Métriques avancées** : Ajouter des tableaux de bord spécifiques pour l'analyse des performances
3. **Auto-assignation intelligente** : Système qui assigne automatiquement les dossiers en fonction de la charge de travail

## Problèmes connus et solutions

| Problème | Solution |
|----------|----------|
| Dossiers orphelins (sans créateur) | Migration pour assigner à un utilisateur par défaut |
| Confusion entre créateur et responsable | Interface claire distinguant les deux rôles |
| Suppression d'utilisateurs | Contrainte `nullOnDelete` pour préserver l'intégrité référentielle |
