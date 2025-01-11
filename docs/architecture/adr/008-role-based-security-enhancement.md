# ADR 008 : Amélioration de la Sécurité Basée sur les Rôles

## Contexte
- Date : 2025-01-08
- Statut : Accepté
- Décideurs : Équipe de développement

## Problème
Les managers avaient la capacité de créer des super-admins, créant ainsi un risque de sécurité potentiel par escalade de privilèges non contrôlée.

## Options Considérées
1. **Validation Backend uniquement**
   - Utiliser uniquement les politiques Laravel
   - Simple mais laisse une interface trompeuse

2. **Validation Frontend uniquement**
   - Masquer les options dans l'interface
   - Risque de contournement via API

3. **✅ Approche Multi-couches** (Choisie)
   - Validation frontend (Filament) et backend (Laravel)
   - Interface cohérente avec les permissions
   - Protection complète à tous les niveaux

## Décision
Nous avons choisi l'approche multi-couches qui combine :
1. Filtrage des options dans le formulaire Filament
2. Validation via les politiques Laravel
3. Interface utilisateur cohérente avec les permissions

## Conséquences
### Positives
- Sécurité renforcée à tous les niveaux
- UX améliorée avec des options cohérentes
- Documentation claire des restrictions

### Négatives
- Complexité accrue du code
- Nécessité de maintenir deux couches de validation

## Implémentation
```php
// UserResource.php
Select::make('roles')
    ->multiple()
    ->relationship('roles', 'name')
    ->preload()
    ->options(function () {
        if (auth()->user()->hasRole('manager')) {
            return Role::where('name', '!=', 'super-admin')
                      ->pluck('name', 'id');
        }
        return Role::pluck('name', 'id');
    })
```

## Notes
- Cette approche suit les meilleures pratiques de Laravel et Filament
- La documentation a été mise à jour dans PROCESS_QUALITE.md
- Un guide spécifique a été créé dans FILAMENT_GUIDE.md
