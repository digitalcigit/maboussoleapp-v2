# Résolution des problèmes liés à la page de profil

Ce document présente les problèmes courants rencontrés avec la nouvelle page de profil utilisateur et leurs solutions.

## Problèmes d'upload d'avatar

### Problème : L'image ne s'affiche pas après l'upload

**Symptômes** :
- L'utilisateur télécharge une image mais elle n'apparaît pas après la sauvegarde
- Aucune erreur n'est visible dans l'interface
- Possible erreur 404 dans les logs du serveur

**Causes possibles** :
1. Permissions incorrectes sur le dossier de stockage
2. Lien symbolique manquant entre `storage/app/public` et `public/storage`
3. Configuration incorrecte du disque dans la configuration Filament

**Solutions** :
```bash
# 1. Vérifier et corriger les permissions
sudo chown -R www-data:www-data storage/app/public/avatars
sudo chmod -R 775 storage/app/public/avatars

# 2. Recréer le lien symbolique
php artisan storage:link

# 3. Vérifier la configuration dans config/filesystems.php
```

### Problème : Erreur lors du recadrage d'image

**Symptômes** :
- Message d'erreur lors de l'utilisation de l'éditeur d'image
- L'image est téléchargée mais le recadrage échoue

**Causes possibles** :
1. Extension GD ou Imagick manquante sur le serveur
2. Mémoire insuffisante pour traiter l'image
3. Fichier image corrompu ou dans un format non supporté

**Solutions** :
```bash
# Vérifier les extensions PHP
php -m | grep -E 'gd|imagick'

# Si GD est manquant, l'installer
sudo apt-get update
sudo apt-get install php8.1-gd  # Ajuster la version PHP selon votre installation

# Augmenter la limite de mémoire PHP dans php.ini
memory_limit = 256M

# Redémarrer le serveur web
sudo systemctl restart apache2  # ou nginx selon votre configuration
```

## Problèmes de changement de mot de passe

### Problème : Erreur "Mot de passe actuel incorrect"

**Symptômes** :
- Le message "Mot de passe actuel incorrect" s'affiche même lorsque l'utilisateur est certain d'avoir saisi le bon mot de passe

**Causes possibles** :
1. Mot de passe mal saisi (majuscules/minuscules, caractères spéciaux)
2. Problème de synchronisation avec le système d'authentification
3. Utilisateur connecté via un fournisseur OAuth (Google, GitHub, etc.)

**Solutions** :
- Vérifier que le Caps Lock n'est pas activé
- Se déconnecter et se reconnecter avant de changer le mot de passe
- Pour les utilisateurs OAuth, leur rappeler qu'ils doivent configurer un mot de passe local
- Vérifier dans la base de données que l'utilisateur a bien un mot de passe défini (non NULL)

### Problème : Validation du nouveau mot de passe échoue

**Symptômes** :
- Message d'erreur indiquant que le nouveau mot de passe ne respecte pas les règles de sécurité

**Causes possibles** :
1. Mot de passe trop court (minimum 8 caractères)
2. Mot de passe ne contenant pas assez de types de caractères (majuscules, minuscules, chiffres, caractères spéciaux)
3. Mot de passe trop simple ou figurant dans une liste de mots de passe courants

**Solutions** :
- Informer l'utilisateur des règles exactes de création de mot de passe
- Vérifier la configuration de validation dans `app/Filament/Pages/Profile.php` :
```php
use Illuminate\Validation\Rules\Password;

TextInput::make('new_password')
    ->password()
    ->rule(Password::default())  // Définit les règles de validation
```

## Problèmes d'affichage et d'interface

### Problème : Page de profil non accessible

**Symptômes** :
- L'utilisateur reçoit une erreur 403 (Forbidden) en tentant d'accéder à la page de profil
- La page ne s'affiche pas dans le menu de navigation

**Causes possibles** :
1. Problème de permissions dans Filament
2. La page n'est pas correctement enregistrée dans le fournisseur de services
3. Conflit avec une autre route ou middleware

**Solutions** :
- Vérifier l'enregistrement de la page dans `app/Providers/FilamentServiceProvider.php`
- S'assurer que la classe `Profile` est bien dans le bon namespace
- Vérifier les permissions associées à la page

### Problème : Champs du formulaire mal alignés ou invisibles

**Symptômes** :
- Les champs du formulaire sont mal alignés ou ne s'affichent pas correctement
- Problèmes d'espacement ou de mise en page

**Causes possibles** :
1. Conflit CSS avec le thème de l'application
2. Erreur dans la définition du formulaire
3. Problème de compatibilité de navigateur

**Solutions** :
- Inspecter les éléments avec les outils de développement du navigateur
- Vérifier les classes CSS appliquées
- Tester sur différents navigateurs
- Mettre à jour Filament vers la dernière version
```bash
composer update filament/filament
```

## Problèmes avancés

### Problème : Erreur après mise à jour de PHP ou Laravel

**Symptômes** :
- La page de profil fonctionnait mais ne fonctionne plus après une mise à jour
- Erreurs Laravel dans les logs

**Causes possibles** :
1. Incompatibilité entre les versions de Filament et PHP/Laravel
2. Changements dans l'API de Laravel qui affectent la fonctionnalité

**Solutions** :
- Vérifier la matrice de compatibilité de Filament
- Mettre à jour tous les packages Filament ensemble
- Consulter le changelog de Laravel pour les changements d'API
```bash
composer require filament/filament:^X.Y filament/forms:^X.Y
```

### Problème : Données de profil non persistantes

**Symptômes** :
- Les modifications du profil semblent être enregistrées mais disparaissent après déconnexion/reconnexion

**Causes possibles** :
1. Problème de cache de session
2. Transaction de base de données non validée
3. Middleware qui réinitialise les valeurs

**Solutions** :
- Vérifier la méthode `save()` dans la classe `Profile`
- Inspecter les logs de base de données
- Ajouter des logs de débogage pour suivre le flux d'exécution
```php
\Log::debug('Saving user profile', ['user_id' => auth()->id(), 'data' => $form->getState()]);
```

## Récapitulatif des commandes utiles

```bash
# Nettoyer le cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Reconstruire le cache
php artisan config:cache
php artisan route:cache

# Vérifier les liaisons de stockage
php artisan storage:link

# Voir les logs en temps réel
tail -f storage/logs/laravel.log
```
