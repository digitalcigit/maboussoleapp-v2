# Système de Permissions - Gestion des Activités

## Permissions Requises

### Activités

| Permission | Description | Méthode de vérification |
|------------|-------------|------------------------|
| `activities.view` | Voir la liste des activités | `canViewAny()` |
| `activities.create` | Créer une nouvelle activité | `canCreate()` |
| `activities.edit` | Modifier une activité existante | `canEdit()` |
| `activities.delete` | Supprimer une activité | `canDelete()` |

## Implémentation

### Dans ActivityResource

```php
public static function canViewAny(): bool
{
    return auth()->user()->can('activities.view');
}

public static function canCreate(): bool
{
    return auth()->user()->can('activities.create');
}

public static function canEdit(Model $record): bool
{
    return auth()->user()->can('activities.edit');
}

public static function canDelete(Model $record): bool
{
    return auth()->user()->can('activities.delete');
}
```

## Vérification des Permissions

### Actions Individuelles

1. **Voir les activités**
   - Requiert : `activities.view`
   - Vérifié dans : Liste des activités

2. **Créer une activité**
   - Requiert : `activities.create`
   - Vérifié dans : Bouton "Créer"

3. **Modifier une activité**
   - Requiert : `activities.edit`
   - Vérifié dans : Menu d'actions (⋮)

4. **Supprimer une activité**
   - Requiert : `activities.delete`
   - Vérifié dans : Menu d'actions (⋮)

### Actions en Masse

1. **Suppression en masse**
   - Requiert : `activities.delete`
   - Vérifié pour chaque activité sélectionnée

2. **Mise à jour du statut en masse**
   - Requiert : `activities.edit`
   - Vérifié pour chaque activité sélectionnée

## Bonnes Pratiques

1. **Vérification systématique**
   - ✅ Vérification côté serveur obligatoire
   - ✅ Double vérification pour les actions critiques
   - ✅ Pas de contournement possible via l'interface

2. **Granularité**
   - ✅ Permissions distinctes pour chaque type d'opération
   - ✅ Pas de permission globale "tout faire"

3. **Sécurité**
   - ✅ Vérification avant chaque action
   - ✅ Messages d'erreur appropriés
   - ✅ Journalisation des tentatives non autorisées

4. **Interface Utilisateur**
   - ✅ Masquage des boutons non autorisés
   - ✅ Messages clairs sur les permissions manquantes
   - ✅ Redirection appropriée si accès refusé
