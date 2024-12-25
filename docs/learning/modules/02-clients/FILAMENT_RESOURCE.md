# Documentation de la Resource Filament Client

## Structure des Fichiers

```yaml
ClientResource/
  ├── ClientResource.php                # Resource principale
  ├── RelationManagers/
  │   └── ActivitiesRelationManager.php # Gestion des activités
  └── Pages/
      ├── ListClients.php              # Liste des clients
      ├── CreateClient.php             # Création
      ├── EditClient.php               # Édition
      └── ViewClient.php               # Vue détaillée
```

## Composants

### 1. Resource Principale (ClientResource.php)
- Configuration globale
- Définition des formulaires
- Configuration des tables
- Actions personnalisées

### 2. Pages Spécifiques
- **ListClients** : Vue tabulaire
- **CreateClient** : Nouveau client
- **EditClient** : Modification
- **ViewClient** : Détails complets

### 3. Gestionnaire de Relations
- **ActivitiesRelationManager** : Suivi des activités

## Fonctionnalités Clés

### 1. Tables et Colonnes
```php
public static function table(Table $table): Table
{
    return $table
        ->columns([
            // Informations client
            TextColumn::make('client_number'),
            TextColumn::make('full_name'),
            
            // Statuts
            BadgeColumn::make('status')
                ->colors([...]),
            BadgeColumn::make('visa_status')
                ->colors([...]),
            
            // Informations financières
            TextColumn::make('total_amount'),
            TextColumn::make('paid_amount'),
            
            // Dates
            DateColumn::make('created_at'),
        ]);
}
```

### 2. Formulaires
```php
public static function form(Form $form): Form
{
    return $form
        ->schema([
            // Informations de base
            TextInput::make('client_number'),
            TextInput::make('passport_number'),
            DatePicker::make('passport_expiry'),
            
            // Statuts
            Select::make('status')
                ->options([...]),
            Select::make('visa_status')
                ->options([...]),
            
            // Informations financières
            TextInput::make('total_amount')
                ->numeric(),
            TextInput::make('paid_amount')
                ->numeric(),
        ]);
}
```

### 3. Actions Personnalisées
```php
public static function getActions(): array
{
    return [
        Actions\CreateAction::make(),
        Actions\EditAction::make(),
        Actions\ViewAction::make(),
    ];
}
```

## Points d'Apprentissage

### 1. Structure Filament
- Organisation des ressources
- Configuration des pages
- Gestion des relations

### 2. Interface Utilisateur
- Tables interactives
- Formulaires dynamiques
- Actions contextuelles

### 3. Logique Métier
- Validation des données
- Workflow client
- Gestion des statuts

## Exemples d'Utilisation

### 1. Configuration de Table
```php
// Exemple de configuration de table
TextColumn::make('status')
    ->badge()
    ->color(fn (string $state): string => match ($state) {
        'actif' => 'success',
        'inactif' => 'danger',
        default => 'warning',
    });
```

### 2. Formulaire Personnalisé
```php
// Exemple de champ de formulaire
Select::make('visa_status')
    ->options(Client::getValidVisaStatuses())
    ->required()
    ->reactive()
    ->afterStateUpdated(function ($state, callable $set) {
        // Logique de mise à jour
    });
```

### 3. Action Personnalisée
```php
// Exemple d'action
Actions\Action::make('updateStatus')
    ->action(function (Client $record, array $data): void {
        $record->update([
            'status' => $data['status']
        ]);
    })
    ->form([
        Select::make('status')
            ->options(Client::getValidStatuses())
            ->required(),
    ]);
```

## Bonnes Pratiques

### 1. Organisation du Code
- Séparation des responsabilités
- Code réutilisable
- Documentation claire

### 2. Interface Utilisateur
- Labels traduits
- Messages d'erreur clairs
- Confirmations d'actions

### 3. Performance
- Chargement optimisé
- Relations eager loading
- Cache approprié

## Maintenance

### Points d'Attention
1. **Mise à jour Filament**
   - Compatibilité versions
   - Breaking changes
   - Nouvelles fonctionnalités

2. **Performance**
   - Requêtes N+1
   - Temps de chargement
   - Utilisation mémoire

3. **Sécurité**
   - Validation entrées
   - Permissions
   - Audit trail
