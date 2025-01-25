# ADR 030 : Implémentation de la Gestion de Dossier dans le Portail Candidat

## Contexte
Le portail candidat nécessite une interface permettant aux candidats de gérer leur dossier. Cette interface doit être distincte de l'interface administrative tout en partageant les mêmes données et logiques métier.

## État
Accepté

## Décision
Nous avons décidé de créer un Resource Filament dédié dans le namespace `App\Filament\PortailCandidat\Resources` pour la gestion des dossiers côté candidat.

### Justification
1. **Séparation des responsabilités**
   - Interface administrative complète dans `App\Filament\Resources`
   - Interface candidat simplifiée dans `App\Filament\PortailCandidat\Resources`
   - Même modèle de données (`Dossier`) mais vues différentes

2. **Réutilisation du code**
   - Utilisation des mêmes modèles et validations
   - Partage des composants Filament
   - Centralisation de la logique métier

3. **Sécurité et contrôle d'accès**
   - Permissions distinctes pour les candidats
   - Limitation des actions possibles
   - Filtrage automatique pour ne voir que son propre dossier

### Approche technique
1. **Structure**
   ```
   app/Filament/PortailCandidat/
   ├── Resources/
   │   └── DossierResource/
   │       ├── Pages/
   │       │   └── EditDossier.php
   │       └── DossierResource.php
   ```

2. **Fonctionnalités limitées**
   - Lecture seule pour certains champs (référence, statut)
   - Upload et remplacement de documents
   - Mise à jour des informations personnelles
   - Visualisation de la progression

## Conséquences
### Positives
1. Interface adaptée aux besoins des candidats
2. Maintenance facilitée par la réutilisation du code
3. Sécurité renforcée par la séparation des interfaces
4. Évolution indépendante possible des deux interfaces

### Négatives
1. Duplication minimale de code pour les configurations spécifiques
2. Nécessité de maintenir deux Resources pour le même modèle

## Notes d'implémentation
1. Utiliser les Gates Laravel pour les permissions
2. Réutiliser les composants de formulaire existants
3. Implémenter des observers pour les notifications
4. Ajouter des tests spécifiques pour l'interface candidat
