# Gestion des Statuts des Prospects

## Vue d'ensemble
Cette fonctionnalité simplifie le workflow de gestion des prospects en introduisant trois statuts clairs et distincts qui reflètent le processus d'analyse des dossiers.

## Statuts
### 1. En attente de documents (STATUS_WAITING_DOCS)
- **Code**: `attente_documents`
- **Couleur**: Warning (Orange)
- **Icône**: `heroicon-o-clock`
- **Description**: Le prospect a été créé mais n'a pas encore fourni tous les documents nécessaires.

### 2. Analyse en cours (STATUS_ANALYZING)
- **Code**: `analyse_en_cours`
- **Couleur**: Primary (Bleu)
- **Icône**: `heroicon-o-document-magnifying-glass`
- **Description**: Tous les documents ont été reçus et sont en cours d'analyse par l'équipe.

### 3. Analyse terminée (STATUS_ANALYZED)
- **Code**: `analyse_terminee`
- **Couleur**: Success (Vert)
- **Icône**: `heroicon-o-check-circle`
- **Description**: L'analyse du dossier est terminée et le prospect peut être converti en client.

## Workflow
1. À la création d'un prospect, son statut est automatiquement défini à "En attente de documents"
2. Une fois tous les documents reçus, le statut passe à "Analyse en cours"
3. Après l'analyse complète du dossier, le statut passe à "Analyse terminée"
4. Seuls les prospects avec le statut "Analyse terminée" peuvent être convertis en clients

## Interface Utilisateur
- Les statuts sont affichés avec des badges colorés dans la liste des prospects
- Le filtre de statut permet de filtrer rapidement les prospects selon leur état
- La mise à jour en masse permet de modifier le statut de plusieurs prospects à la fois
- L'action de conversion en client n'est visible que pour les prospects analysés

## Modifications Techniques
### ProspectResource.php
- Mise à jour des options de statut dans le formulaire
- Configuration des badges avec couleurs et icônes
- Mise à jour des filtres pour refléter les nouveaux statuts
- Modification de l'action de conversion pour vérifier le statut "Analyse terminée"

### Prospect.php (Model)
- Définition des constantes pour les nouveaux statuts
- Suppression des anciens statuts non utilisés

## Impacts sur les Performances
- Réduction du nombre de statuts possibles
- Simplification des requêtes de filtrage
- Optimisation de l'interface utilisateur

## Bonnes Pratiques
- Toujours utiliser les constantes définies dans le modèle Prospect pour référencer les statuts
- Vérifier le statut avant d'autoriser la conversion en client
- Maintenir la cohérence des couleurs et icônes dans toute l'interface
