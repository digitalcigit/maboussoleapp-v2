# Suivi des Actions sur les Dossiers

## Vue d'ensemble
Le système intègre un mécanisme de suivi des actions sur les dossiers via le champ `last_action_at`. Ce champ permet de suivre la dernière modification significative apportée à un dossier.

## Implémentation technique

### Structure de la base de données
- Champ : `last_action_at` (timestamp)
- Table : `dossiers`
- Nullable : true
- Index : Oui (pour le tri)

### Mise à jour automatique
Le champ `last_action_at` est automatiquement mis à jour dans les cas suivants :

1. **Modification individuelle d'un dossier**
   - Via la méthode `afterSave()` dans `EditDossier.php`
   - Mise à jour avec la date et l'heure actuelles

2. **Changement de statut en masse**
   - Via l'action en masse dans `DossierResource.php`
   - Mise à jour lors de la modification du statut

3. **Progression d'étape**
   - Via la méthode `progressToNextStep()` dans le modèle `Dossier`
   - Mise à jour lors du passage à l'étape suivante

### Affichage dans l'interface
- Colonne "Dernière action" dans la liste des dossiers
- Format d'affichage : "dd/mm/yyyy HH:mm"
- Triable par défaut du plus récent au plus ancien

## Utilisation

### Pour les développeurs
```php
// Mise à jour manuelle
$dossier->update(['last_action_at' => now()]);

// Vérification de la dernière action
$lastAction = $dossier->last_action_at;
```

### Pour les utilisateurs
- La colonne "Dernière action" permet de :
  - Suivre l'activité sur les dossiers
  - Identifier les dossiers nécessitant une attention
  - Trier les dossiers par ordre chronologique d'activité
