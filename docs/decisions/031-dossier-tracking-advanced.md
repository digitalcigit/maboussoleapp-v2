# ADR 031: Système avancé de suivi des dossiers

## Contexte

Le système actuel ne permet pas d'avoir une traçabilité complète sur la création et la gestion des dossiers. Cette limitation pose plusieurs problèmes :

1. Impossibilité d'identifier qui a créé un dossier spécifique
2. Difficulté à mesurer les performances individuelles
3. Manque de responsabilisation dans le suivi des dossiers
4. Absence de métriques précises sur les activités des utilisateurs

Pour résoudre ces problèmes, nous avons besoin d'un système qui permette de suivre qui crée et qui gère chaque dossier.

## Décision

Nous avons décidé d'implémenter un système de suivi des dossiers avec les éléments suivants :

1. Ajout d'un champ `created_by` dans la table `dossiers` pour identifier l'utilisateur qui a créé chaque dossier
2. Utilisation du champ existant `assigned_to` pour indiquer l'utilisateur actuellement responsable du dossier
3. Configuration automatique du champ `created_by` lors de la création d'un dossier via l'interface Filament
4. Définition de relations explicites dans le modèle entre dossiers et utilisateurs via `creator()` et `assignedTo()`
5. Ajout d'interfaces administratives pour visualiser et filtrer les dossiers par créateur et responsable

## Conséquences

### Positives

1. **Traçabilité complète** : Chaque dossier est désormais lié à son créateur, permettant une traçabilité complète des actions.
2. **Métriques précises** : Possibilité de générer des métriques précises sur les performances individuelles et collectives.
3. **Responsabilisation** : Les utilisateurs sont responsabilisés par le suivi explicite de leurs actions.
4. **Gestion d'équipe améliorée** : Les superviseurs peuvent mieux répartir et suivre les charges de travail.
5. **Base pour fonctionnalités futures** : Cette structure forme une base solide pour d'autres fonctionnalités comme les notifications et l'historique des changements.

### Négatives

1. **Migration des données existantes** : Nécessité de mettre à jour les dossiers existants sans information de création.
2. **Complexité accrue** : Le modèle de données devient légèrement plus complexe, nécessitant des jointures supplémentaires.

### Techniques

1. **Nouvelle migration** : Création d'une migration pour ajouter le champ `created_by` à la table des dossiers.
2. **Modification du modèle** : Mise à jour du modèle `Dossier` pour inclure les relations avec les utilisateurs.
3. **Mise à jour de l'interface** : Adaptation des formulaires Filament pour intégrer le nouveau champ.
4. **Initialisation automatique** : Configuration pour que le champ `created_by` soit automatiquement rempli avec l'ID de l'utilisateur connecté.

## Options considérées

### Option 1: Table d'audit séparée
Utilisation d'une table d'audit séparée qui enregistrerait toutes les actions sur les dossiers.

**Avantages**:
- Historique complet des changements
- Pas de modification des tables existantes

**Inconvénients**:
- Complexité d'implémentation plus élevée
- Performances potentiellement moins bonnes pour les requêtes fréquentes

### Option 2: Utilisation d'un package d'audit externe
Intégration d'un package comme `spatie/laravel-activitylog` pour gérer l'historique des actions.

**Avantages**:
- Fonctionnalité d'audit complète
- Moins de code à maintenir

**Inconvénients**:
- Dépendance externe
- Possibly overkill pour nos besoins simples de traçabilité

### Option 3: Solution adoptée (champs dédiés)
Ajout des champs `created_by` et utilisation de `assigned_to` dans la table des dossiers.

**Avantages**:
- Simple et directe
- Bonnes performances pour les requêtes fréquentes
- Facile à intégrer avec l'interface existante

**Inconvénients**:
- Historique limité (pas d'historique complet des changements d'assignation)

## Implémentation

1. **Migration de base de données**:
   ```php
   Schema::table('dossiers', function (Blueprint $table) {
       $table->foreignId('created_by')
           ->nullable()
           ->after('id')
           ->constrained('users')
           ->nullOnDelete();
   });
   ```

2. **Modification du modèle**:
   ```php
   public function creator(): BelongsTo
   {
       return $this->belongsTo(User::class, 'created_by');
   }
   ```

3. **Mise à jour du formulaire Filament**:
   ```php
   Forms\Components\Hidden::make('created_by')
       ->default(auth()->id())
   ```

## Plan de migration des données

Pour les dossiers existants, nous avons appliqué la règle suivante:
- Attribution au super-administrateur (ID 1) comme créateur par défaut

```php
DB::table('dossiers')
    ->whereNull('created_by')
    ->update(['created_by' => 1]);
```

## Statut

Adopté

## Références

- [Laravel Relationships Documentation](https://laravel.com/docs/10.x/eloquent-relationships)
- [Filament Forms Documentation](https://filamentphp.com/docs/3.x/forms)
- Décision précédente: [ADR 005 - Système d'assignation de dossiers](005-dossier-assignment-system.md)
- Décision précédente: [ADR 026 - Suivi des actions sur les dossiers](026-suivi-actions-dossiers.md)
