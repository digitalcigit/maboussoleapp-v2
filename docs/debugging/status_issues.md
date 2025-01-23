# Guide de Débogage - Problèmes de Statuts

## Problèmes Courants

### 1. Statut Incorrect après Transition d'Étape

#### Symptômes
- Statut inattendu après changement d'étape
- Statut manquant dans la liste déroulante

#### Causes Possibles
1. Migration non exécutée
2. Cache de l'application
3. Données incohérentes en base

#### Solutions
```bash
# Vérifier les migrations
php artisan migrate:status

# Nettoyer le cache
php artisan cache:clear
php artisan config:clear

# Vérifier les données
SELECT current_step, current_status, COUNT(*) 
FROM dossiers 
GROUP BY current_step, current_status;
```

### 2. Statuts Non Disponibles

#### Symptômes
- Options manquantes dans le sélecteur de statut
- Erreur lors du changement de statut

#### Causes Possibles
1. Méthode `getValidStatusesForStep` non mise à jour
2. Constante de statut mal définie
3. Traduction manquante

#### Solutions
1. Vérifier le modèle `Dossier.php`
2. Valider les constantes de statut
3. Vérifier les fichiers de traduction

### 3. Transition Automatique Échouée

#### Symptômes
- Statut initial incorrect après changement d'étape
- Erreur lors du passage à l'étape suivante

#### Solutions
```php
// Vérifier dans le modèle Dossier
dd($dossier->getValidStatusesForStep($nextStep));
dd($dossier->getInitialStatus($nextStep));
```

## Bonnes Pratiques

1. **Validation des Données**
   ```sql
   -- Vérifier les statuts invalides
   SELECT * FROM dossiers 
   WHERE current_status NOT IN (
       'attente_documents',
       'attente_documents_physiques',
       'attente_paiement_frais_agence'
       -- ... autres statuts valides
   );
   ```

2. **Journalisation**
   - Activer la journalisation détaillée
   - Vérifier `/storage/logs/debug.log`
   - Tracer les changements de statut

3. **Tests**
   - Vérifier les transitions de statut
   - Valider les contraintes de workflow
   - Tester les cas limites
