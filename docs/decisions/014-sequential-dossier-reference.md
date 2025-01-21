# ADR-014 : Numérotation Séquentielle des Références de Dossiers

## Contexte
Actuellement, les références des dossiers sont générées de manière aléatoire. Pour une meilleure organisation et traçabilité, nous souhaitons mettre en place une numérotation séquentielle (DOS-001, DOS-002, etc.).

## Options Considérées

### Option 1 : Utilisation d'un champ auto-incrémenté
- Utiliser l'ID auto-incrémenté de la table
- Formater la référence comme "DOS-{ID padded}"
- Simple mais peut créer des "trous" dans la numérotation en cas de suppression

### Option 2 : Table dédiée de compteur
- Créer une table séparée pour gérer le compteur
- Plus complexe mais garantit une séquence continue
- Meilleure gestion des transactions concurrentes

### Option 3 : Calcul basé sur le dernier numéro
- Rechercher la dernière référence et incrémenter
- Simple mais risque de problèmes de concurrence
- Pas optimal pour les performances

## Décision
Nous avons choisi l'Option 2 avec une table dédiée de compteur car :
- Garantit une séquence continue sans trous
- Gère correctement les accès concurrents
- Permet une extension future (compteurs par année, par type, etc.)

## Implémentation
1. Création d'une table `reference_counters`
2. Implémentation d'un service dédié pour la génération
3. Utilisation de verrous pour la gestion concurrentielle
4. Format : DOS-XXX (padding sur 3 chiffres)

## Conséquences
### Positives
- Meilleure organisation et lisibilité des références
- Facilite le suivi chronologique des dossiers
- Structure extensible pour besoins futurs

### Négatives
- Complexité accrue du code
- Légère surcharge de performance
- Migration nécessaire des données existantes
