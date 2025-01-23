# Gestion du rejet des dossiers

## Vue d'ensemble

Le système permet aux administrateurs de rejeter un dossier en fournissant un rapport détaillé. Ce rapport est stocké dans la base de données et pourra être utilisé pour informer le prospect des raisons du rejet.

## Composants principaux

### 1. Base de données

La table `dossier_rejection_reports` stocke les rapports de rejet avec les champs suivants :
- `id` : Identifiant unique du rapport
- `dossier_id` : Référence au dossier concerné
- `created_by` : Référence à l'utilisateur ayant créé le rapport
- `content` : Contenu du rapport au format Markdown
- `sent_at` : Date d'envoi au prospect
- Timestamps standards (created_at, updated_at, deleted_at)

### 2. Modèles

#### DossierRejectionReport
- Utilise le trait SoftDeletes
- Relation avec le modèle Dossier
- Relation avec le modèle User pour le créateur

#### Dossier
- Relation hasMany avec DossierRejectionReport
- Statut spécifique STATUS_SUBMISSION_REJECTED

### 3. Interface utilisateur

#### Action de rejet (RejectDossierAction)
- Accessible depuis la page d'édition du dossier
- Modal avec éditeur Markdown
- Prévisualisation en temps réel
- Validation obligatoire du rapport

#### Composant de prévisualisation Markdown
- Vue Blade personnalisée
- Utilisation de Str::markdown pour le rendu
- Mise à jour en temps réel avec debounce

## Workflow

1. L'administrateur clique sur "Rejeter le dossier"
2. Une modal s'ouvre avec l'éditeur Markdown
3. L'administrateur saisit le rapport avec possibilité de mise en forme
4. La prévisualisation s'actualise automatiquement
5. À la validation :
   - Le rapport est enregistré
   - Le statut du dossier est mis à jour
   - Une notification de confirmation est affichée

## Sécurité

- Seuls les utilisateurs autorisés peuvent rejeter un dossier
- Les rapports sont liés à leur créateur pour la traçabilité
- Soft deletes pour préserver l'historique

## Maintenance

Pour modifier le comportement du rejet :
1. L'action est définie dans `app/Filament/Resources/DossierResource/Actions/RejectDossierAction.php`
2. Le composant de prévisualisation est dans `resources/views/filament/forms/components/markdown-preview.blade.php`
3. La migration de la table est dans `database/migrations/YYYY_MM_DD_create_dossier_rejection_reports_table.php`

## Évolutions futures prévues

1. Intégration avec le tableau de bord du prospect
2. Système de notification par email
3. Possibilité d'ajouter des pièces jointes au rapport
4. Templates prédéfinis pour les motifs de rejet courants
