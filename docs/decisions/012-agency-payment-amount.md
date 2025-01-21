# ADR-012 : Champ de Montant pour les Frais d'Agence

## Contexte
Lors du paiement des frais d'agence, il est nécessaire de capturer le montant exact payé en FCFA. Ce montant doit être saisi uniquement lorsque le statut "Frais d'agence payés" est sélectionné.

## Décision
Ajout d'un champ dynamique pour le montant des frais d'agence qui apparaît uniquement lorsque le statut approprié est sélectionné.

### Solution Technique
1. Ajout d'une colonne `agency_payment_amount` dans la table dossiers
2. Configuration du champ dans le modèle Dossier
3. Implémentation d'un champ conditionnel dans le formulaire

```php
Forms\Components\TextInput::make('agency_payment_amount')
    ->label('Montant des frais d\'agence')
    ->numeric()
    ->prefix('FCFA')
    ->maxValue(999999.99)
    ->minValue(0)
    ->visible(fn (Forms\Get $get): bool => 
        $get('current_status') === Dossier::STATUS_AGENCY_PAID
    )
    ->required(fn (Forms\Get $get): bool => 
        $get('current_status') === Dossier::STATUS_AGENCY_PAID
    )
```

## Avantages
1. Capture précise des montants payés en FCFA
2. Interface utilisateur contextuelle
3. Validation des données au moment de la saisie
4. Cohérence avec la logique de statut existante

## Impact sur le Système
1. Nouvelle colonne dans la base de données
2. Champ conditionnel dans le formulaire
3. Validation supplémentaire des données
4. Standardisation de la devise en FCFA

## Alternatives Considérées
1. **Champ Toujours Visible**
   - Rejeté car pourrait créer de la confusion
   - Ne respecte pas la logique métier

2. **Formulaire Séparé**
   - Rejeté car ajouterait de la complexité
   - Moins intuitif pour les utilisateurs

## Statut
Approuvé et implémenté le 20 janvier 2025
Testé et validé le 20 janvier 2025
