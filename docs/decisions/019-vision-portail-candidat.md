# ADR 019 : Vision Étendue - Portail Candidat Multi-Dossiers

Date : 21/01/2025
Statut : En attente - Priorité aux modifications des fonctionnalités existantes
Décideurs : Équipe de développement, MOA, MOE

## Contexte
L'application Ma Boussole vise à faciliter le suivi des demandes de bourses d'études internationales. Une nouvelle vision plus large du projet a émergé, permettant aux candidats de gérer plusieurs dossiers de candidature via un portail unique.

## Vision Étendue

### 1. Portail Candidat Unifié
- Un seul compte utilisateur par candidat
- Accès à tous ses dossiers de candidature
- Interface personnalisée selon le pays visé
- Gestion centralisée des documents

### 2. Gestion Multi-Dossiers
**Exemple** :
- Dossier principal : Candidature France (Campus France)
- Dossier secondaire : Candidature Canada (EIC)
- Réutilisation intelligente des documents communs

### 3. Avantages

#### Pour les Candidats
- Une seule authentification pour tout gérer
- Vue d'ensemble de toutes les candidatures
- Évite la répétition dans la soumission des documents
- Suivi en temps réel de chaque dossier
- Notifications centralisées

#### Pour les Conseillers
- Vision globale du parcours candidat
- Meilleure coordination des dossiers parallèles
- Optimisation du temps de traitement
- Conseil plus pertinent sur les options

#### Pour le Processus
- Validation unique des documents de base
- Gestion efficace des deadlines multiples
- Réduction des erreurs et des doublons
- Meilleure qualité de service

## Architecture Proposée

### Structure de Données
```
User (Authentification)
├── Rôle : portail_candidat
└── Lié à -> Prospect (Informations personnelles)
            └── Dossiers[]
                ├── Documents de base (partagés)
                └── Documents spécifiques (par pays)
```

### Fonctionnalités Clés
1. **Tableau de Bord Personnalisé**
   - Barre de progression par dossier
   - Calendrier des échéances
   - Liste des tâches à accomplir

2. **Gestion Documentaire**
   - Classification : documents de base/spécifiques
   - Upload direct avec validation
   - Système de versions des documents

3. **Système de Notifications**
   - Alertes deadlines
   - Rappels documents manquants
   - Notifications d'avancement

## Plan d'Implémentation

### Phase 1 : Base
1. Création du rôle "portail_candidat"
2. Mise en place de l'authentification
3. Développement du tableau de bord de base

### Phase 2 : Documents
1. Système de gestion documentaire
2. Classification des documents
3. Fonctionnalité d'upload

### Phase 3 : Notifications
1. Système de notifications
2. Rappels automatiques
3. Suivi des deadlines

## Impact sur l'Existant
- Modification mineure de la structure de données
- Ajout de nouvelles relations
- Conservation des fonctionnalités actuelles

## Risques et Mitigations
1. **Complexité accrue**
   - Solution : Interface utilisateur intuitive
   - Documentation claire pour les utilisateurs

2. **Performance**
   - Solution : Optimisation du chargement des documents
   - Mise en cache intelligente

3. **Sécurité**
   - Solution : Cloisonnement strict des données
   - Audit régulier des accès

## Évolutions Futures Possibles
1. Module de statistiques avancées
2. Intelligence artificielle pour les recommandations
3. Intégration avec les systèmes des ambassades
4. Application mobile dédiée
