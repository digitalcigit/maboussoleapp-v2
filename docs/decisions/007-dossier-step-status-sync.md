# ADR-007 : Synchronisation Dynamique des Statuts de Dossier

## Contexte
Les statuts disponibles dans le formulaire de dossier ne s'adaptaient pas automatiquement en fonction de l'étape sélectionnée, ce qui pouvait mener à des incohérences dans les données.

## Décision
Nous avons implémenté une synchronisation dynamique entre l'étape du dossier et ses statuts possibles, en utilisant les fonctionnalités de Filament et les méthodes existantes du modèle Dossier.

### Solution Technique
1. Utilisation des propriétés `live()` de Filament pour la réactivité
2. Implémentation d'un hook `afterStateUpdated` pour la synchronisation
3. Utilisation des méthodes du modèle pour la cohérence des données

## Implémentation

### Champ de Sélection d'Étape
```php
Forms\Components\Select::make('current_step')
    ->label('Étape actuelle')
    ->options([
        Dossier::STEP_ANALYSIS => 'Analyse de dossier',
        Dossier::STEP_ADMISSION => 'Admission',
        Dossier::STEP_PAYMENT => 'Paiement',
        Dossier::STEP_VISA => 'Visa',
    ])
    ->default(Dossier::STEP_ANALYSIS)
    ->live()
    ->afterStateUpdated(fn ($state, Forms\Set $set) => 
        $set('current_status', Dossier::getValidStatusesForStep($state)[0] ?? null)
    )
    ->required()
```

### Champ de Sélection de Statut
```php
Forms\Components\Select::make('current_status')
    ->label('Statut actuel')
    ->options(function (Forms\Get $get) {
        $step = $get('current_step');
        if (!$step) return [];
        
        return collect(Dossier::getValidStatusesForStep($step))
            ->mapWithKeys(fn ($status) => [
                $status => Dossier::getStatusLabel($status)
            ])
            ->toArray();
    })
    ->required()
    ->live()
```

## Avantages

1. **Cohérence des Données**
   - Validation automatique des statuts selon l'étape
   - Prévention des erreurs de saisie
   - Intégrité des données garantie

2. **Expérience Utilisateur**
   - Interface réactive et intuitive
   - Mise à jour instantanée des options
   - Réduction des erreurs utilisateur

3. **Maintenance**
   - Code centralisé dans le modèle Dossier
   - Utilisation des méthodes existantes
   - Facilité de modification des statuts

## Inconvénients

1. **Performance**
   - Légère augmentation des requêtes AJAX
   - Temps de chargement initial du formulaire

2. **Complexité**
   - Logique plus complexe dans le formulaire
   - Dépendance accrue aux méthodes du modèle

## Impact sur le Système

1. **Interface Utilisateur**
   - Modification du comportement du formulaire
   - Amélioration de la validation des données

2. **Code**
   - Modification du DossierResource
   - Utilisation accrue des fonctionnalités Filament
   - Meilleure utilisation des méthodes du modèle

## Alternatives Considérées

1. **Validation Côté Serveur**
   - Plus simple à implémenter
   - Moins réactive pour l'utilisateur
   - Rejetée pour l'expérience utilisateur

2. **États Statiques**
   - Performance légèrement meilleure
   - Risque d'incohérence des données
   - Rejetée pour la fiabilité

## Statut
Approuvé et implémenté le 19 janvier 2025
