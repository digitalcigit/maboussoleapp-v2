# Module : Gestion de Dossier (Portail Candidat)

## Vue d'ensemble
Ce module permet aux candidats de gérer leur dossier directement depuis le portail candidat. Il offre une interface simplifiée et sécurisée pour :
- Mettre à jour les informations personnelles
- Gérer les documents requis
- Suivre l'avancement du dossier

## Points clés
1. Interface dédiée et simplifiée
2. Accès limité à son propre dossier
3. Notifications automatiques aux conseillers
4. Gestion des documents par étape

## Composants principaux
- `DossierResource` : Gestion de l'affichage et des interactions
- `EditDossier` : Page d'édition du dossier
- `DossierProgressWidget` : Widget de progression

## Sécurité
- Authentification requise
- Vérification automatique des permissions
- Validation des documents uploadés
- Protection contre les modifications non autorisées

## Intégration
- Utilise le même modèle que l'interface admin
- Notifications en temps réel aux conseillers
- Synchronisation automatique des données

## Support
En cas de problème, consulter :
- Le guide de dépannage dans `troubleshooting.md`
- Les cas d'utilisation dans `case-studies/`
- La documentation technique dans `implementation.md`
