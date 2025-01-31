# Configuration HTTPS pour le serveur de test

## Contexte
Le serveur de test `gestion.maboussole.net` nécessite une configuration HTTPS pour :
- Sécuriser les données des utilisateurs
- Respecter les bonnes pratiques de sécurité
- Offrir une expérience proche de la production

## Tâches à réaliser

### 1. Certificat SSL
- [ ] Obtenir un certificat SSL (Let's Encrypt recommandé)
- [ ] Installer le certificat sur le serveur
- [ ] Configurer le renouvellement automatique

### 2. Configuration Serveur
- [ ] Configurer Nginx/Apache pour utiliser le certificat
- [ ] Rediriger tout le trafic HTTP vers HTTPS
- [ ] Mettre à jour les en-têtes de sécurité

### 3. Application
- [ ] Mettre à jour APP_URL dans .env vers https://gestion.maboussole.net
- [ ] Vérifier les URLs codées en dur dans l'application
- [ ] Tester les fonctionnalités après passage en HTTPS

## Priorité
MOYENNE - À réaliser avant les premiers tests clients

## Notes
- Prévoir une fenêtre de maintenance
- Faire une sauvegarde avant la modification
- Documenter la procédure pour la production
