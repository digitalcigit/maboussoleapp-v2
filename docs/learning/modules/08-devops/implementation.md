# DevOps : Guide d'Implémentation

## Configuration du Workflow GitHub Actions

### Structure du Workflow
```yaml
name: Deploy Application

on:
  workflow_dispatch:  # Déploiement manuel
  push:
    branches:
      - develop
      - main

jobs:
  deploy:
    runs-on: ubuntu-latest
    permissions:
      contents: write
      deployments: write
      actions: read
      checks: write
      id-token: write
```

### Configuration des Secrets GitHub

### Secrets Requis

Pour le déploiement automatisé via GitHub Actions, les secrets suivants doivent être configurés dans le repository :

| Secret | Description | Exemple |
|--------|-------------|---------|
| `SSH_PRIVATE_KEY` | Clé SSH privée pour l'authentification au serveur | `-----BEGIN OPENSSH PRIVATE KEY-----\n...` |
| `SERVER_HOST` | Adresse IP du serveur | `104.37.188.51` |
| `SERVER_USER` | Utilisateur SSH sur le serveur | `crmmaboussole` |
| `SERVER_PORT` | Port SSH personnalisé | `8483` |
| `DEPLOY_PATH` | Chemin de déploiement sur le serveur | `/var/www/laravel/crm-app.maboussole.net` |
| `SLACK_WEBHOOK_URL` | URL du webhook Slack | `https://hooks.slack.com/services/...` |

### Configuration des Secrets

1. Accédez aux paramètres du repository sur GitHub
2. Naviguez vers "Settings" > "Secrets and variables" > "Actions"
3. Cliquez sur "New repository secret"
4. Ajoutez chaque secret avec sa valeur correspondante

### Génération de la Clé SSH

Pour générer une nouvelle paire de clés SSH pour le déploiement :

```bash
# Générer la clé
ssh-keygen -t ed25519 -C "github-actions-deploy@crm-app.maboussole.net" -f ~/.ssh/maboussole_github_deploy

# Configurer le fichier SSH config
Host crmmaboussole-github
    HostName 104.37.188.51
    User crmmaboussole
    Port 8483
    IdentityFile ~/.ssh/maboussole_github_deploy

# Ajouter la clé publique sur le serveur
ssh-copy-id -i ~/.ssh/maboussole_github_deploy.pub -p 8483 crmmaboussole@104.37.188.51
```

### Sécurité

- Les secrets sont chiffrés dans GitHub
- Les valeurs des secrets ne sont jamais exposées dans les logs
- Le port SSH non standard (8483) ajoute une couche de sécurité supplémentaire
- L'utilisation d'une clé SSH dédiée permet une meilleure traçabilité

## Étapes du Déploiement

### 1. Préparation
```yaml
steps:
  - uses: actions/checkout@v3
    with:
      fetch-depth: 0
```

### 2. Configuration SSH
```yaml
- name: Setup SSH
  uses: webfactory/ssh-agent@v0.8.0
  with:
    ssh-private-key: ${{ secrets.SSH_PRIVATE_KEY }}
```

### 3. Déploiement
```yaml
- name: Deploy Application
  run: |
    rsync -avz --delete -e "ssh -p ${secrets.SERVER_PORT}" \
      --exclude='.git*' \
      --exclude='node_modules' \
      --exclude='vendor' \
      --exclude='.env' \
      ./ ${{ secrets.SERVER_USER }}@${{ secrets.SERVER_HOST }}:${{ secrets.DEPLOY_PATH }}
```

### 4. Notification Slack
```yaml
- name: Notify Slack
  if: always()
  uses: 8398a7/action-slack@v3
  with:
    status: ${{ steps.deploy.outputs.deploy_status == 'success' && 'success' || 'failure' }}
    fields: repo,message,commit,author,action,eventName,ref,workflow,job,took
    text: |
      Déploiement ${{ steps.version.outputs.version }} vers ${{ steps.version.outputs.environment }}
      ${{ steps.deploy.outputs.deploy_status == 'success' && '✅ Succès' || '❌ Échec' }}
```

## Gestion des Versions

### 1. Création de la Version
```yaml
- name: Set version
  id: version
  run: |
    VERSION=$(date '+%Y%m%d.%H%M')
    if [[ "${{ github.ref }}" == "refs/heads/main" ]]; then
      echo "version=v$VERSION" >> $GITHUB_OUTPUT
      echo "environment=production" >> $GITHUB_OUTPUT
    else
      echo "version=v$VERSION-beta" >> $GITHUB_OUTPUT
      echo "environment=staging" >> $GITHUB_OUTPUT
    fi
```

### 2. Création des Tags
```yaml
- name: Create Release
  if: github.ref == 'refs/heads/main'
  uses: softprops/action-gh-release@v1
  with:
    tag_name: ${{ steps.version.outputs.version }}
    name: Release ${{ steps.version.outputs.version }}
```

## Sécurité et Permissions

### 1. Configuration SSH
```bash
mkdir -p ~/.ssh
touch ~/.ssh/known_hosts
ssh-keyscan -H -p ${secrets.SERVER_PORT} ${{ secrets.SERVER_HOST }} >> ~/.ssh/known_hosts
chmod 644 ~/.ssh/known_hosts
```

### 2. Permissions GitHub Actions
```yaml
permissions:
  contents: write    # Pour les releases
  deployments: write # Pour les déploiements
  actions: read      # Pour les actions
  checks: write      # Pour les checks
  id-token: write    # Pour les secrets
```

## Tests et Validation

### 1. Test du Déploiement
```bash
if [ $? -eq 0 ]; then
  echo "deploy_status=success" >> $GITHUB_OUTPUT
else
  echo "deploy_status=failure" >> $GITHUB_OUTPUT
  exit 1
fi
```

### 2. Validation des Notifications
- Vérification des webhooks
- Test des formats de message
- Confirmation des permissions

## Configuration de l'Environnement Local

### Prérequis
- Apache2
- PHP 8.1+
- MySQL
- Composer
- Node.js et npm

### Configuration Apache

1. **Créer un Virtual Host**
```bash
sudo nano /etc/apache2/sites-available/crm-app.maboussole.conf
```

Contenu :
```apache
<VirtualHost *:80>
    ServerName crm-app.local
    DocumentRoot /home/dcidev/CascadeProjects/maboussoleapp-v2/public
    
    <Directory /home/dcidev/CascadeProjects/maboussoleapp-v2/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/crm-app-error.log
    CustomLog ${APACHE_LOG_DIR}/crm-app-access.log combined
</VirtualHost>
```

2. **Activer le site et les modules**
```bash
sudo a2ensite crm-app.maboussole.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

3. **Configurer le DNS local**
```bash
sudo nano /etc/hosts
```
Ajouter :
```
127.0.0.1   crm-app.local
```

### Permissions

Les permissions doivent être configurées comme suit :

1. **Dossiers critiques**
```bash
sudo chown -R $USER:www-data storage
sudo chown -R $USER:www-data bootstrap/cache
sudo chown -R $USER:www-data public

sudo chmod -R 775 storage
sudo chmod -R 775 bootstrap/cache
sudo chmod -R 775 public
```

2. **Fichiers dans les dossiers critiques**
```bash
sudo find storage -type f -exec chmod 664 {} \;
sudo find bootstrap/cache -type f -exec chmod 664 {} \;
sudo find public -type f -exec chmod 664 {} \;
```

## Configuration du Serveur de Production

### Accès SSH
```bash
ssh crmmaboussole
```

### Structure des Dossiers
```
/var/www/laravel/crm-app.maboussole.net/
├── public/          # Document root
├── storage/         # Stockage Laravel
├── bootstrap/       # Cache et autres fichiers générés
└── [autres dossiers de l'application]
```

### Déploiement

Le déploiement utilise un système de releases pour permettre des rollbacks rapides :

```
public_html/
├── current -> ./releases/release-YYYYMMDD-HHMMSS
├── releases/
│   ├── release-20241230-065107/
│   ├── release-20241229-123456/
│   └── ...
└── public_html -> ./current/public
```

## Maintenance

### Logs
- Logs Apache : `/var/log/apache2/crm-app-error.log`
- Logs Laravel : `storage/logs/laravel.log`

### Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Base de données
```bash
php artisan migrate
```

## Troubleshooting

### Problèmes courants

1. **Page blanche ou 500**
   - Vérifier les permissions des dossiers storage et bootstrap/cache
   - Consulter les logs Laravel et Apache

2. **403 Forbidden**
   - Vérifier les permissions des dossiers parents
   - Vérifier la configuration Apache

3. **Assets non chargés**
   - Vérifier les permissions du dossier public
   - Exécuter `npm run build`
