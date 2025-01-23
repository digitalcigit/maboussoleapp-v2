# Documentation Technique : Système d'Assignation des Dossiers

## Structure de la Base de Données

### Table `dossiers`
```sql
ALTER TABLE dossiers
ADD COLUMN assigned_to BIGINT UNSIGNED NULL,
ADD CONSTRAINT dossiers_assigned_to_foreign FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
ADD INDEX idx_assigned_to (assigned_to);
```

## Permissions et Rôles

### Permissions
```php
'view_dossiers'    // Voir les dossiers
'create_dossiers'  // Créer des dossiers
'edit_dossiers'    // Modifier des dossiers
'delete_dossiers'  // Supprimer des dossiers
'assign_dossiers'  // Assigner des dossiers
'manage_dossiers'  // Gérer tous les aspects des dossiers
```

### Attribution des Permissions par Rôle

#### Super Admin
- Toutes les permissions

#### Manager
- view_dossiers
- create_dossiers
- edit_dossiers
- assign_dossiers
- manage_dossiers

#### Conseiller
- view_dossiers
- create_dossiers
- edit_dossiers

## Logique d'Assignation

### Création de Dossier
```php
protected function mutateFormDataBeforeCreate(array $data): array
{
    // Assignation automatique si non spécifié
    if (!isset($data['assigned_to'])) {
        $data['assigned_to'] = auth()->id();
    }
    
    // ... reste de la logique
}
```

### Vérification des Permissions
```php
public function assign(User $user): bool
{
    return $user->hasRole('super-admin') || $user->hasRole('manager');
}

public function reassign(User $user, Dossier $dossier): bool
{
    if ($user->hasRole('super-admin')) {
        return true;
    }

    if ($user->hasRole('manager')) {
        return $dossier->assigned_to === $user->id || 
               $dossier->assignedTo->hasRole('conseiller');
    }

    return false;
}
```

## Interface Utilisateur

### Champ d'Assignation
```php
Forms\Components\Select::make('assigned_to')
    ->label('Assigné à')
    ->relationship('assignedTo', 'name')
    ->options(function () {
        if (auth()->user()->hasRole('super-admin')) {
            return User::all()->pluck('name', 'id');
        }
        
        if (auth()->user()->hasRole('manager')) {
            return User::role(['conseiller', 'manager'])
                ->where(function ($query) {
                    $query->role('conseiller')
                        ->orWhere('id', auth()->id());
                })
                ->pluck('name', 'id');
        }
        
        return User::where('id', auth()->id())->pluck('name', 'id');
    })
    ->default(fn () => auth()->id())
    ->required()
    ->visible(fn () => auth()->user()->can('assign', Dossier::class))
    ->disabled(fn (string $operation) => 
        $operation === 'edit' && 
        !auth()->user()->can('reassign', Dossier::class)
    )
```

## Bonnes Pratiques

1. **Création de Dossier**
   - Toujours vérifier l'assignation automatique
   - Synchroniser l'assignation avec le prospect

2. **Modification d'Assignation**
   - Vérifier les permissions avant toute modification
   - Notifier les utilisateurs concernés

3. **Requêtes et Performance**
   - Utiliser les index pour les requêtes d'assignation
   - Précharger la relation assignedTo si nécessaire
