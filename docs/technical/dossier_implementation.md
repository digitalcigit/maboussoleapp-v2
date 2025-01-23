# Implémentation du Module de Gestion des Dossiers

## Vue d'ensemble
Le module de gestion des dossiers permet de suivre le parcours d'un prospect à travers différentes étapes, de l'analyse initiale jusqu'à l'obtention du visa. Cette documentation détaille l'implémentation technique de ce module.

## Structure de la Base de Données

### Table `dossiers`
- `id` : Identifiant unique
- `reference_number` : Numéro de référence unique (format: DOS-XXXXX)
- `prospect_id` : Clé étrangère vers la table prospects
- `current_step` : Étape actuelle du dossier
- `current_status` : Statut actuel dans l'étape
- `notes` : Notes générales sur le dossier
- `last_action_at` : Horodatage de la dernière action
- Timestamps standards Laravel

### Table `dossier_steps`
- Stocke l'historique des étapes du dossier
- Permet de suivre la progression à travers le workflow

### Table `dossier_documents`
- Gère les documents requis pour chaque étape
- Stocke les métadonnées des fichiers uploadés

## Workflow des Dossiers

### Étapes du Workflow
1. **Analyse de Dossier**
   - En attente de documents
   - Analyse en cours
   - Analyse terminée

2. **Ouverture & Admission**
   - Documents physiques reçus
   - Frais d'admission payés
   - Dossier soumis
   - Soumission acceptée/rejetée

3. **Paiement**
   - Frais d'agence payés
   - Paiement partiel scolarité
   - Paiement total scolarité
   - Dossier abandonné

4. **Accompagnement Visa**
   - Dossier visa prêt
   - Frais visa payés
   - Visa soumis
   - Visa obtenu/refusé
   - Frais finaux payés

## Création de Dossier

### Modes de Création

Le système supporte deux modes de création de dossier :

1. **À partir d'un Prospect Existant**
   - Sélection d'un prospect dans la liste
   - Pré-remplissage automatique des informations
   - Conservation de l'historique prospect-dossier

2. **Création Directe avec Nouveau Prospect**
   - Saisie directe des informations du prospect
   - Création automatique du prospect
   - Liaison automatique prospect-dossier

### Structure des Données

#### Table `dossiers`
```sql
CREATE TABLE dossiers (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    reference_number varchar(255) NOT NULL,
    prospect_id bigint(20) unsigned NULL,
    current_step tinyint unsigned NOT NULL DEFAULT 1,
    current_status varchar(50) NOT NULL DEFAULT 'en_attente',
    prospect_info json DEFAULT NULL,
    notes text DEFAULT NULL,
    last_action_at timestamp NULL,
    completed_at timestamp NULL,
    created_at timestamp NULL,
    updated_at timestamp NULL,
    deleted_at timestamp NULL,
    PRIMARY KEY (id),
    UNIQUE KEY dossiers_reference_number_unique (reference_number),
    KEY dossiers_prospect_id_foreign (prospect_id),
    KEY idx_current_step_status (current_step, current_status),
    KEY idx_last_action (last_action_at),
    CONSTRAINT dossiers_prospect_id_foreign FOREIGN KEY (prospect_id) REFERENCES prospects (id) ON DELETE CASCADE
);
```

### Workflow de Création

1. **Initialisation du Formulaire**
   - Génération automatique du numéro de référence
   - Affichage des champs selon le mode de création

2. **Sélection du Mode**
   - Choix optionnel d'un prospect existant
   - Si non sélectionné, activation des champs de saisie prospect

3. **Validation et Enregistrement**
   - Vérification des données requises
   - Création du prospect si nécessaire
   - Création du dossier avec les relations appropriées

### Étapes et Statuts

#### Étapes (current_step)
1. Analyse de dossier (1)
2. Admission (2)
3. Paiement (3)
4. Visa (4)

#### Statuts par Étape
1. **Analyse**
   - En attente de documents
   - Analyse en cours
   - Analyse terminée

2. **Admission**
   - Documents physiques reçus
   - Frais d'admission payés
   - Dossier soumis
   - Soumission acceptée/rejetée

3. **Paiement**
   - Frais d'agence payés
   - Scolarité partiellement payée
   - Scolarité totalement payée
   - Abandonné

4. **Visa**
   - Dossier visa prêt
   - Frais de visa payés
   - Visa soumis/obtenu/refusé
   - Frais finaux payés

### Bonnes Pratiques

1. **Création de Prospect**
   - Vérifier les doublons potentiels
   - Remplir tous les champs obligatoires
   - Utiliser des formats standardisés

2. **Gestion des Documents**
   - Associer les documents au prospect
   - Utiliser les types de documents appropriés
   - Vérifier la qualité des fichiers

3. **Suivi des Étapes**
   - Respecter la progression logique
   - Mettre à jour les statuts régulièrement
   - Documenter les changements importants

## Interface Utilisateur (Filament)

### DossierResource
Le `DossierResource` fournit une interface complète pour la gestion des dossiers avec :

- Liste des dossiers avec filtres et tri
- Formulaire de création/édition avec :
  - Génération automatique du numéro de référence
  - Sélection du prospect associé
  - Gestion des étapes et statuts
  - Champ de notes

### Navigation
- Groupe : "Gestion des Dossiers"
- Label : "Dossiers"
- Icône : Folder (heroicon)
- Badge : Nombre de dossiers actifs

### Fonctionnalités de l'Interface
- Vue tabulaire avec colonnes personnalisées
- Badges colorés pour les étapes et statuts
- Filtres par étape et statut
- Actions de vue et d'édition
- Support multilingue (labels en français)

## Modèles

### Dossier
- Relations avec Prospect et historique des étapes
- Méthodes utilitaires pour la gestion des statuts
- Constantes pour les étapes et statuts

### DossierStep
- Gestion de l'historique des étapes
- Méthodes pour les labels et validations

### DossierDocument
- Gestion des documents requis
- Intégration avec le système de fichiers

## Sécurité et Validation
- Validation des transitions d'étapes
- Vérification des documents requis
- Gestion des permissions utilisateur

## Points d'Extension
- Support pour des étapes personnalisées
- Hooks pour les actions personnalisées
- Système de notification configurable

## Journal des Modifications

### 18/01/2025 - Implémentation du Workflow Initial

#### Fonctionnalités Ajoutées
1. **Gestion des Étapes**
   - Définition des 4 étapes principales (Analyse, Admission, Paiement, Visa)
   - Chaque étape a ses propres statuts
   - Transition automatique vers l'étape suivante avec statut initial

2. **Interface Utilisateur**
   - Affichage des étapes avec badges colorés :
     - Analyse : gris
     - Admission : orange
     - Paiement : vert
     - Visa : bleu
   - Affichage des statuts avec indicateurs de progression
   - Bouton "Continuer" conditionnel pour la progression du workflow

3. **Workflow d'Analyse**
   - Statuts implémentés :
     - En attente de documents
     - Analyse en cours
     - Analyse terminée
   - Progression automatique vers l'étape Admission une fois l'analyse terminée

#### Prochaines Étapes

1. **Étape Admission**
   - [ ] Ajouter un champ pour le montant des frais d'admission
   - [ ] Implémenter l'upload des documents physiques
   - [ ] Gérer le changement de destination en cas de refus
   - [ ] Ajouter des notifications pour les changements de statut

2. **Étape Paiement**
   - [ ] Créer une table pour le suivi des paiements
   - [ ] Implémenter le système de rappels pour les paiements en retard
   - [ ] Ajouter un tableau de bord financier

3. **Étape Visa**
   - [ ] Créer une structure pour les documents du visa
   - [ ] Implémenter le suivi des rendez-vous
   - [ ] Gérer les notifications de dates importantes

#### Points d'Amélioration
- Ajouter des validations pour chaque transition d'étape
- Implémenter un système de commentaires par étape
- Ajouter des indicateurs de performance (durée moyenne par étape)
- Mettre en place des rappels automatiques

## Mise à jour du 18/01/2025

### Améliorations de l'Interface Utilisateur

#### Formulaire de Création
- Réorganisation du formulaire en une seule colonne pour plus de clarté
- Grille 2 colonnes pour l'étape et le statut
- Affichage conditionnel du statut en fonction de l'étape sélectionnée
- Champ notes agrandi à 3 lignes pour une meilleure lisibilité

#### Sélection des Prospects
- Amélioration de la recherche (prénom, nom, email)
- Tri automatique par nom complet
- Filtrage des prospects déjà associés à un dossier
- Affichage du nom complet dans le sélecteur

#### Gestion des Statuts
- Statuts traduits en français
- Validation des statuts par étape
- Mise à jour dynamique des options de statut

### Corrections Techniques
- Correction de la génération du numéro de référence
- Ajout de la relation `dossier` dans le modèle Prospect
- Optimisation des requêtes SQL pour le tri et la recherche
- Correction des problèmes d'affichage du sélecteur de prospect

### Prochaines Étapes Prévues
- Ajout d'indicateurs visuels (icônes, couleurs)
- Intégration de la gestion des documents
- Système de rappels et dates limites
