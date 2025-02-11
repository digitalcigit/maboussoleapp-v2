# Guide de Dépannage - Documents

## Problème : Documents Inaccessibles

### Symptômes
- Les documents uploadés ne sont pas accessibles
- Les liens de téléchargement ne fonctionnent pas
- Erreurs 404 lors de l'accès aux fichiers

### Solutions

1. **Vérifier le Lien Symbolique**
```bash
# Vérifier si le lien existe
ls -la public/storage

# Si non, créer le lien
php artisan storage:link
```

2. **Vérifier les Permissions**
```bash
# Ajuster les permissions
chmod -R 775 storage/app/public
chmod -R 775 storage/framework
chmod -R 775 storage/logs
```

3. **Vérifier l'Existence des Fichiers**
```php
// Dans le code
if (!Storage::disk('public')->exists($filePath)) {
    // Gérer l'erreur
}
```

4. **Vérifier la Configuration**
- Confirmer que APP_URL est correctement défini dans .env
- Vérifier la configuration dans config/filesystems.php

## Problème : Types de Fichiers Incorrects

### Symptômes
- Les fichiers ne s'ouvrent pas correctement
- Types MIME incorrects

### Solutions
1. Vérifier le type MIME stocké
2. Utiliser getMimeType() pour obtenir le type réel
3. Valider les types de fichiers à l'upload

## Bonnes Pratiques
1. Toujours vérifier l'existence du fichier avant l'accès
2. Stocker les métadonnées (taille, type MIME) à l'upload
3. Utiliser les méthodes du modèle pour accéder aux fichiers
