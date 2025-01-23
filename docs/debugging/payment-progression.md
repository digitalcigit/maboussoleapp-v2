# Guide de Débogage : Progression des Paiements

## Problème : Bouton "Continuer" visible malgré paiement incomplet

### Symptômes
- Le bouton "Continuer" apparaît alors que le paiement n'est pas complet
- Possibilité de passer à l'étape suivante avec un paiement partiel

### Vérifications
1. Vérifier le statut actuel du dossier
   - Doit être `paiement_scolarite`

2. Vérifier les montants
   - `tuition_total_amount` : Montant total à payer
   - `tuition_paid_amount` : Montant déjà payé
   - La progression doit être à 100% pour permettre la progression

3. Vérifier la méthode `isTuitionFullyPaid()`
   ```php
   public function isTuitionFullyPaid(): bool
   {
       return $this->tuition_paid_amount >= $this->tuition_total_amount;
   }
   ```

### Solutions Courantes
1. Si le bouton apparaît alors qu'il ne devrait pas :
   - Vérifier que les montants sont correctement enregistrés
   - S'assurer que la méthode `canProceedToNextStep()` est appelée

2. Si le paiement est complet mais le bouton n'apparaît pas :
   - Vérifier que le statut est bien `paiement_scolarite`
   - Vérifier qu'il n'y a pas d'erreur d'arrondi dans les montants

### Prévention
- Toujours saisir les montants avec précision
- Vérifier la progression du paiement avant de changer le statut
- Utiliser les méthodes de validation intégrées
