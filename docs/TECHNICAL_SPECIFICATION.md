# Dossier Technique - MaBoussole CRM v2

## Table des matières
1. [Architecture Technique](#architecture-technique)
2. [Stack Technologique](#stack-technologique)
3. [Structure de la Base de Données](#structure-de-la-base-de-données)
4. [Modules et Fonctionnalités](#modules-et-fonctionnalités)
5. [Sécurité et Authentification](#sécurité-et-authentification)
6. [Intégrations et API](#intégrations-et-api)
7. [Documentation et Maintenance](#documentation-et-maintenance)

## Architecture Technique

### Vue d'ensemble
```
maboussole-crm-v2/
├── app/
│   ├── Models/           # Modèles Eloquent
│   ├── Filament/        # Resources et Pages Filament
│   ├── Livewire/        # Composants Livewire
│   ├── Services/        # Services métier
│   ├── Notifications/   # Notifications (email, SMS)
│   └── Policies/        # Politiques d'autorisation
├── database/
│   ├── migrations/      # Migrations de base de données
│   └── seeders/        # Données de test
└── resources/
    └── views/          # Vues Blade
```

### Choix Technologiques Justifiés
1. **Laravel** : Framework principal
   - Robuste et mature
   - Excellent ORM (Eloquent)
   - Système d'authentification intégré

2. **Filament** : Admin Panel
   - CRUD automatisé
   - Tableaux de bord personnalisables
   - Gestion des ressources simplifiée

3. **Livewire** : Interactivité
   - Composants dynamiques sans JavaScript complexe
   - Intégration native avec Laravel
   - Performance optimisée

## Stack Technologique

### Backend
```yaml
PHP:
  version: "8.2"
  raison: "Version stable la plus récente adaptée à la production"
  extensions:
    - pdo_mysql
    - mbstring
    - xml
    - curl
    - gd
    - zip

Laravel:
  version: "10.x"
  type: "LTS"
  raison: "Dernière version majeure avec support long terme"
  packages_clés:
    - laravel/framework: "^10.0"
    - laravel/sanctum: "^3.3"
    - laravel/tinker: "^2.8"

MySQL:
  version: "8.0"
  raison: "Version stable recommandée pour la production"
  configuration:
    charset: "utf8mb4"
    collation: "utf8mb4_unicode_ci"
```

### Admin Panel & Frontend
```yaml
Filament:
  version: "3.x"
  raison: "Dernière version majeure stable"
  packages:
    - filament/filament: "^3.0"
    - filament/forms: "^3.0"
    - filament/tables: "^3.0"
    - filament/notifications: "^3.0"

Livewire:
  version: "3.x"
  raison: "Version stable la plus récente"
  packages:
    - livewire/livewire: "^3.0"

TailwindCSS:
  version: "3.x"
  raison: "Dernière version stable"
  plugins:
    - "@tailwindcss/forms": "^0.5"
    - "@tailwindcss/typography": "^0.5"

Alpine.js:
  version: "3.x"
  raison: "Dernière version stable"
  plugins:
    - focus
    - mask
    - collapse
```

### Outils de Développement
```yaml
Composer:
  version: "2.6.x"
  raison: "Dernière version stable"

Node.js:
  version: "20.x LTS"
  raison: "Support jusqu'en 2026"
  
npm:
  version: "10.x"
  raison: "Dernière version stable"

Git:
  version: "2.43.x"
  raison: "Dernière version stable"
```

### Exigences Système
```yaml
Serveur:
  - PHP >= 8.2
  - BCMath PHP Extension
  - Ctype PHP Extension
  - JSON PHP Extension
  - Mbstring PHP Extension
  - OpenSSL PHP Extension
  - PDO PHP Extension
  - Tokenizer PHP Extension
  - XML PHP Extension

Développement:
  - Composer >= 2.6
  - Node.js >= 20.0
  - npm >= 10.0
  - Git >= 2.43
```

### Compatibilité Navigateurs
```yaml
Support:
  - Chrome (derniers 2 ans)
  - Firefox (derniers 2 ans)
  - Safari (derniers 2 ans)
  - Edge (derniers 2 ans)
  Mobile:
    - iOS Safari
    - Chrome pour Android
```

## Structure de la Base de Données

### Tables Principales
1. **users**
   - Gestion multi-rôles (polymorphique)
   - Authentification et autorisations

2. **prospects**
   - Informations personnelles
   - Statut et étapes
   - Relations avec documents

3. **clients**
   - Extension de prospects
   - Informations spécifiques admission

4. **documents**
   - Stockage sécurisé
   - Versioning
   - Validation status

5. **notifications**
   - Historique
   - Types (email, SMS, in-app)
   - Status de livraison

### Relations et Workflows
[Diagramme à venir]

## Modules et Fonctionnalités

### 1. Gestion des Utilisateurs
- Hiérarchie de rôles (RBAC)
- Permissions granulaires
- Audit trail complet

### 2. Pipeline Prospects
- Workflow automatisé
- Points de validation
- Système de relance intelligent

### 3. Gestion Documents
- Upload sécurisé
- Validation automatique
- Archivage intelligent

### 4. Notifications
- Multi-canal (email, SMS, in-app)
- Templates personnalisables
- File d'attente et retry

## Sécurité et Authentification

### Authentification
- Multi-facteur (email + SMS)
- Session management
- Password policies

### Autorisation
- RBAC (Role-Based Access Control)
- Policies Laravel
- Audit logging

## Intégrations et API

### API RESTful
- Documentation automatique (Scribe)
- Versioning
- Rate limiting

### Intégrations Externes
- Passerelle SMS
- Service email
- Stockage cloud

## Documentation et Maintenance

### Documentation Technique
- Scribe pour l'API
- PHPDoc pour le code
- Markdown pour guides

### Monitoring
- Logs structurés
- Métriques performances
- Alerting
