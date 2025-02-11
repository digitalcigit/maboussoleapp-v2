# Module d'Automatisation des Dossiers

## Fonctionnalités
- Vérification automatique des dossiers en retard
- Envoi d'emails automatiques aux clients
- Création d'activités pour le suivi manager
- Notification des retards dans le système

## Composants
1. **DossierAutomationService** : Service principal gérant l'automatisation
2. **DossierRetardMail** : Template d'email pour les notifications
3. **CheckDelayedDossiersCommand** : Commande artisan pour l'exécution
4. **Tâche planifiée** : Exécution quotidienne à 9h

## Fonctionnement
1. Chaque jour à 9h, le système vérifie les dossiers en attente de documents
2. Pour les dossiers en attente depuis plus de 7 jours :
   - Un email est envoyé au client
   - Une activité est créée pour le manager
   - Le dossier est marqué comme notifié

## Configuration
- L'email est envoyé une seule fois par période d'attente
- Le système utilise le statut "attente_document" comme déclencheur
- Les managers sont notifiés via le système d'activités

## Tests
Pour tester manuellement :
```bash
php artisan dossiers:check-delays
```
