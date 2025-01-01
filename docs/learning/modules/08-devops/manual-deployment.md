# Guide de Déploiement Manuel

Ce guide décrit le processus de déploiement manuel de l'application en utilisant le script `deploy-production.sh`.

## Objectif

Le script de déploiement manuel est conçu pour :
- Assurer une mise à jour cohérente de l'application en production
- Maintenir un historique des déploiements via un système de releases
- Minimiser le temps d'indisponibilité lors des mises à jour
- Permettre des rollbacks rapides en cas de problème

## Structure de Déploiement

```
/var/www/laravel/crm-app.maboussole.net/
├── current -> ./releases/release-YYYYMMDD-HHMMSS
├── releases/
│   ├── release-20241230-065107/
│   ├── release-20241229-123456/
│   └── ...
└── public -> ./current/public
```

## Prérequis

- Accès SSH au serveur : `ssh crmmaboussole`
- Fichier ZIP de la release préparé localement
- Permissions suffisantes sur le serveur

## Utilisation du Script

1. **Préparation**
   ```bash
   # Rendre le script exécutable
   chmod +x scripts/deploy-production.sh
   ```

2. **Configuration**
   Le script utilise les variables suivantes :
   ```bash
   # Connexion SSH
   SSH_CONNECTION="ssh crmmaboussole"
   
   # Chemin sur le serveur
   REMOTE_BASE_DIR="/var/www/laravel/crm-app.maboussole.net"
   
   # Nom de la release (généré automatiquement)
   RELEASE_NAME="release-$(date +%Y%m%d-%H%M%S)"
   ```

3. **Exécution**
   ```bash
   ./scripts/deploy-production.sh
   ```

## Étapes du Déploiement

Le script effectue les opérations suivantes :

1. **Création des répertoires**
   - Crée le dossier de la nouvelle release
   - Maintient la structure des releases

2. **Copie des fichiers**
   - Décompresse l'archive dans le dossier de release
   - Restaure le fichier .env de production

3. **Configuration des permissions**
   - Définit les bonnes permissions pour les dossiers et fichiers
   - Configure les permissions spéciales pour storage et cache

4. **Optimisation Laravel**
   - Nettoie et recrée les caches
   - Optimise l'application

5. **Mise à jour des liens symboliques**
   - Met à jour le lien `current`
   - Configure le lien `public`

## Rollback

En cas de problème, vous pouvez revenir à une version précédente :

1. **Lister les releases disponibles**
   ```bash
   ssh crmmaboussole "ls -la /var/www/laravel/crm-app.maboussole.net/releases"
   ```

2. **Changer le lien symbolique**
   ```bash
   ssh crmmaboussole "cd /var/www/laravel/crm-app.maboussole.net && ln -nfs releases/[ANCIENNE_RELEASE] current"
   ```

## Maintenance

### Nettoyage des anciennes releases

Pour éviter d'occuper trop d'espace disque, gardez uniquement les 5 dernières releases :

```bash
ssh crmmaboussole "cd /var/www/laravel/crm-app.maboussole.net/releases && ls -t | tail -n +6 | xargs rm -rf"
```

### Vérification du déploiement

Après chaque déploiement, vérifiez :
1. L'accès à l'application
2. Les logs d'erreur
3. Les permissions des fichiers critiques
4. La connexion à la base de données
