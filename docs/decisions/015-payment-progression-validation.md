# ADR-015 : Validation de la Progression des Paiements

## Contexte
Le système permettait de passer à l'étape suivante dès que le statut était "Paiement de la scolarité", sans vérifier si le montant total était effectivement payé. Cela pouvait créer des situations où un dossier progressait malgré un paiement incomplet.

## Problème
- Un dossier pouvait passer à l'étape suivante avec un paiement partiel
- Le bouton "Continuer" était visible même si le paiement n'était pas complet
- Aucune validation du montant payé n'était effectuée

## Solution
Modification de la logique de progression pour inclure une double validation :
1. Vérification du statut (`STATUS_TUITION_PAYMENT`)
2. Vérification que le paiement est complet (`isTuitionFullyPaid()`)

### Implémentation
```php
public function canProceedToNextStep(): bool
{
    return match ($this->current_step) {
        self::STEP_PAYMENT => 
            $this->current_status === self::STATUS_TUITION_PAYMENT 
            && $this->isTuitionFullyPaid(),
        // ...
    };
}
```

## Conséquences
### Positives
- Meilleure intégrité des données
- Prévention des progressions prématurées
- Validation plus stricte des paiements

### Négatives
- Nécessité de s'assurer que tous les montants sont correctement enregistrés
- Possible besoin de revoir les dossiers existants

## Notes
Cette modification renforce la logique métier en s'assurant qu'un dossier ne peut progresser qu'avec un paiement complet, améliorant ainsi la fiabilité du suivi des paiements.
