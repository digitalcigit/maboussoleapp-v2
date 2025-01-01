# DevOps : Guide de Test

## Tests du Pipeline CI/CD

### 1. Tests de Déploiement

#### Test en Staging
```bash
# 1. Créer une branche de test
git checkout -b test/deploy-verification

# 2. Ajouter un changement mineur
echo "# Test deployment" >> README.md
git add README.md
git commit -m "test: verify deployment pipeline"

# 3. Pousser et observer
git push origin test/deploy-verification
```

#### Vérification des Étapes
- [ ] Déclenchement du workflow
- [ ] Exécution des actions GitHub
- [ ] Notification Slack de début
- [ ] Déploiement des fichiers
- [ ] Notification Slack de fin

### 2. Tests des Notifications

#### Test du Webhook Slack
```bash
# Test via curl
curl -X POST -H 'Content-type: application/json' \
    --data '{"text":"Test notification"}' \
    $SLACK_WEBHOOK_URL
```

#### Validation des Messages
```yaml
# Format attendu
text: |
  Déploiement ${VERSION} vers ${ENVIRONMENT}
  Status: ✅ Succès / ❌ Échec
```

### 3. Tests de Sécurité

#### Vérification des Permissions
```yaml
# Test des permissions GitHub
permissions:
  contents: write
  deployments: write
  actions: read
  checks: write
  id-token: write
```

#### Test SSH
```bash
# Test de connexion
ssh -T -p 5022 -i /path/to/key user@host

# Test des permissions
ls -la $DEPLOY_PATH
```

## Procédures de Test

### 1. Test Complet du Pipeline

#### Préparation
1. Créer une branche de test
2. Préparer des modifications test
3. Configurer l'environnement

#### Exécution
1. Pousser les modifications
2. Observer le workflow
3. Vérifier les notifications
4. Valider le déploiement

#### Validation
- [ ] Workflow complété
- [ ] Notifications reçues
- [ ] Fichiers déployés
- [ ] Permissions correctes

### 2. Test des Rollbacks

#### Simulation d'Échec
```bash
# Créer un commit problématique
git commit --allow-empty -m "test: simulate deployment failure"
git push

# Préparer le rollback
git reset --hard HEAD^
git push --force
```

#### Points de Vérification
- [ ] Détection de l'échec
- [ ] Notification d'erreur
- [ ] Processus de rollback
- [ ] État final stable

### 3. Tests de Performance

#### Métriques à Mesurer
```yaml
Temps:
  - Déclenchement à début: < 30s
  - Déploiement complet: < 5min
  - Notification: < 10s

Fiabilité:
  - Taux de succès: > 99%
  - Temps moyen entre échecs: > 30 jours
  - Temps de récupération: < 15min
```

## Checklist de Validation

### 1. Pré-déploiement
- [ ] Secrets configurés
- [ ] Permissions validées
- [ ] Webhooks actifs
- [ ] SSH fonctionnel

### 2. Pendant le Déploiement
- [ ] Logs cohérents
- [ ] Notifications reçues
- [ ] Progression normale
- [ ] Pas d'erreurs bloquantes

### 3. Post-déploiement
- [ ] Application fonctionnelle
- [ ] Fichiers synchronisés
- [ ] Permissions correctes
- [ ] Documentation à jour

## Maintenance des Tests

### Documentation
- Mettre à jour les procédures
- Noter les cas particuliers
- Documenter les solutions

### Automatisation
```bash
# Script de test automatisé
#!/bin/bash
echo "Starting deployment tests..."

# Test SSH
ssh -T -p 5022 $SERVER_USER@$SERVER_HOST || exit 1

# Test Slack
curl -X POST $SLACK_WEBHOOK_URL \
     -H 'Content-type: application/json' \
     --data '{"text":"Test automation"}' || exit 1

echo "Tests completed successfully"
```

### Surveillance Continue
- Monitorer les temps de déploiement
- Analyser les patterns d'échec
- Optimiser les processus
