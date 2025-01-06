# Système de Permissions - Gestion des Prospects

## Permissions Requises

### Prospects

| Permission | Description | Méthode de vérification |
|------------|-------------|------------------------|
| `prospects.view` | Voir la liste des prospects | `canViewAny()` |
| `prospects.create` | Créer un nouveau prospect | `canCreate()` |
| `prospects.edit` | Modifier un prospect existant | `canEdit()` |
| `prospects.delete` | Supprimer un prospect | `canDelete()` |

### Clients (pour la conversion)

| Permission | Description | Contexte d'utilisation |
|------------|-------------|----------------------|
| `clients.create` | Convertir un prospect en client | Action de conversion |

## Implémentation

### Dans ProspectResource

```php
public static function canViewAny(): bool
{
    return auth()->user()->can('prospects.view');
}

public static function canCreate(): bool
{
    return auth()->user()->can('prospects.create');
}

public static function canEdit(Model $record): bool
{
    return auth()->user()->can('prospects.edit');
}

public static function canDelete(Model $record): bool
{
    return auth()->user()->can('prospects.delete');
}
```

### Pour l'action de conversion

```php
->visible(fn (Prospect $record): bool => 
    $record->status !== Prospect::STATUS_CONVERTED && 
    auth()->user()->can('clients.create')
)
```

## Attribution des Permissions

Les permissions sont attribuées via :
1. Rôles prédéfinis
2. Attribution directe aux utilisateurs
3. Héritage des permissions via la hiérarchie des rôles

## Bonnes Pratiques

1. **Vérification systématique**
   - Toujours vérifier les permissions avant chaque action
   - Ne pas se fier uniquement à l'interface utilisateur

2. **Granularité**
   - Permissions distinctes pour chaque type d'opération
   - Éviter les permissions trop larges

3. **Audit**
   - Journalisation des actions importantes
   - Traçabilité des modifications

4. **Sécurité**
   - Vérification côté serveur obligatoire
   - Double vérification pour les actions critiques
