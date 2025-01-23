# Gestion des Accès au Portail Candidat

Date: 2025-01-22
Statut: Accepté

## Contexte

Le système nécessite un contrôle strict sur la création des comptes candidats pour le portail.

## Décision

Nous avons décidé de :
1. Désactiver l'inscription directe sur le portail candidat
2. Créer automatiquement les comptes portail lors de la création d'un prospect
3. Utiliser un observateur (ProspectObserver) pour gérer cette automatisation

## Conséquences

### Positives
- Meilleur contrôle sur la création des comptes
- Validation préalable des prospects par le personnel
- Processus automatisé et uniforme
- Pas de comptes orphelins ou non validés

### Négatives
- Les candidats ne peuvent pas s'inscrire directement
- Dépendance au processus de création des prospects

## Implémentation

1. L'observateur `ProspectObserver` crée automatiquement :
   - Un compte utilisateur
   - Attribution du rôle "portail_candidat"
   - Envoi des identifiants par email

2. Le portail candidat :
   - Inscription désactivée
   - Connexion maintenue
   - Réinitialisation de mot de passe active
   - Vérification d'email active

3. Points de déclenchement :
   - Création directe d'un prospect via le module Prospects
   - Création indirecte via la création d'un dossier (CreateDossier)

## Workflow Principal

Le système est principalement orienté "dossier". La création d'un compte portail est automatisée dans les deux scénarios suivants :

1. **Workflow Dossier (Principal)**
   - Création d'un nouveau dossier
   - Création automatique du prospect associé
   - Déclenchement de l'observateur -> Création du compte portail

2. **Workflow Marketing (Secondaire)**
   - Création directe d'un prospect
   - Déclenchement de l'observateur -> Création du compte portail
   - Possibilité ultérieure d'associer le prospect à un dossier

Cette approche garantit que tout prospect, qu'il soit créé via un dossier ou directement, aura systématiquement accès au portail candidat.

## Notes

Les prospects reçoivent leurs identifiants par email et sont invités à changer leur mot de passe lors de leur première connexion.
