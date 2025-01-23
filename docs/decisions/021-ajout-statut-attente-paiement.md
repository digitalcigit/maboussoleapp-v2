# ADR 021 : Ajout du statut "Attente de Paiements des Frais d'admission" et clarification des statuts

Date : 21/01/2025
Statut : Accepté
Décideurs : Équipe de développement, Client

## Contexte

Dans le workflow actuel de gestion des dossiers, deux modifications sont nécessaires :
1. Il manquait une étape intermédiaire entre la réception des documents physiques et le paiement effectif des frais d'admission
2. Le statut "Frais d'admission payés" ne reflétait pas clairement que le dossier était en cours de soumission

## Décision

Nous avons décidé de :
1. Ajouter un nouveau statut "Attente de Paiements des Frais d'admission"
2. Renommer le statut "Frais d'admission payés" en "Frais d'admission Payé - Soumission en cours"

Le workflow mis à jour pour l'étape d'admission devient :
1. En attente de documents physiques
2. Documents physiques reçus
3. Attente de Paiements des Frais d'admission (NOUVEAU)
4. Frais d'admission Payé - Soumission en cours (MODIFIÉ)
5. Dossier soumis
6. Soumission acceptée/rejetée

## Conséquences

### Positives
- Meilleur suivi des dossiers en attente de paiement
- Clarification du processus pour les utilisateurs
- Meilleure compréhension de l'état "Frais payés" qui indique maintenant clairement que la soumission est en cours
- Facilite le reporting sur les paiements en attente

### Négatives
- Nécessite une étape supplémentaire dans le workflow
- Les utilisateurs devront être formés sur ces changements de statuts

## Implémentation

1. Ajout de la constante `STATUS_WAITING_ADMISSION_PAYMENT`
2. Modification du libellé de `STATUS_ADMISSION_PAID`
3. Mise à jour des méthodes de gestion des statuts
4. Intégration dans l'interface utilisateur Filament
