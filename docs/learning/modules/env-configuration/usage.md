# Guide d'Utilisation

## Configuration Initiale d'un Nouveau Projet

### 1. Première Installation
1. Cloner le projet
2. Copier le fichier d'exemple :
   ```bash
   cp .env.example .env
   ```
3. Générer la clé d'application :
   ```bash
   php artisan key:generate
   ```
4. Configurer les variables spécifiques à votre environnement

### 2. Configuration par Environnement

#### Développement Local
```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_DATABASE=maboussole_local
```

#### Serveur de Test
```env
APP_ENV=staging
APP_DEBUG=false
APP_URL=http://gestion.maboussole.net

DB_DATABASE=maboussole_staging
```

## Bonnes Pratiques Quotidiennes

### 1. Ajout de Nouvelles Variables
1. Toujours ajouter la variable dans `.env.example`
2. Documenter la variable (objectif, valeurs possibles)
3. Mettre à jour la documentation
4. Informer l'équipe

### 2. Modification de Variables Existantes
1. Informer l'équipe avant tout changement
2. Mettre à jour `.env.example`
3. Documenter les changements
4. Tester sur tous les environnements

### 3. Déploiement
1. Vérifier les variables requises
2. Sauvegarder l'ancien fichier .env
3. Mettre à jour les nouvelles variables
4. Tester après déploiement

## Résolution des Problèmes Courants

### 1. "Variable d'environnement non trouvée"
- Vérifier si la variable existe dans .env
- Vérifier l'orthographe
- Recharger le cache de configuration :
  ```bash
  php artisan config:clear
  php artisan config:cache
  ```

### 2. "Valeur incorrecte"
- Vérifier le format de la valeur
- Vérifier les guillemets si nécessaire
- Vérifier les espaces indésirables

### 3. "Cache de configuration périmé"
```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

## Sécurité

### 1. À Faire
- Utiliser des variables différentes par environnement
- Changer régulièrement les mots de passe
- Sauvegarder le fichier .env
- Utiliser des valeurs cryptées pour les données sensibles

### 2. À Ne Pas Faire
- Ne jamais commiter le fichier .env
- Ne pas utiliser de valeurs de production en local
- Ne pas partager les credentials de production
- Ne pas utiliser env() directement dans le code
