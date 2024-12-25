# Système de Notifications - Planification

## Objectif
Implémenter un système de notifications complet pour informer les utilisateurs des événements importants dans le CRM.

## Spécifications

### 1. Types de Notifications
- **Prospect**
  * Nouveau prospect assigné
  * Changement de statut
  * Date limite d'analyse approchant
  
- **Client**
  * Paiement reçu
  * Changement de statut visa
  * Document expirant bientôt
  
- **Activité**
  * Rappel d'activité planifiée
  * Activité en retard
  * Mention dans une note

### 2. Canaux de Notification
- Email (prioritaire)
- Interface utilisateur (temps réel)
- Résumé quotidien (digest)

### 3. Structure Technique

```php
class Notification extends Model
{
    const TYPE_PROSPECT_ASSIGNED = 'prospect_assigne';
    const TYPE_STATUS_CHANGED = 'statut_change';
    const TYPE_DEADLINE_APPROACHING = 'deadline_approche';
    const TYPE_PAYMENT_RECEIVED = 'paiement_recu';
    const TYPE_VISA_UPDATE = 'visa_maj';
    const TYPE_DOCUMENT_EXPIRING = 'document_expire';
    const TYPE_ACTIVITY_REMINDER = 'rappel_activite';
    const TYPE_ACTIVITY_OVERDUE = 'activite_retard';
    const TYPE_MENTION = 'mention';
    
    const STATUS_UNREAD = 'non_lu';
    const STATUS_READ = 'lu';
    const STATUS_ARCHIVED = 'archive';
}
```

### 4. Files d'Attente
- Queue dédiée pour les emails
- Queue séparée pour les notifications temps réel
- Gestion des tentatives et des échecs

## Plan d'Implémentation

### Phase 1 : Infrastructure
1. Création des modèles et migrations
2. Configuration des queues
3. Templates d'emails

### Phase 2 : Notifications de Base
1. Notifications de prospects
2. Notifications de clients
3. Interface utilisateur

### Phase 3 : Fonctionnalités Avancées
1. Notifications temps réel
2. Préférences utilisateur
3. Résumés quotidiens

## Points d'Attention

### Technique
- Performance des requêtes
- Gestion de la concurrence
- Monitoring des queues

### Métier
- Format des messages en français
- Priorités des notifications
- Règles de notification par rôle

## Tests Requis

### Unitaires
- Création des notifications
- Formatage des messages
- Règles de routage

### Intégration
- Envoi d'emails
- Notifications temps réel
- Files d'attente

### End-to-End
- Flux complet de notification
- Interface utilisateur
- Préférences utilisateur

## Notes pour l'IA
1. **Conventions**
   - Messages en français
   - Statuts cohérents avec le reste du système
   - Documentation des événements déclencheurs

2. **Points de Vigilance**
   - Vérifier les permissions
   - Gérer les erreurs d'envoi
   - Maintenir la cohérence des statuts
