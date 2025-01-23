# Guide Filament 3.x - Référence Rapide

Ce document sert d'aide-mémoire pour les fonctionnalités clés de Filament 3.x utilisées dans notre projet. Il référence la documentation officielle disponible dans `/docs/filament-3.x/`.

## 1. Structure des Ressources

### 1.1 Localisation des Documentations
- Documentation générale des ressources : `/docs/filament-3.x/packages/panels/docs/03-resources/`
- Documentation des pages : `/docs/filament-3.x/packages/panels/docs/04-pages.md`
- Documentation du dashboard : `/docs/filament-3.x/packages/panels/docs/05-dashboard.md`

### 1.2 Création d'une Ressource
```bash
# Ressource standard
php artisan make:filament-resource Customer

# Ressource avec modales (simple)
php artisan make:filament-resource Customer --simple
```

## 2. Autorisation et Sécurité

### 2.1 Politiques de Modèle
Filament utilise automatiquement les politiques de modèle Laravel. Méthodes importantes :
- `viewAny()` : Contrôle l'accès à la liste
- `create()` : Contrôle la création
- `update()` : Contrôle la modification
- `view()` : Contrôle la visualisation
- `delete()` et `deleteAny()` : Contrôle la suppression
- `restore()` et `restoreAny()` : Contrôle la restauration (soft delete)

Documentation : `/docs/filament-3.x/packages/panels/docs/03-resources/01-getting-started.md` (section Authorization)

### 2.2 Contrôle d'Accès au Panel
```php
class User extends Authenticatable implements FilamentUser
{
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole('admin');
    }
}
```
Documentation : `/docs/filament-3.x/packages/panels/docs/08-users.md`

## 3. Widgets et Dashboard

### 3.1 Structure du Dashboard
- Configuration dans `AdminPanelProvider`
- Widgets organisés en sections (header/footer)
- Support du responsive avec `getColumns()`

Documentation : `/docs/filament-3.x/packages/panels/docs/05-dashboard.md`

### 3.2 Widgets Personnalisés
```php
protected function getHeaderWidgets(): array
{
    return [
        StatsOverview::class,
        // ...
    ];
}
```

## 4. Bonnes Pratiques

### 4.1 Sécurité
- Utiliser les politiques de modèle pour l'autorisation
- Ne pas exposer de données sensibles via `mutateFormDataBeforeFill()`
- Implémenter `FilamentUser` pour le contrôle d'accès

### 4.2 Organisation du Code
- Un fichier de ressource par modèle
- Séparation des pages dans le dossier `Pages`
- Utilisation des widgets pour les fonctionnalités réutilisables

## 5. Personnalisation

### 5.1 Labels et Navigation
```php
protected static ?string $navigationLabel = 'Utilisateurs';
protected static ?string $navigationIcon = 'heroicon-o-users';
protected static ?string $navigationGroup = 'Administration';
protected static ?int $navigationSort = 1;
```

### 5.2 Formulaires et Tables
- Utilisation de `form()` pour définir les champs
- Utilisation de `table()` pour définir les colonnes
- Support des actions personnalisées

## 6. Ressources Utiles

### 6.1 Documentation Officielle
- [Documentation des ressources](file:///home/dcidev/CascadeProjects/maboussoleapp-v2/docs/filament-3.x/packages/panels/docs/03-resources/01-getting-started.md)
- [Documentation des utilisateurs](file:///home/dcidev/CascadeProjects/maboussoleapp-v2/docs/filament-3.x/packages/panels/docs/08-users.md)
- [Documentation de la sécurité](file:///home/dcidev/CascadeProjects/maboussoleapp-v2/docs/filament-3.x/packages/panels/docs/03-resources/11-security.md)

### 6.2 Fichiers Importants du Projet
- `app/Filament/Resources/` : Ressources Filament
- `app/Policies/` : Politiques d'autorisation
- `app/Providers/Filament/` : Configuration du panel
