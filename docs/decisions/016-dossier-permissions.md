# ADR 016 : Refonte des Permissions des Dossiers

Date : 20/01/2025
Statut : Accepté
Décideurs : Équipe de développement

## Contexte
Les conseillers et managers n'avaient pas accès à la gestion des dossiers car celle-ci était limitée par la permission générale 'view_admin_panel'.

## Décision
Nous avons décidé de :
1. Créer des permissions spécifiques pour les dossiers
2. Attribuer ces permissions aux rôles appropriés
3. Mettre à jour la politique d'accès (DossierPolicy)

### Nouvelles Permissions
- dossiers.view : Voir les dossiers
- dossiers.create : Créer des dossiers
- dossiers.edit : Modifier les dossiers
- dossiers.delete : Supprimer les dossiers

### Attribution des Permissions
- Manager : Toutes les permissions
- Conseiller : view, create, edit (pas de delete)

## Conséquences

### Positives
- Accès granulaire aux fonctionnalités des dossiers
- Meilleure sécurité avec des permissions spécifiques
- Facilité de maintenance et d'extension future

### Négatives
- Nécessité de réexécuter le seeder pour mettre à jour les permissions

### Notes d'implémentation
- Modification du RoleSeeder.php pour inclure les nouvelles permissions
- Mise à jour de DossierPolicy.php pour utiliser les nouvelles permissions
- Les conseillers ne peuvent modifier que leurs dossiers assignés
