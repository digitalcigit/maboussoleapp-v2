# Dossier Technique - MaBoussole CRM v2

## Table des matières
1. [Architecture Technique](#architecture-technique)
2. [Stack Technologique](#stack-technologique)
3. [Structure de la Base de Données](#structure-de-la-base-de-données)
4. [Modules et Fonctionnalités](#modules-et-fonctionnalités)
5. [Sécurité et Authentification](#sécurité-et-authentification)
6. [Intégrations et API](#intégrations-et-api)
7. [Documentation et Maintenance](#documentation-et-maintenance)
8. [Tests et Bases de Données](#tests-et-bases-de-données)
9. [Tests et Qualité du Code](#tests-et-qualité-du-code)

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

## Tests et Bases de Données

### Configuration des Bases de Données

L'application utilise deux bases de données distinctes :

1. **Base de données principale** (Production/Développement)
   - Nom : `maboussole_crm`
   - Configurée dans le fichier `.env`
   - Utilisée pour le développement et la production
   - Contient les données réelles de l'application

2. **Base de données de test**
   - Nom : `maboussole_crm_testing`
   - Configurée dans `phpunit.xml`
   - Utilisée exclusivement pour les tests automatisés
   - Réinitialisée à chaque exécution des tests

### Bonnes Pratiques pour les Tests

Pour garantir que les tests n'affectent pas la base de données principale :

1. **Exécution des Tests**
   ```bash
   php artisan test --env=testing
   ```
   Cette commande force l'utilisation de l'environnement de test.

2. **Configuration des Tests**
   - Utiliser le trait `RefreshDatabase` dans les classes de test
   - Ce trait assure que la base de données de test est réinitialisée après chaque test
   ```php
   use RefreshDatabase;
   ```

3. **Migrations et Seeders**
   - Les migrations doivent être idempotentes (pouvoir être exécutées plusieurs fois sans effet secondaire)
   - Vérifier l'existence des colonnes/clés avant de les modifier :
   ```php
   if (Schema::hasColumn('table_name', 'column_name')) {
       // Modification de la colonne
   }
   ```

### Résolution des Problèmes Courants

1. **Perte d'Accès Admin**
   Si l'accès admin est perdu après des manipulations de base de données :
   ```bash
   php artisan migrate:refresh --seed
   ```
   Cette commande :
   - Réinitialise la structure de la base de données
   - Recrée les rôles et permissions
   - Recrée l'utilisateur admin
   - Ajoute les données de test

2. **Vérification de l'Environnement**
   Avant d'exécuter des commandes de migration :
   - Vérifier le fichier `.env` pour la base principale
   - Vérifier `phpunit.xml` pour la base de test
   - S'assurer que la bonne base de données est ciblée

### Notes Importantes

- Ne jamais exécuter `migrate:refresh` en production sans sauvegarde
- Toujours vérifier l'environnement actif avant les opérations de base de données
- Maintenir les seeders à jour pour faciliter la reconstruction de l'environnement
- Garder le fichier `.env` dans `.gitignore` pour la sécurité

## Tests et Qualité du Code

### Tests Automatisés

#### 1. Tests Unitaires et d'Intégration
```yaml
Framework: PHPUnit
Couverture: ~95%
Types de Tests:
  - Unitaires: Tests isolés des composants
  - Intégration: Tests des interactions entre composants
  - Feature: Tests des fonctionnalités complètes
```

#### 2. Tests des Ressources Filament

##### ActivityResource
1. **Tests CRUD**
   - Listing des activités
   - Création (prospects et clients)
   - Modification
   - Suppression

2. **Tests de Filtrage**
   - Par type d'activité
   - Par statut de complétion
   - Par plage de dates
   - Par type de sujet (prospect/client)

3. **Tests de Validation**
   - Champs obligatoires
   - Formats de données
   - Contraintes métier

4. **Tests de Permissions**
   - Lecture
   - Création
   - Modification
   - Suppression

5. **Tests de Relations**
   - Chargement des relations
   - Intégrité référentielle

6. **Tests de Pagination et Tri**
   - Pagination avec tri par date
   - Navigation entre pages
   - Ordre de tri personnalisé

### Bonnes Pratiques de Test

#### 1. Organisation des Tests
```php
tests/
├── Feature/
│   ├── Auth/           # Tests d'authentification
│   ├── Filament/       # Tests des ressources Filament
│   └── API/            # Tests des endpoints API
├── Unit/
│   ├── Models/         # Tests des modèles
│   └── Services/       # Tests des services
└── TestCase.php        # Classe de base des tests
```

#### 2. Base de Données de Test
```yaml
Configuration:
  driver: sqlite
  database: :memory:
  
Migrations:
  - Exécutées avant chaque test
  - Nettoyage après chaque test
  
Factories:
  - Données de test réalistes
  - États prédéfinis pour scénarios
```

#### 3. Assertions Communes
```php
// Exemple d'assertions fréquentes
$response->assertStatus(200);           // Vérification du statut HTTP
$response->assertJsonStructure([...]);  // Validation de la structure JSON
$this->assertDatabaseHas('table', []); // Vérification des données en base
```

#### 4. Mocking et Isolation
```php
// Exemple de mock de service
$this->mock(ServiceClass::class)
     ->shouldReceive('method')
     ->once()
     ->andReturn($value);
```

### Outils de Qualité

#### 1. Analyse Statique
```yaml
PHPStan:
  niveau: 8
  configuration: phpstan.neon

PHP_CodeSniffer:
  standard: PSR-12
  exclusions:
    - database/
    - tests/
```

#### 2. Couverture de Code
```yaml
PHPUnit:
  coverage-html: rapport détaillé HTML
  minimum: 90%
  exclusions:
    - database/
    - config/
```

#### 3. CI/CD
```yaml
GitHub Actions:
  événements:
    - push sur main
    - pull requests
  étapes:
    - tests unitaires
    - analyse statique
    - vérification style
    - build assets
```

### Maintenance des Tests

#### 1. Organisation
- Tests groupés par fonctionnalité
- Nommage descriptif des tests
- Documentation des scénarios complexes

#### 2. Données de Test
- Utilisation de factories Laravel
- Données réalistes mais anonymisées
- Isolation entre les tests

#### 3. Performance
- Tests rapides (< 1s par test)
- Utilisation de SQLite en mémoire
- Optimisation des seeders

#### 4. Documentation
- PHPDoc pour les classes de test
- Description claire des scénarios
- Exemples d'utilisation
