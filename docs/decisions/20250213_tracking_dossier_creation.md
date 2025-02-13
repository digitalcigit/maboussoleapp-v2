# Suivi de la création des dossiers par utilisateur

## Contexte
Pour améliorer le suivi des performances, nous avons besoin de savoir qui crée les dossiers dans le système.

## Décision
Ajouter un champ `created_by` dans la table `dossiers` pour suivre l'utilisateur qui crée chaque dossier.

## Conséquences
1. Positives
   - Meilleur suivi des performances individuelles
   - Statistiques précises sur la création de dossiers
   - Responsabilisation des utilisateurs

2. Techniques
   - Ajout d'une clé étrangère vers la table `users`
   - Migration pour les données existantes
   - Mise à jour automatique lors de la création de dossiers

## Implémentation
1. Migration pour ajouter le champ `created_by`
2. Mise à jour du modèle `Dossier`
3. Ajout d'un widget de tableau de bord pour les métriques
4. Remplissage automatique du champ via Filament
