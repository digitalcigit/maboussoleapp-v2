# Planification Agile - MaBoussole CRM v2

## Vision du Projet
Créer un CRM moderne et efficace pour la gestion des prospects et clients dans le domaine de l'orientation des études à l'étranger.

## Méthodologie
- Sprints de 2 semaines
- Daily standup meetings
- Revue de sprint bi-hebdomadaire
- Rétrospective mensuelle

## Épiques

### 1. Configuration Système (Sprint 1-2)
```agile
- Installation et configuration de base
  ├── Mise en place Laravel
  ├── Configuration Filament
  ├── Installation Livewire
  └── Configuration base de données

- Authentification et Autorisation
  ├── Système de rôles
  ├── Permissions
  └── Interface connexion/inscription
```

### 2. Gestion des Prospects (Sprint 3-4)
```agile
- Module Prospects
  ├── Formulaire de création
  ├── Liste et filtres
  ├── Système d'attribution
  └── Workflow d'analyse

- Documents Prospects
  ├── Upload système
  ├── Validation documents
  └── Notifications automatiques
```

### 3. Gestion des Clients (Sprint 5-6)
```agile
- Conversion Prospect-Client
  ├── Processus de conversion
  ├── Migration données
  └── Création compte client

- Suivi Client
  ├── Dashboard client
  ├── Étapes visa
  └── Préparation voyage
```

### 4. Notifications et Communications (Sprint 7-8)
```agile
- Système de Notifications
  ├── Email (Amazon SES)
  ├── SMS (Twilio)
  └── Notifications in-app

- Automatisations
  ├── Relances automatiques
  ├── Rappels deadlines
  └── Alertes système
```

### 5. Rapports et Analytics (Sprint 9-10)
```agile
- Tableaux de Bord
  ├── KPIs principaux
  ├── Graphiques performance
  └── Rapports personnalisés

- Export et Partage
  ├── Export PDF/Excel
  ├── Partage automatique
  └── Programmation rapports
```

## Backlog des Sprints

### Sprint 1 (Semaines 1-2)
```agile
Stories:
1. Configuration initiale [8 points]
   - Installer Laravel
   - Configurer environnement
   - Mettre en place Git

2. Base de données [5 points]
   - Créer migrations
   - Configurer relations
   - Seeder données test

3. Auth de base [5 points]
   - Login/Register
   - Reset password
   - Remember me
```

### Sprint 2 (Semaines 3-4)
```agile
Stories:
1. Rôles et Permissions [8 points]
   - Implémenter RBAC
   - Créer middleware auth
   - Tests unitaires

2. Tests d'Intégration [13 points]
   - Tests des interactions entre composants
     ├── Auth & Permissions
     ├── Workflow Prospect-Client
     └── Interactions Base de données

   - Tests des flux complets
     ├── Parcours utilisateur
     ├── API endpoints
     └── Events & Listeners

   - Tests de performance
     ├── Temps de réponse
     ├── Charge base de données
     └── Optimisation requêtes

3. Interface Admin [5 points]
   - Setup Filament
   - Dashboard base
   - CRUD utilisateurs
```

### Sprint 3 (Semaines 5-6)
```agile
Stories:
1. Gestion Prospects [13 points]
   - Formulaire création
   - Liste prospects
   - Filtres et recherche

2. Attribution [8 points]
   - Algo attribution
   - Notifications
   - Interface attribution
```

## État d'Avancement

### Sprint 2 (Semaines 3-4)
```progress
✅ Terminé:
- Configuration de Filament
- Mise en place des rôles et permissions
- CRUD utilisateurs de base

🔄 En cours:
- Tests des ressources Activity
- Migrations de la base de données

⏭️ Planifié:
- Documentation technique
- Préparation du sprint 3
```

## Rétrospectives

### Sprint 2 - 12/12/2024
```retrospective
✅ Succès:
- Mise en place réussie du système de rôles et permissions avec Spatie
- Configuration de Filament pour l'interface admin
- Implémentation des tests pour les ressources Activity

🔄 En cours:
- Correction des tests pour les activités
- Migration des colonnes manquantes dans la table activities

📝 À améliorer:
- Documentation des migrations et des changements de schéma
- Couverture des tests pour les nouvelles fonctionnalités

⏭️ Prochaines étapes:
- Finaliser les tests des activités
- Implémenter les filtres avancés
- Commencer le module de gestion des prospects
```

### Points à Évaluer
```retro
1. Vélocité équipe
2. Qualité code
3. Communication
4. Satisfaction client
```

### Actions d'Amélioration
```improve
1. Review code systématique
2. Documentation continue
3. Tests automatisés
4. Feedback utilisateurs
```

## Estimation des Risques

### Risques Techniques
```risk
1. Performance [MEDIUM]
   - Impact: Ralentissement système
   - Mitigation: Optimisation cache et requêtes

2. Sécurité [HIGH]
   - Impact: Fuite données
   - Mitigation: Audit sécurité, encryption
```

### Risques Projet
```risk
1. Délais [MEDIUM]
   - Impact: Retard livraison
   - Mitigation: Buffer 20% par sprint

2. Adoption [LOW]
   - Impact: Résistance utilisateurs
   - Mitigation: Formation continue
```

## Métriques de Suivi

### KPIs Développement
```metrics
- Vélocité équipe
- Dette technique
- Couverture tests
- Temps moyen résolution bugs
```

### KPIs Produit
```metrics
- Satisfaction utilisateur
- Temps traitement prospect
- Taux conversion
- Utilisation fonctionnalités
```

## Planning des Releases

### Release 1.0 (MVP)
```release
Date: Fin Sprint 4
Features:
- Auth complète
- Gestion prospects base
- Dashboard simple
```

### Release 2.0
```release
Date: Fin Sprint 8
Features:
- Workflow complet
- Notifications
- Rapports base
```

### Release 3.0
```release
Date: Fin Sprint 10
Features:
- Analytics avancés
- Automatisations
- API complète
```
