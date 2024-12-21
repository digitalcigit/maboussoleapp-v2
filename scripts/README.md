# Scripts Utilitaires

## validate-command.php

Script de validation des commandes critiques pour éviter les erreurs courantes.

### Utilisation

```bash
# Valider une commande
php scripts/validate-command.php migrate --env=testing  # ✅ Valide
php scripts/validate-command.php migrate               # ❌ Invalide

# Intégration avec les alias (à ajouter dans votre .bashrc ou .zshrc)
alias artisan-safe='php scripts/validate-command.php "$@" && php artisan "$@"'
```

### Commandes Surveillées
- `migrate` : Requiert --env=testing pendant les tests
- `db:seed` : Requiert --env=testing pendant les tests
- `config:cache` : Requiert la spécification de l'environnement

### Ajout de Nouvelles Règles
Pour ajouter une nouvelle commande à surveiller, modifiez le tableau `$criticalCommands` dans `validate-command.php`.
