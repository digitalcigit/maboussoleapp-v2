# Décision : Simplification des Statuts de Prospect

## Contexte
Le système initial utilisait un ensemble plus complexe de statuts pour les prospects, incluant des états comme "Nouveau", "Approuvé", "Rejeté" et "Converti". Cette approche s'est révélée trop complexe et ne reflétait pas efficacement le processus réel de traitement des prospects.

## Décision
Nous avons décidé de simplifier le système en utilisant trois statuts principaux qui suivent le flux naturel du processus d'analyse :
1. En attente de documents
2. Analyse en cours
3. Analyse terminée

## Justification
### Avantages
1. **Clarté du Processus**
   - Chaque statut représente une étape claire et distincte
   - Les utilisateurs comprennent immédiatement où en est le dossier
   - Le workflow est plus intuitif et suit la logique métier

2. **Simplicité d'Utilisation**
   - Réduction de la complexité pour les utilisateurs
   - Moins de risques d'erreurs dans le changement de statut
   - Interface plus claire et plus facile à naviguer

3. **Efficacité Opérationnelle**
   - Meilleure visibilité sur l'état réel des dossiers
   - Facilite le suivi des dossiers en attente
   - Permet une meilleure allocation des ressources

4. **Maintenance Technique**
   - Code plus simple à maintenir
   - Moins de cas particuliers à gérer
   - Réduction de la complexité des requêtes

### Inconvénients Potentiels
1. **Perte de Granularité**
   - Moins de détails sur l'historique des statuts
   - Impossibilité de marquer explicitement les prospects rejetés

2. **Migration Nécessaire**
   - Besoin de mettre à jour les données existantes
   - Formation des utilisateurs aux nouveaux statuts

## Alternatives Considérées
1. **Conserver l'Ancien Système**
   - Plus complexe mais plus détaillé
   - Difficile à maintenir et à utiliser efficacement

2. **Système à Deux États**
   - Trop simpliste
   - Ne reflète pas suffisamment le processus

3. **Système avec Sous-statuts**
   - Trop complexe
   - Risque de confusion pour les utilisateurs

## Impact sur l'Expérience Utilisateur
1. **Interface Plus Claire**
   - Badges colorés intuitifs
   - Icônes représentatives
   - Filtres simplifiés

2. **Workflow Plus Naturel**
   - Progression logique des statuts
   - Actions contextuelles appropriées
   - Meilleure compréhension du processus

## Conclusion
La simplification des statuts de prospect améliore significativement l'utilisabilité du système tout en maintenant les fonctionnalités essentielles. Cette approche plus simple et plus directe devrait conduire à une meilleure adoption par les utilisateurs et une réduction des erreurs.
