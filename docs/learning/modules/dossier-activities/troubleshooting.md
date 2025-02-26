# Guide de dépannage : Activités dans les Dossiers

## Problèmes courants

### 1. Les activités ne s'affichent pas

**Causes possibles :**
- Problème de permissions
- Cache de la base de données
- Erreur dans la relation polymorphique

**Solutions :**
1. Vérifier les permissions de l'utilisateur
2. Vider le cache : `php artisan cache:clear`
3. Vérifier la configuration de la relation dans le modèle

### 2. Erreur lors de la création d'une activité

**Causes possibles :**
- Champs requis manquants
- Format de date incorrect
- Type d'activité non valide

**Solutions :**
1. Vérifier que tous les champs requis sont remplis
2. Utiliser le sélecteur de date pour éviter les erreurs de format
3. Sélectionner un type d'activité dans la liste déroulante

### 3. Les filtres ne fonctionnent pas

**Causes possibles :**
- Cache du navigateur
- Problème de JavaScript
- Erreur dans la configuration des filtres

**Solutions :**
1. Vider le cache du navigateur
2. Vérifier la console JavaScript pour les erreurs
3. Réinitialiser les filtres

## Logs et débogage

### Où trouver les logs
```bash
# Logs Laravel
tail -f storage/logs/laravel.log

# Logs du serveur web
tail -f /var/log/nginx/error.log
```

### Activer le mode debug
1. Dans `.env` :
```
APP_DEBUG=true
```
2. Vider le cache :
```bash
php artisan cache:clear
php artisan config:clear
```

## Support et ressources

1. **Documentation technique**
   - Voir `/docs/technical/activities.md`
   - Consulter la documentation de Filament

2. **Canaux de support**
   - Créer un ticket dans le système de suivi
   - Contacter l'équipe technique

3. **Mises à jour**
   - Vérifier la version de Filament
   - Mettre à jour les dépendances si nécessaire
