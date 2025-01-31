# Guide Technique d'Implémentation

## Configuration Initiale

### 1. Création du fichier .env
```bash
# Copier le fichier exemple
cp .env.example .env

# Générer la clé d'application
php artisan key:generate
```

### 2. Structure Type du Fichier .env
```env
# Application
APP_NAME="Ma Boussole"
APP_ENV=local
APP_KEY=base64:votre-clé-générée
APP_DEBUG=true
APP_URL=http://localhost

# Base de données
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=maboussole
DB_USERNAME=user
DB_PASSWORD=password

# Configuration Mail
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

## Utilisation dans le Code

### 1. Accéder aux Variables d'Environnement
```php
// Dans le code PHP
$appName = env('APP_NAME', 'Valeur par défaut');

// Dans les fichiers de configuration
'debug' => env('APP_DEBUG', false),

// Bonne pratique : utiliser config() plutôt que env() dans le code
$appName = config('app.name');
```

### 2. Validation des Variables
```php
// Dans AppServiceProvider.php
public function boot()
{
    $this->validateEnvironment();
}

private function validateEnvironment()
{
    $required = [
        'APP_NAME',
        'APP_ENV',
        'APP_KEY',
        'DB_DATABASE',
        // ...
    ];

    foreach ($required as $variable) {
        if (empty(env($variable))) {
            throw new \Exception("Variable d'environnement manquante : {$variable}");
        }
    }
}
```

## Configuration par Environnement

### 1. Local (Development)
```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_LEVEL=debug
```

### 2. Staging (Test)
```env
APP_ENV=staging
APP_DEBUG=false
APP_URL=http://gestion.maboussole.net

LOG_LEVEL=debug
```

### 3. Production
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://gestion.maboussole.net

LOG_LEVEL=error
```

## Sécurisation

### 1. Protection du Fichier .env
```apache
# Dans .htaccess
<Files .env>
    Order allow,deny
    Deny from all
</Files>
```

### 2. Configuration Git
```gitignore
# Dans .gitignore
.env
.env.backup
.env.*.local
```

## Tests

### 1. Configuration des Tests
```env
# .env.testing
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

### 2. Exemple de Test
```php
public function test_environment_variables_are_loaded()
{
    $this->assertEquals('testing', config('app.env'));
    $this->assertNotEmpty(config('app.key'));
}
