# ADR 019 : Vision - Portail Candidat Autonome

Date : 21/01/2025
Statut : En attente - Priorité aux modifications des fonctionnalités existantes
Décideurs : Équipe de développement, MOA, MOE

## Contexte
L'application Ma Boussole vise à faciliter le suivi des demandes d'accès aux universités internationales. Une nouvelle vision du projet a émergé, permettant aux candidats d'être plus autonomes dans le suivi et la gestion de leur dossier de candidature.

## Vision

### 1. Portail Candidat Autonome
- Interface personnalisée pour chaque candidat
- Gestion autonome des documents et informations
- Suivi en temps réel de l'avancement
- Communication directe avec les conseillers

### 2. Avantages

#### Pour les Candidats
- Autonomie dans la gestion du dossier
- Upload direct des documents requis
- Suivi en temps réel du traitement
- Notifications et alertes personnalisées
- Communication facilitée avec les conseillers

#### Pour les Conseillers
- Réduction des tâches administratives
- Focus sur l'accompagnement qualitatif
- Meilleure gestion du temps
- Communication centralisée

#### Pour le Processus
- Réduction des délais de traitement
- Amélioration de la qualité des dossiers
- Diminution des erreurs
- Traçabilité accrue

## Architecture Proposée

### Structure de Données
```
User (Authentification)
├── Rôle : portail_candidat
└── Lié à -> Prospect (Informations personnelles)
            └── Dossier
                ├── Documents requis
                ├── État d'avancement
                └── Historique des interactions
```

### Fonctionnalités Clés
1. **Tableau de Bord Personnalisé**
   - Barre de progression du dossier
   - Liste des documents requis avec statut
   - Prochaines étapes à accomplir
   - Historique des actions

2. **Gestion Documentaire**
   - Upload direct des documents
   - Validation automatique des formats
   - Système de versions
   - Prévisualisation

3. **Système de Notifications**
   - Alertes pour documents manquants
   - Rappels d'échéances
   - Notifications d'avancement
   - Messages des conseillers

## Plan d'Implémentation

### Phase 1 : Base
1. Création du rôle "portail_candidat"
2. Mise en place de l'authentification
3. Développement du tableau de bord de base

### Phase 2 : Documents
1. Système d'upload de documents
2. Validation automatique
3. Gestion des versions

### Phase 3 : Notifications
1. Système de notifications
2. Rappels automatiques
3. Communication conseiller-candidat

## Impact sur l'Existant
- Ajout du nouveau rôle utilisateur
- Extension des fonctionnalités actuelles
- Pas de modification majeure de la structure

## Risques et Mitigations
1. **Adoption par les utilisateurs**
   - Solution : Interface intuitive
   - Formation et documentation claire
   - Support réactif

2. **Performance**
   - Solution : Optimisation des uploads
   - Gestion efficace du stockage

3. **Sécurité**
   - Solution : Validation stricte des fichiers
   - Droits d'accès bien définis
   - Audit des actions

## Évolutions Futures Possibles
1. Chat intégré avec les conseillers
2. Application mobile
3. Système de prise de rendez-vous
4. Intégration de tutoriels vidéo
