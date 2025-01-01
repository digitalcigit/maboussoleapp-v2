# DevOps : Guide d'Utilisation

## Déploiement Quotidien

### Déploiement Automatique
1. **Push sur les branches principales**
   ```bash
   git push origin main     # Pour la production
   git push origin develop  # Pour le staging
   ```
   - Le déploiement se déclenche automatiquement
   - Une notification Slack est envoyée au début du processus

2. **Suivi du Déploiement**
   - Observer le canal Slack dédié
   - Vérifier les statuts dans GitHub Actions
   - Attendre la notification de fin de déploiement

### Déploiement Manuel

1. **Via GitHub Actions**
   - Aller sur GitHub > Actions
   - Sélectionner "Deploy Application"
   - Cliquer "Run workflow"
   - Choisir la branche
   - Confirmer

2. **Surveillance**
   ```mermaid
   graph TD
     A[Déclenchement] -->|Attendre| B[Notification Début]
     B -->|Observer| C[Progression]
     C -->|Vérifier| D[Notification Fin]
   ```

## Notifications Slack

### Interprétation des Messages

1. **Message de Début**
   ```
   🚀 Déploiement v20241228.1558 vers production en cours...
   ```
   - Version : Format YYYYMMDD.HHMM
   - Environnement : production/staging

2. **Message de Fin**
   ```
   Déploiement v20241228.1558 vers production
   ✅ Succès
   ```
   - ✅ : Déploiement réussi
   - ❌ : Échec du déploiement

### Actions Post-Déploiement

1. **En cas de Succès**
   - Vérifier l'application en production
   - Confirmer les nouvelles fonctionnalités
   - Informer l'équipe si nécessaire

2. **En cas d'Échec**
   - Consulter les logs GitHub Actions
   - Suivre le guide de dépannage
   - Informer l'équipe technique

## Bonnes Pratiques

### Avant le Déploiement
- [ ] Code reviewé et approuvé
- [ ] Tests locaux passés
- [ ] Branches à jour
- [ ] Commits bien documentés

### Pendant le Déploiement
- [ ] Surveiller les notifications
- [ ] Rester disponible
- [ ] Noter tout comportement inhabituel

### Après le Déploiement
- [ ] Vérifier les fonctionnalités clés
- [ ] Confirmer la version déployée
- [ ] Documenter les problèmes rencontrés

## Cas d'Usage Courants

### 1. Déploiement de Hotfix
```bash
# Créer et pousser un hotfix
git checkout -b hotfix/description
git commit -m "fix: description du correctif"
git push origin hotfix/description

# Après merge sur main
git checkout main
git pull
git push
```

### 2. Déploiement de Feature
```bash
# Déploiement en staging
git checkout develop
git merge feature/nouvelle-fonctionnalite
git push origin develop

# Vérification en staging
# Si OK, merger sur main
git checkout main
git merge develop
git push origin main
```

## Checklist Quotidienne

### Matin
- [ ] Vérifier les déploiements nocturnes
- [ ] Consulter les logs d'erreur
- [ ] Planifier les déploiements du jour

### Après-midi
- [ ] Vérifier les déploiements en cours
- [ ] Mettre à jour les tickets
- [ ] Préparer les prochains déploiements

### Soir
- [ ] Confirmer l'état des déploiements
- [ ] Documenter les problèmes
- [ ] Planifier pour le lendemain
