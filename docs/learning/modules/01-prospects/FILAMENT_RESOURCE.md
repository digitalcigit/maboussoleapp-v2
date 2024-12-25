# Documentation de la Resource Filament Prospect

## Structure des Fichiers

```yaml
ProspectResource/
  ├── ProspectResource.php              # Resource principale
  ├── RelationManagers/
  │   └── ActivitiesRelationManager.php # Gestion des activités
  └── Pages/
      ├── ListProspects.php            # Liste des prospects
      ├── CreateProspect.php           # Création
      ├── EditProspect.php             # Édition
      └── ConvertToClient.php          # Conversion en client
```

## Composants Principaux

### 1. ProspectResource
La resource principale qui définit :
- Tables et colonnes
- Formulaires
- Actions
- Navigation
- Permissions

### 2. Pages
Chaque page gère un aspect spécifique :
- **ListProspects** : Vue tabulaire des prospects
- **CreateProspect** : Formulaire de création
- **EditProspect** : Modification des données
- **ConvertToClient** : Processus de conversion

### 3. RelationManagers
- **ActivitiesRelationManager** : Gestion des activités liées

## Points d'Apprentissage

### 1. Structure Filament
- Organisation en Resources
- Séparation des pages
- Gestion des relations

### 2. Bonnes Pratiques
- Code organisé et modulaire
- Réutilisation des composants
- Validation des données

### 3. Interface Utilisateur
- Tables interactives
- Formulaires dynamiques
- Actions contextuelles

## Exemples d'Utilisation

### Navigation
```php
public static function getNavigationGroup(): ?string
{
    return __('Prospects');
}

public static function getNavigationLabel(): string
{
    return __('Prospects');
}
```

### Tables
```php
public static function table(Table $table): Table
{
    return $table
        ->columns([
            // Définition des colonnes
        ])
        ->filters([
            // Filtres de recherche
        ])
        ->actions([
            // Actions sur chaque ligne
        ]);
}
```

### Formulaires
```php
public static function form(Form $form): Form
{
    return $form
        ->schema([
            // Champs du formulaire
        ]);
}
```

## Guides Pratiques

### 1. Personnalisation des Tables
- Ajout de colonnes
- Configuration des filtres
- Définition des actions

### 2. Modification des Formulaires
- Ajout de champs
- Validation personnalisée
- Relations dynamiques

### 3. Actions Personnalisées
- Création d'actions
- Gestion des permissions
- Processus métier

## Sécurité et Permissions

### Contrôle d'Accès
```php
public static function getPermissionPrefixes(): array
{
    return [
        'view',
        'view_any',
        'create',
        'update',
        'delete',
        'delete_any',
    ];
}
```

### Validation des Actions
```php
protected function getPermissionManager()
{
    return app(PermissionManager::class);
}
```

## Tests et Qualité

### Points à Vérifier
1. Validation des données
2. Permissions utilisateur
3. Actions personnalisées
4. Relations et intégrité

### Exemples de Tests
```php
public function test_can_list_prospects()
{
    // Test de listing
}

public function test_can_create_prospect()
{
    // Test de création
}
```

## Maintenance

### Points d'Attention
1. Mise à jour Filament
2. Compatibilité PHP/Laravel
3. Performance des requêtes
4. Sécurité des données

### Bonnes Pratiques
1. Documentation du code
2. Tests réguliers
3. Revue de performance
4. Audit de sécurité
