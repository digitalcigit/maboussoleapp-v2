# Workflow des Dossiers

## Vue d'ensemble

Le module de gestion des dossiers est au cœur de l'application MaBoussole. Il gère le cycle de vie complet d'un dossier, depuis sa création initiale jusqu'à sa complétion, en passant par différentes étapes de validation et de traitement.

## Points clés

### 1. Structure du workflow

Le workflow est composé de 7 étapes principales :
- Création initiale
- Collecte des documents
- Validation
- Paiement
- Traitement
- Complétion
- Rejet (cas particulier)

### 2. Statut Prospect/Client

La distinction entre prospect et client est déterminée automatiquement par l'étape actuelle du workflow :
- Prospect : étapes 1-3 (avant paiement)
- Client : étapes 4-6 (après paiement)

### 3. Gestion des documents

Chaque étape du workflow peut nécessiter des documents spécifiques qui doivent être fournis et validés avant de passer à l'étape suivante.

### 4. Validation et contrôles

Des contrôles automatiques et manuels sont effectués à chaque étape pour assurer la qualité et la conformité des dossiers.

## Composants principaux

1. `Dossier.php` : Modèle principal gérant la logique métier
2. `DossierStep.php` : Gestion des étapes du workflow
3. `DossierResource.php` : Interface d'administration Filament
4. `DossierProgressWidget.php` : Widget de suivi de progression

## Intégration

Le module s'intègre avec :
- Le système d'authentification
- La gestion des documents
- Le système de paiement
- Les notifications

## Documentation associée

- [Concepts fondamentaux](concept.md)
- [Guide d'implémentation](implementation.md)
- [Guide d'utilisation](usage.md)
- [Résolution des problèmes](troubleshooting.md)
- [Études de cas](case-studies/)
