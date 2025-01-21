# Décision : Système d'Assignation des Dossiers

## Contexte
Le système nécessite une gestion claire des responsabilités pour chaque dossier. Il est important de savoir quel conseiller est en charge de quel dossier, tout en permettant aux managers de superviser et réassigner les dossiers si nécessaire.

## Décision
Nous avons implémenté un système d'assignation des dossiers avec les caractéristiques suivantes :

1. Assignation Automatique
   - Chaque dossier est automatiquement assigné à son créateur
   - Le prospect associé est également assigné au même conseiller

2. Droits d'Assignation
   - Conseillers : peuvent uniquement créer des dossiers qui leur sont assignés
   - Managers : peuvent assigner les dossiers à n'importe quel conseiller
   - Super-admin : contrôle total sur l'assignation

3. Réassignation
   - Seuls les managers et super-admin peuvent réassigner les dossiers
   - Les conseillers ne peuvent pas modifier l'assignation

## Implémentation Technique

1. Base de Données
   - Ajout du champ `assigned_to` dans la table `dossiers`
   - Clé étrangère vers la table `users`
   - Index pour optimiser les requêtes

2. Permissions
   - `view_dossiers`
   - `create_dossiers`
   - `edit_dossiers`
   - `delete_dossiers`
   - `assign_dossiers`
   - `manage_dossiers`

3. Interface Utilisateur
   - Champ d'assignation visible uniquement pour les rôles autorisés
   - Options d'assignation filtrées selon le rôle
   - Désactivation du champ en édition pour les non-autorisés

## Avantages

1. **Clarté des Responsabilités**
   - Chaque dossier a un responsable clairement identifié
   - Pas de dossiers "orphelins"

2. **Hiérarchie Respectée**
   - Les managers gardent le contrôle sur l'assignation
   - Les conseillers se concentrent sur leurs dossiers

3. **Traçabilité**
   - Historique clair des responsabilités
   - Facilite le suivi et la gestion

## Inconvénients Potentiels

1. **Rigidité**
   - Les conseillers ne peuvent pas s'échanger directement les dossiers
   - Nécessite l'intervention d'un manager

2. **Complexité Accrue**
   - Logique d'autorisation plus complexe
   - Plus de code à maintenir

## Impact sur le Système

1. **Performance**
   - Impact minimal grâce à l'indexation
   - Requêtes optimisées pour le tableau de bord

2. **Sécurité**
   - Contrôle d'accès granulaire
   - Protection contre les modifications non autorisées

3. **Maintenance**
   - Documentation claire des règles d'assignation
   - Tests automatisés pour les scénarios critiques

## Alternatives Considérées

1. **Assignation Libre**
   - Permettre aux conseillers de s'échanger les dossiers
   - Rejeté pour maintenir le contrôle managérial

2. **Sans Assignation**
   - Dossiers accessibles à tous
   - Rejeté pour la traçabilité et la responsabilité

## Statut
Approuvé et implémenté le 19 janvier 2025
