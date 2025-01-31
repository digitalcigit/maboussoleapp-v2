# Guide de Résolution des Problèmes

## Problèmes Courants et Solutions

### 1. Les Variables d'Environnement ne Sont pas Chargées

#### Symptômes
- Variables undefined dans l'application
- Erreurs "Variable d'environnement non trouvée"
- Configuration par défaut utilisée

#### Solutions
1. Vérifier l'existence du fichier .env
   ```bash
   ls -la .env
   ```

2. Recharger la configuration
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan config:cache
   ```

3. Vérifier les permissions
   ```bash
   chmod 644 .env
   ```

### 2. Problèmes de Format

#### Symptômes
- Valeurs mal interprétées
- Erreurs de parsing
- Caractères spéciaux corrompus

#### Solutions
1. Vérifier la syntaxe
   - Pas d'espaces autour du =
   - Guillemets autour des valeurs avec espaces
   ```env
   # Correct
   APP_NAME="Ma Boussole"
   DB_PASSWORD=mot_de_passe

   # Incorrect
   APP_NAME = Ma Boussole
   DB_PASSWORD = mot_de_passe
   ```

2. Encoder les caractères spéciaux
   ```env
   APP_NAME="Ma Boussole \u0026 Co"
   ```

### 3. Problèmes de Cache

#### Symptômes
- Anciennes valeurs persistantes
- Modifications non prises en compte
- Incohérences entre environnements

#### Solutions
1. Nettoyer tous les caches
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   php artisan route:clear
   ```

2. Reconstruire les caches
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### 4. Problèmes de Sécurité

#### Symptômes
- Fichier .env exposé publiquement
- Credentials compromis
- Accès non autorisés

#### Solutions
1. Vérifier la configuration serveur
   ```apache
   # Dans .htaccess
   <Files .env>
       Order allow,deny
       Deny from all
   </Files>
   ```

2. Restreindre les permissions
   ```bash
   chmod 644 .env
   chown www-data:www-data .env
   ```

3. Régénérer les credentials compromis
   ```bash
   # Générer une nouvelle clé d'application
   php artisan key:generate
   
   # Changer les mots de passe de base de données
   # Mettre à jour les clés d'API
   ```

## Vérifications de Diagnostic

### 1. Vérifier la Configuration Actuelle
```bash
php artisan env
php artisan config:show
```

### 2. Tester les Connexions
```bash
# Base de données
php artisan db:monitor

# Cache
php artisan cache:monitor

# File system
php artisan storage:link
```

### 3. Logs à Consulter
```bash
tail -f storage/logs/laravel.log
```

## Prévention

### 1. Bonnes Pratiques
- Toujours avoir une sauvegarde du .env
- Documenter les changements
- Tester après modification
- Utiliser des outils de validation

### 2. Maintenance Régulière
- Vérifier les permissions régulièrement
- Faire des audits de sécurité
- Mettre à jour la documentation
- Former l'équipe aux bonnes pratiques
