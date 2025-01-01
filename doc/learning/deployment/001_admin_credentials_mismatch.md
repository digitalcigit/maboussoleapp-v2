# Incident d'apprentissage : Confusion des identifiants administrateur lors du déploiement

## Contexte
**Date :** 29 Décembre 2024
**Type d'incident :** Erreur de configuration
**Impact :** Échec de connexion administrateur après déploiement

## Description du problème
Lors du déploiement initial de l'application, une confusion est survenue concernant les identifiants administrateur. Des identifiants incorrects ont été communiqués (`admin@maboussole.app`/`Admin@2024`) alors que le système était configuré avec des identifiants différents via les seeders (`admin@maboussole-crm.com`/`password`).

## Cause racine
1. **Manque de vérification du code source**
   - Les seeders existants n'ont pas été consultés avant de communiquer les identifiants
   - Supposition incorrecte sur la création manuelle d'utilisateur vs utilisation des seeders

2. **Absence de validation post-déploiement**
   - Les credentials n'ont pas été validés après l'exécution des seeders
   - Manque de documentation sur les identifiants par défaut

## Solution appliquée
1. Identification des identifiants corrects dans `AdminUserSeeder.php`
2. Validation de la création de l'utilisateur via les seeders
3. Documentation des identifiants corrects

## Leçons apprises
1. **Vérification du code source**
   - Toujours examiner les seeders et migrations existants
   - Ne pas faire de suppositions sur les configurations par défaut

2. **Validation post-déploiement**
   - Tester les credentials immédiatement après le seeding
   - Valider que les données correspondent aux attentes

3. **Documentation**
   - Documenter les identifiants par défaut
   - Maintenir une liste des configurations critiques

## Bonnes pratiques établies
1. **Avant le déploiement**
   - Examiner les seeders existants
   - Documenter les credentials par défaut
   - Valider les configurations avec le code source

2. **Pendant le déploiement**
   - Suivre une checklist de déploiement
   - Valider chaque étape avant de continuer

3. **Après le déploiement**
   - Tester les credentials
   - Valider les données créées
   - Mettre à jour la documentation si nécessaire

## Références
- `database/seeders/AdminUserSeeder.php`
- `php artisan migrate:fresh --seed`

## Statut
✅ Résolu
- Identifiants corrects identifiés et documentés
- Processus de vérification établi pour les futurs déploiements
