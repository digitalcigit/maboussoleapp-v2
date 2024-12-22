# Architecture Technique - MaBoussole CRM v2

> Dernière mise à jour : 2024-12-21

## Vue d'ensemble
L'architecture de MaBoussole CRM v2 est construite sur des technologies modernes et éprouvées, avec un focus sur la maintenabilité, la performance et la sécurité.

## Stack Technique

### Backend
```yaml
PHP: 8.2
Laravel: 10.x
MySQL: 8.0
```

### Admin Panel & Frontend
```yaml
Filament: 3.x
Livewire: 3.x
TailwindCSS: 3.x
```

## Structure du Projet

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

## Composants Principaux

### 1. Gestion des Utilisateurs
- Authentification Laravel Breeze
- RBAC avec Spatie Permissions
- Sessions sécurisées

### 2. Interface Administration
- Panel Filament personnalisé
- Resources CRUD automatisés
- Tableaux de bord dynamiques

### 3. Base de Données
- Migrations versionnées
- Relations Eloquent
- Indexation optimisée

### 4. API et Services
- API RESTful
- Services métier isolés
- Cache et queues

## Sécurité

### Authentication
- Laravel Sanctum
- Protection CSRF
- Rate limiting

### Autorisation
- RBAC (Role-Based Access Control)
- Policies Laravel
- Middleware personnalisés

### Données
- Encryption at rest
- Validation stricte
- Audit logging

## Performance

### Optimisations
- Cache Redis
- Queue système
- Eager loading

### Monitoring
- Telescope en dev
- Logs structurés
- Métriques New Relic

## Tests

### Types de Tests
- Tests unitaires (PHPUnit)
- Tests Feature (Laravel)
- Tests Browser (Dusk)

### Couverture
- Models : 95%
- Controllers : 92%
- Services : 94%

## Déploiement

### Environnements
- Development
- Staging
- Production

### CI/CD
- GitHub Actions
- Tests automatisés
- Déploiement zero-downtime

---
*Documentation générée pour MaBoussole CRM v2*
