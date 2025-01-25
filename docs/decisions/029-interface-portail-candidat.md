# 4. Interface du Portail Candidat

Date: 2025-01-24

## État

Accepté

## Contexte

Le portail candidat nécessite une interface permettant aux candidats de :
- Suivre l'avancement de leur dossier
- Gérer leurs informations personnelles
- Soumettre et gérer leurs documents
- Mettre à jour leurs informations de connexion

## Décision

Nous avons décidé de structurer l'interface du portail candidat en trois parties distinctes :

1. **Dashboard**
   - Widget de progression montrant l'étape actuelle et son statut
   - Bouton d'accès rapide vers la gestion du dossier
   - Focus sur la visualisation de l'avancement

2. **Mon Profil**
   - Limité à la gestion des informations de connexion
   - Modification du mot de passe et email uniquement

3. **Gérer mon dossier**
   - Formulaire basé sur l'interface administrative existante
   - Accès à toutes les informations et documents dès le début du processus
   - Interface simplifiée excluant les champs administratifs

## Conséquences

### Positives
- Interface cohérente avec le back-office existant
- Séparation claire des responsabilités entre gestion de compte et gestion de dossier
- Expérience utilisateur simplifiée et intuitive
- Réutilisation des composants existants

### Négatives
- Nécessité de maintenir deux interfaces similaires (admin et candidat)
- Risque de confusion si les documents sont tous disponibles dès le début

### Mitigations
- Documentation claire des différences entre interfaces admin et candidat
- Messages d'aide contextuels pour guider les candidats
- Indicateurs visuels clairs de l'état d'avancement
