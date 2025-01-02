# ADR 003: Gestion des Secrets GitHub pour le Déploiement

## Statut
Accepté

## Contexte
Le processus de déploiement utilisait des configurations en dur dans les workflows GitHub Actions, notamment le port SSH et d'autres paramètres sensibles. Cette approche présente des risques de sécurité et limite la flexibilité de la configuration.

## Décision
Nous avons décidé de migrer toutes les configurations sensibles vers les secrets GitHub Actions :

```yaml
Secrets Required:
  SSH_PRIVATE_KEY: Clé SSH pour l'authentification
  SERVER_HOST: Adresse IP du serveur
  SERVER_USER: Utilisateur SSH
  SERVER_PORT: Port SSH personnalisé
  DEPLOY_PATH: Chemin de déploiement
```

## Conséquences

### Positives
- Amélioration significative de la sécurité
- Flexibilité accrue pour les changements de configuration
- Meilleure gestion des accès et des permissions
- Documentation claire des paramètres requis

### Négatives
- Nécessité de gérer plus de secrets
- Processus d'onboarding légèrement plus complexe
- Besoin de documentation supplémentaire

## Implémentation

### Étapes Réalisées
1. Création d'une nouvelle paire de clés SSH dédiée
2. Configuration des secrets dans GitHub
3. Mise à jour du workflow de déploiement
4. Documentation du processus

### Validation
- Tests de déploiement réussis
- Vérification de la sécurité
- Documentation mise à jour

## Notes
- La rotation régulière des clés SSH est recommandée
- Les secrets doivent être mis à jour en cas de changement d'infrastructure
- La documentation doit être maintenue à jour

## Références
- [GitHub Actions Secrets Documentation](https://docs.github.com/en/actions/security-guides/encrypted-secrets)
- [SSH Best Practices](https://docs.github.com/en/authentication/connecting-to-github-with-ssh/generating-a-new-ssh-key)
