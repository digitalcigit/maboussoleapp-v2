# ADR-011 : Flux des Statuts en Phase de Paiement

## Contexte
Dans la phase de paiement, le statut "Frais d'agence payés" était le premier statut, ce qui ne reflétait pas correctement le processus réel où il y a une période d'attente du paiement.

## Décision
Ajout d'un statut intermédiaire "En attente de paiement des frais d'agence" avant "Frais d'agence payés".

### Flux de Statuts Mis à Jour
1. En attente de paiement des frais d'agence (Nouveau)
2. Frais d'agence payés
3. Paiement partiel scolarité
4. Paiement total scolarité
5. Dossier abandonné (optionnel)

### Solution Technique
1. Ajout de la constante `STATUS_WAITING_AGENCY_PAYMENT` dans le modèle Dossier
2. Mise à jour de la méthode `getValidStatusesForStep`
3. Ajout du libellé français dans `getStatusLabel`
4. Modification du statut initial lors du passage à l'étape de paiement
5. Migration pour mettre à jour les dossiers existants

## Avantages
1. Meilleure représentation du processus réel
2. Suivi plus précis des paiements
3. Communication plus claire avec les clients

## Impact sur le Système
1. Ajout d'un nouveau statut dans le workflow de paiement
2. Migration des dossiers existants
3. Mise à jour de l'interface utilisateur

## Statut
Approuvé et implémenté le 20 janvier 2025
