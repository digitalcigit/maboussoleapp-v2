# Opérations CRUD - Gestion des Activités

## Actions Individuelles

### Menu d'actions (⋮)
Le menu d'actions vertical (trois points) propose les options suivantes :

1. **Modifier**
   - Accès au formulaire d'édition de l'activité
   - Notification de succès après modification
   - Nécessite la permission `activities.edit`

2. **Supprimer**
   - Suppression de l'activité avec confirmation
   - Notification de succès après suppression
   - Nécessite la permission `activities.delete`

## Actions en Masse

Accessibles via la sélection multiple d'activités :

1. **Suppression en masse**
   - Supprime plusieurs activités simultanément
   - Notification de succès groupée
   - Utilise le SoftDelete de Laravel

2. **Mise à jour en masse du statut**
   - Modification du statut pour plusieurs activités
   - Options disponibles :
     - En attente (jaune)
     - En cours (bleu)
     - Terminé (vert)
     - Annulé (rouge)
   - Notification de succès après la mise à jour

## Interface de Liste

### Colonnes affichées
- Sujet (lié au prospect)
- Type (avec badge coloré)
- Statut (avec badge coloré)
- Description
- Date prévue
- Date de réalisation
- Assigné à

### Fonctionnalités de la liste
- Tri par colonnes
- Recherche globale
- Filtres par type et statut
- Pagination (10 éléments par page)
- Sélection multiple pour actions en masse

## Formulaire de Création/Édition

### Champs du formulaire

1. **Informations de base**
   - Type de sujet (Prospect)
   - Sujet (sélection du prospect)
   - Type d'activité (Email, Note, etc.)
   - Statut

2. **Détails**
   - Description
   - Date prévue (obligatoire)
   - Date de réalisation
   - Assigné à (obligatoire)

## Système de Notifications

Des notifications sont envoyées pour :
- ✅ Création réussie d'une activité
- ✅ Modification réussie d'une activité
- ✅ Suppression réussie d'une activité
- ✅ Suppression en masse réussie
- ✅ Mise à jour en masse des statuts réussie

## Validation

Tous les formulaires incluent une validation pour :
- Champs obligatoires
- Formats de date valides
- Relations existantes (prospects, utilisateurs)
- Permissions utilisateur

## Permissions Requises

- `activities.view` : Voir la liste des activités
- `activities.create` : Créer une nouvelle activité
- `activities.edit` : Modifier une activité existante
- `activities.delete` : Supprimer une activité
