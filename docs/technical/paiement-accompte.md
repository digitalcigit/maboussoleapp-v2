# Gestion du Paiement de l'Accompte

## Vue d'ensemble
Le système de paiement a été modifié pour se concentrer sur le paiement de l'accompte plutôt que sur le montant total de la scolarité. Cette modification reflète mieux les besoins métier où la validation du dossier dépend du paiement complet de l'accompte.

## Structure de données
- `montant_total_scolarite`: Montant indicatif de la scolarité totale
- `montant_accompte`: Montant de l'accompte requis (champ clé)
- `montant_paye`: Montant actuellement payé
- `progression_paiement`: Calculé comme (montant_paye / montant_accompte) * 100

## Logique métier
1. Le statut "Paiement Complété" est atteint quand montant_paye >= montant_accompte
2. La progression est calculée par rapport à l'accompte et non au montant total
3. Le montant total de la scolarité reste visible mais est purement informatif

## Interface utilisateur
- Affichage clair de l'accompte requis
- Barre de progression basée sur le pourcentage de l'accompte payé
- Indication visuelle que le montant total est informatif

## Validation
- L'accompte est un champ obligatoire
- Le montant total de la scolarité est optionnel
- La progression ne peut pas dépasser 100%

## Migration des données
Une migration a été créée pour ajouter le champ montant_accompte à la table dossiers.
Les dossiers existants devront être mis à jour manuellement avec les montants d'accompte corrects.
