# Guide de Maintenance - MaBoussole CRM v2

> Dernière mise à jour : 2024-12-21

## Vue d'ensemble
Documentation des procédures de maintenance pour MaBoussole CRM v2.

## Maintenance Quotidienne

### 1. Vérifications
```bash
# Espace disque
df -h

# Utilisation mémoire
free -m

# Processus PHP
ps aux | grep php

# Logs d'erreur
tail -f /var/www/maboussole/storage/logs/laravel.log
```

### 2. Nettoyage
```bash
# Cache
php artisan cache:clear
php artisan view:clear

# Sessions expirées
php artisan session:gc

# Fichiers temporaires
find /tmp -type f -mtime +7 -delete
```

## Maintenance Hebdomadaire

### 1. Mises à Jour
```bash
# Dépendances
composer update --no-dev
npm update --production

# Packages système
sudo apt update
sudo apt upgrade

# SSL
sudo certbot renew
```

### 2. Sauvegardes
```bash
# Base de données
mysqldump -u backup_user -p maboussole > /backup/db/maboussole_$(date +%Y%m%d).sql

# Fichiers uploads
rsync -av /var/www/maboussole/storage/app/public/ /backup/files/
```

## Maintenance Mensuelle

### 1. Audit
```sql
-- Utilisateurs inactifs
SELECT * FROM users 
WHERE last_login_at < DATE_SUB(NOW(), INTERVAL 30 DAY);

-- Prospects abandonnés
SELECT * FROM prospects 
WHERE status = 'new' 
AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);

-- Documents obsolètes
SELECT * FROM documents 
WHERE status = 'temporary' 
AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

### 2. Optimisation
```sql
-- Optimisation tables
OPTIMIZE TABLE users, prospects, clients, documents;

-- Analyse tables
ANALYZE TABLE users, prospects, clients, documents;
```

## Procédures de Maintenance

### Mode Maintenance
```php
// Activation
php artisan down --secret="maboussole-secret"
// Access: https://app.maboussole.com/maboussole-secret

// Désactivation
php artisan up
```

### Redémarrage Services
```bash
#!/bin/bash
# restart-services.sh

# PHP-FPM
sudo systemctl restart php8.2-fpm

# Nginx
sudo systemctl restart nginx

# Redis
sudo systemctl restart redis-server

# Supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart all
```

## Nettoyage Base de Données

### 1. Tables Temporaires
```sql
-- Nettoyage sessions expirées
DELETE FROM sessions 
WHERE last_activity < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 24 HOUR));

-- Nettoyage notifications lues
DELETE FROM notifications 
WHERE read_at IS NOT NULL 
AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

### 2. Optimisation Indexes
```sql
-- Reconstruction indexes
ALTER TABLE prospects FORCE;
ALTER TABLE clients FORCE;
ALTER TABLE documents FORCE;
```

## Gestion des Logs

### Rotation
```conf
# /etc/logrotate.d/maboussole
/var/www/maboussole/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
    postrotate
        /usr/bin/supervisorctl restart all
    endscript
}
```

### Archivage
```bash
#!/bin/bash
# archive-logs.sh

LOGS_DIR="/var/www/maboussole/storage/logs"
ARCHIVE_DIR="/backup/logs"
DATE=$(date +%Y%m)

# Création archive
tar -czf "${ARCHIVE_DIR}/logs_${DATE}.tar.gz" "${LOGS_DIR}"/*.log.*

# Upload S3
aws s3 cp "${ARCHIVE_DIR}/logs_${DATE}.tar.gz" s3://maboussole-backups/logs/
```

## Sécurité

### Scan Vulnérabilités
```bash
# Dépendances PHP
composer audit

# Dépendances NPM
npm audit

# Système
sudo lynis audit system
```

### Mise à Jour Certificats
```bash
# Vérification expiration
ssl-cert-check -a -n 14

# Renouvellement
sudo certbot renew --dry-run
```

## Monitoring Performance

### Queries Lentes
```sql
-- Activation log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 1;

-- Analyse
SELECT * FROM mysql.slow_log
WHERE start_time > DATE_SUB(NOW(), INTERVAL 24 HOUR)
ORDER BY query_time DESC
LIMIT 10;
```

### Cache Redis
```bash
# Stats Redis
redis-cli info | grep used_memory

# Nettoyage si nécessaire
redis-cli FLUSHDB
```

## Automatisation

### Cron Jobs
```bash
# /etc/cron.d/maboussole
# Maintenance quotidienne
0 1 * * * www-data /usr/bin/php /var/www/maboussole/artisan schedule:run

# Backup hebdomadaire
0 2 * * 0 www-data /usr/bin/php /var/www/maboussole/artisan backup:run

# Nettoyage mensuel
0 3 1 * * www-data /usr/bin/php /var/www/maboussole/artisan cleanup:monthly
```

### Scripts Maintenance
```php
// app/Console/Commands/MaintenanceDaily.php
class MaintenanceDaily extends Command
{
    protected $signature = 'maintenance:daily';

    public function handle()
    {
        // Nettoyage cache
        $this->call('cache:clear');
        
        // Nettoyage sessions
        $this->call('session:gc');
        
        // Vérification santé
        $this->call('health:check');
    }
}
```

---
*Documentation générée pour MaBoussole CRM v2*
