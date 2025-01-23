# ADR-010 : Redirection après Création/Modification de Dossier

## Contexte
Après la création ou la modification d'un dossier, l'utilisateur restait sur le formulaire. Ce comportement n'était pas optimal pour le flux de travail des utilisateurs qui souhaitent généralement retourner à la liste des dossiers pour continuer leur travail.

## Décision
Modification de la redirection après les actions de création et de modification pour renvoyer l'utilisateur vers la liste des dossiers.

### Solution Technique
Implémentation de la méthode `getRedirectUrl()` dans les classes de gestion des formulaires :

```php
// Dans CreateDossier.php et EditDossier.php
protected function getRedirectUrl(): string
{
    return DossierResource::getUrl('index');
}
```

## Avantages

1. **Efficacité du Flux de Travail**
   - Retour direct à la vue d'ensemble
   - Réduction du nombre de clics nécessaires
   - Meilleure continuité dans le travail

2. **Expérience Utilisateur**
   - Navigation plus intuitive
   - Confirmation visuelle immédiate des changements dans la liste
   - Cohérence avec les attentes des utilisateurs

3. **Productivité**
   - Facilite la gestion en série des dossiers
   - Réduit le temps de navigation
   - Vue immédiate sur les autres dossiers à traiter

## Inconvénients

1. **Vérification des Modifications**
   - L'utilisateur doit retourner explicitement sur le formulaire pour vérifier les détails
   - Nécessité de faire confiance aux notifications de succès

## Alternatives Considérées

1. **Rester sur le Formulaire**
   - Plus sécurisant mais moins efficace
   - Rejeté car ralentit le flux de travail

2. **Redirection Configurable**
   - Option pour choisir la redirection
   - Rejeté pour maintenir la simplicité

## Impact sur le Système

1. **Interface Utilisateur**
   - Modification du comportement post-action
   - Notifications de succès plus importantes

2. **Code**
   - Modification des classes `CreateDossier` et `EditDossier`
   - Utilisation des fonctionnalités de redirection de Filament

## Statut
Approuvé et implémenté le 20 janvier 2025
