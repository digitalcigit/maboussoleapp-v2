# Spécification Technique - Portail Candidat

## Architecture Technique

### 1. Modèles et Relations

```php
// User.php
class User extends Authenticatable
{
    // Relations
    public function prospect()
    {
        return $this->hasOne(Prospect::class);
    }
}

// Prospect.php
class Prospect extends Model
{
    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dossiers()
    {
        return $this->hasMany(Dossier::class);
    }
}

// Dossier.php
class Dossier extends Model
{
    // Relations
    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}
```

### 2. Système de Permissions

```php
// Permissions du portail candidat
[
    'portail.access',           // Accès au portail
    'dossier.view.own',        // Voir ses propres dossiers
    'dossier.edit.own',        // Modifier ses propres dossiers
    'document.upload',         // Uploader des documents
    'document.delete.own'      // Supprimer ses propres documents
]
```

### 3. Événements et Notifications

```php
// Events
DocumentUploaded
DossierStatusChanged
DeadlineApproaching

// Notifications
DocumentValidationNotification
DossierProgressNotification
DeadlineReminderNotification
```

## Interfaces Utilisateur

### 1. Tableau de Bord Candidat
```php
// Filament Resource
class CandidatDashboardResource extends Resource
{
    public static function getWidgets(): array
    {
        return [
            DossierProgressWidget::class,
            DocumentsManquantsWidget::class,
            CalendrierEcheancesWidget::class
        ];
    }
}
```

### 2. Gestion des Documents
```php
// Upload de documents
class DocumentUploader extends Component
{
    public function upload(TemporaryUploadedFile $file)
    {
        // Validation
        // Classification automatique
        // Stockage
        // Notification aux conseillers
    }
}
```

## Processus Métier

### 1. Création de Compte Candidat
1. Création du dossier initial
2. Génération automatique du compte
3. Envoi email d'invitation
4. Attribution des permissions de base

### 2. Workflow Documents
1. Upload par le candidat
2. Validation automatique du format
3. Notification au conseiller
4. Validation manuelle
5. Feedback au candidat

### 3. Suivi des Étapes
1. Mise à jour automatique de la progression
2. Génération des notifications
3. Mise à jour du tableau de bord

## Sécurité

### 1. Authentification
- Utilisation de Sanctum pour l'API
- Sessions pour l'interface web
- Tokens d'accès temporaires pour les uploads

### 2. Autorisation
- Policies strictes par dossier
- Vérification systématique des propriétaires
- Journalisation des accès sensibles

### 3. Validation des Documents
- Scan antivirus
- Vérification des types MIME
- Limites de taille configurables

## Configuration Système

### 1. Variables d'Environnement
```env
PORTAL_DOCUMENT_MAX_SIZE=10240
PORTAL_ALLOWED_EXTENSIONS=pdf,jpg,png
PORTAL_NOTIFICATION_EMAIL=notifications@maboussole.ci
```

### 2. File d'Attente
```php
// Jobs
ProcessDocumentUpload
GenerateDocumentThumbnail
SendNotificationBatch
```

## Monitoring

### 1. Métriques à Suivre
- Temps de traitement des dossiers
- Taux de complétion des profils
- Taux de réussite des uploads
- Temps de réponse du système

### 2. Alertes
- Échecs d'upload répétés
- Dépassement des quotas
- Erreurs de validation fréquentes

## Plan de Déploiement

### 1. Prérequis
- Mise à jour des migrations
- Configuration du stockage
- Configuration des emails

### 2. Étapes
1. Déploiement base de données
2. Mise à jour des rôles
3. Activation progressive des fonctionnalités
4. Migration des données existantes
