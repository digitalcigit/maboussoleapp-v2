# ADR-008 : Confirmation de Suppression des Documents

## Contexte
La suppression des documents dans le formulaire de dossier se faisait sans confirmation préalable, créant un risque de suppression accidentelle de documents importants.

## Décision
Nous avons décidé d'ajouter une confirmation obligatoire avant toute suppression de document dans le formulaire de dossier.

### Solution Technique
Utilisation de la fonctionnalité de confirmation de Filament dans le composant Repeater :

```php
->deleteAction(
    fn (Forms\Components\Actions\Action $action) => $action
        ->requiresConfirmation()
        ->modalHeading('Supprimer le document')
        ->modalDescription('Êtes-vous sûr de vouloir supprimer ce document ? Cette action est irréversible.')
        ->modalSubmitActionLabel('Oui, supprimer')
        ->modalCancelActionLabel('Annuler')
)
```

## Avantages

1. **Sécurité**
   - Prévention des suppressions accidentelles
   - Confirmation explicite requise
   - Message clair sur l'irréversibilité

2. **Expérience Utilisateur**
   - Interface plus sécurisée
   - Messages explicites
   - Actions clairement identifiées

3. **Intégrité des Données**
   - Réduction des erreurs humaines
   - Protection des documents importants
   - Traçabilité des actions

## Inconvénients

1. **Interaction**
   - Étape supplémentaire pour la suppression
   - Légère augmentation du temps de traitement

## Impact sur le Système

1. **Interface Utilisateur**
   - Ajout d'une boîte de dialogue de confirmation
   - Messages plus explicites

2. **Code**
   - Modification du composant Repeater dans DossierResource
   - Utilisation des fonctionnalités de confirmation de Filament

## Alternatives Considérées

1. **Corbeille Temporaire**
   - Plus complexe à implémenter
   - Nécessite une gestion de la durée de rétention
   - Rejetée pour la simplicité

2. **Journalisation des Suppressions**
   - Ne prévient pas les suppressions accidentelles
   - Complexité supplémentaire
   - Rejetée car ne résout pas le problème principal

## Statut
Approuvé et implémenté le 20 janvier 2025
