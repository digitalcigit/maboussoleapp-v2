# ADR-009 : Flux des Statuts en Phase d'Admission

## Contexte
Dans la phase d'admission, le statut "Documents physiques reçus" était le premier statut, ce qui ne reflétait pas correctement le processus réel où il y a une période d'attente des documents physiques.

## Décision
Ajout d'un statut intermédiaire "En attente de documents physiques" avant "Documents physiques reçus".

### Flux de Statuts Mis à Jour
1. En attente de documents physiques (Nouveau)
2. Documents physiques reçus
3. Frais d'admission payés
4. Dossier soumis
5. Soumission acceptée/rejetée

### Solution Technique
1. Ajout de la constante `STATUS_WAITING_PHYSICAL_DOCS` dans le modèle Dossier
2. Mise à jour de la méthode `getValidStatusesForStep`
3. Ajout du libellé français dans `getStatusLabel`
4. Modification du statut initial lors du passage à l'étape d'admission
5. Migration pour mettre à jour les dossiers existants

## Avantages
1. Meilleure représentation du processus réel
2. Suivi plus précis de l'état des documents
3. Communication plus claire avec les clients

## Impact sur le Système
1. Ajout d'un nouveau statut dans le workflow d'admission
2. Migration des dossiers existants
3. Mise à jour de l'interface utilisateur

## Statut
Approuvé et implémenté le 20 janvier 2025
