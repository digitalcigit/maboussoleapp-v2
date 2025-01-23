# DevOps : Guide de Dépannage

## Problèmes Courants et Solutions

### 1. Erreur "Resource not accessible by integration"

#### Symptômes
- Échec des notifications Slack
- Message d'erreur dans les logs GitHub Actions

#### Causes Possibles
- Permissions GitHub Actions insuffisantes
- Configuration incorrecte du webhook Slack
- Token ou secret expiré

#### Solutions
1. Vérifier les permissions dans le workflow
   ```yaml
   permissions:
     contents: write
     deployments: write
     actions: read
     checks: write
     id-token: write
   ```

2. Valider le webhook Slack
   - Vérifier le secret dans GitHub
   - Tester le webhook manuellement
   - Recréer le webhook si nécessaire

### 2. Échec du Déploiement SSH

#### Symptômes
- Erreur de connexion SSH
- Timeout pendant le déploiement
- Permissions refusées

#### Causes Possibles
- Clé SSH invalide
- Permissions serveur incorrectes
- Port SSH bloqué

#### Solutions
1. Vérifier la configuration SSH
   ```bash
   # Test de connexion SSH
   ssh -p 5022 -i /path/to/key user@host
   
   # Vérifier les permissions
   ls -la ~/.ssh/
   chmod 600 ~/.ssh/id_rsa
   ```

2. Valider les known_hosts
   ```yaml
   - name: Add Known Hosts
     run: |
       mkdir -p ~/.ssh
       ssh-keyscan -H -p 5022 ${{ secrets.SERVER_HOST }} >> ~/.ssh/known_hosts
   ```

### 3. Problèmes de Synchronisation

#### Symptômes
- Fichiers manquants après déploiement
- Erreurs rsync
- Permissions incorrectes

#### Solutions
1. Vérifier les exclusions rsync
   ```yaml
   rsync -avz --delete \
     --exclude='.git*' \
     --exclude='node_modules' \
     --exclude='vendor' \
     --exclude='.env'
   ```

2. Corriger les permissions
   ```bash
   chmod -R 755 $DEPLOY_PATH
   chown -R $USER:$GROUP $DEPLOY_PATH
   ```

### 4. Notifications Slack Incomplètes

#### Symptômes
- Messages sans détails
- Formatage incorrect
- Émojis manquants

#### Solutions
1. Vérifier le format du message
   ```yaml
   text: |
     Déploiement ${{ steps.version.outputs.version }} vers ${{ steps.version.outputs.environment }}
     ${{ steps.deploy.outputs.deploy_status == 'success' && '✅ Succès' || '❌ Échec' }}
   ```

2. Valider les variables
   ```yaml
   - name: Debug Variables
     run: |
       echo "Version: ${{ steps.version.outputs.version }}"
       echo "Environment: ${{ steps.version.outputs.environment }}"
       echo "Status: ${{ steps.deploy.outputs.deploy_status }}"
   ```

### 5. Problèmes de Connexion SSH

#### Erreur : Connection Refused
- Vérifier que le port SSH (`SERVER_PORT`) est correct dans les secrets GitHub
- Vérifier que le serveur est accessible sur ce port
```bash
# Test de connexion
ssh -p 8483 crmmaboussole@104.37.188.51
```

#### Erreur : Host Key Verification Failed
- Vérifier que la commande `ssh-keyscan` s'exécute avec le bon port
- Vérifier le contenu du fichier `known_hosts`
```bash
# Vérification manuelle des clés du serveur
ssh-keyscan -H -p 8483 104.37.188.51
```

#### Erreur : Permission Denied
- Vérifier que la clé privée (`SSH_PRIVATE_KEY`) est correctement configurée
- Vérifier les permissions sur le serveur
```bash
# Vérification des permissions sur le serveur
ls -la ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
chmod 700 ~/.ssh
```

#### Erreur : No Such File or Directory
- Vérifier que le chemin de déploiement (`DEPLOY_PATH`) existe sur le serveur
- Vérifier les permissions du dossier
```bash
# Création du dossier si nécessaire
mkdir -p /var/www/laravel/crm-app.maboussole.net
chown -R crmmaboussole:www-data /var/www/laravel/crm-app.maboussole.net
```

## Procédure de Diagnostic

### 1. Vérification Initiale
- Consulter les logs GitHub Actions
- Vérifier les secrets configurés
- Tester les permissions

### 2. Tests de Connexion
```bash
# Test SSH
ssh -T -p 5022 $SERVER_USER@$SERVER_HOST

# Test Slack
curl -X POST -H 'Content-type: application/json' \
    --data '{"text":"Test message"}' \
    $SLACK_WEBHOOK_URL
```

### 3. Validation des Configurations
- Comparer avec une configuration fonctionnelle
- Vérifier les versions des actions
- Valider les permissions GitHub

## Maintenance Préventive

### 1. Vérifications Régulières
- [ ] Test des webhooks Slack
- [ ] Rotation des clés SSH
- [ ] Mise à jour des actions GitHub

### 2. Documentation
- Noter les problèmes rencontrés
- Documenter les solutions
- Mettre à jour les procédures

### 3. Monitoring
- Surveiller les temps de déploiement
- Vérifier les taux de succès
- Analyser les patterns d'erreur
