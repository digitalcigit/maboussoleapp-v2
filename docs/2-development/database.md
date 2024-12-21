# Schéma de Base de Données - MaBoussole CRM v2

> Dernière mise à jour : 2024-12-21

## Vue d'ensemble
Documentation détaillée du schéma de base de données de MaBoussole CRM v2.

## Tables Principales

### Users
```sql
users
├── id (bigint, primary key)
├── name (varchar)
├── email (varchar, unique)
├── password (varchar)
├── role (enum: super_admin, manager, advisor, partner, commercial)
├── phone (varchar)
├── status (enum: active, inactive)
├── email_verified_at (timestamp)
├── phone_verified_at (timestamp)
├── last_login_at (timestamp)
└── timestamps (created_at, updated_at)
```

### Prospects
```sql
prospects
├── id (bigint, primary key)
├── reference_number (varchar, unique)
├── first_name (varchar)
├── last_name (varchar)
├── email (varchar)
├── phone (varchar)
├── birth_date (date)
├── profession (varchar)
├── education_level (varchar)
├── current_location (varchar)
├── current_field (varchar)
├── desired_field (varchar)
├── desired_destination (varchar)
├── emergency_contact (json)
├── status (enum: new, analyzing, validated, rejected, converted)
├── advisor_id (bigint, foreign key)
├── analysis_deadline (timestamp)
└── timestamps
```

### Clients
```sql
clients
├── id (bigint, primary key)
├── prospect_id (bigint, foreign key)
├── reference_number (varchar, unique)
├── status (enum: active, inactive, pending, archived)
├── payment_status (enum: pending, partial, completed)
├── advisor_id (bigint, foreign key)
├── documents_status (json)
├── visa_status (enum: not_started, in_progress, approved, rejected)
└── timestamps
```

## Relations et Contraintes

### Clés Étrangères
```sql
-- Prospects
ALTER TABLE prospects
ADD CONSTRAINT fk_prospects_advisor
FOREIGN KEY (advisor_id) REFERENCES users(id);

-- Clients
ALTER TABLE clients
ADD CONSTRAINT fk_clients_prospect
FOREIGN KEY (prospect_id) REFERENCES prospects(id);

ALTER TABLE clients
ADD CONSTRAINT fk_clients_advisor
FOREIGN KEY (advisor_id) REFERENCES users(id);
```

### Index
```sql
-- Performance Indexes
CREATE INDEX idx_prospects_status ON prospects(status);
CREATE INDEX idx_clients_status ON clients(status);
CREATE INDEX idx_users_role ON users(role);
```

## Migrations

### Création des Tables
```bash
php artisan make:migration create_users_table
php artisan make:migration create_prospects_table
php artisan make:migration create_clients_table
```

### Mise à Jour du Schéma
```bash
# Development
php artisan migrate

# Testing
php artisan migrate --env=testing

# Production (avec précaution)
php artisan migrate --force
```

## Seeders

### Development Data
```bash
php artisan db:seed
```

### Test Data
```bash
php artisan db:seed --class=TestDataSeeder
```

## Maintenance

### Backup
```bash
# Configuration
BACKUP_DISK=s3
BACKUP_PATH=backups

# Exécution
php artisan backup:run
```

### Restauration
```bash
php artisan backup:restore
```

## Bonnes Pratiques

### 1. Migrations
- Toujours créer des migrations réversibles
- Tester les rollbacks
- Documenter les changements

### 2. Performance
- Indexer les colonnes fréquemment utilisées
- Optimiser les requêtes complexes
- Monitorer la taille des tables

### 3. Sécurité
- Chiffrer les données sensibles
- Utiliser les prepared statements
- Limiter les accès directs

---
*Documentation générée pour MaBoussole CRM v2*
