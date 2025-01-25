# Guide d'Utilisation du Débogage Systématique

## Processus de Débogage

### 1. Observation
#### Comment collecter les informations
- Examiner les logs d'erreur
- Vérifier les messages d'erreur
- Noter le contexte exact
- Documenter les étapes de reproduction

#### Exemple pratique
```
Erreur observée : 403 Forbidden
Contexte :
- URL : /portail/mon-dossier/7/edit
- Utilisateur : ID 14 (rôle: candidat)
- Timestamp : 2025-01-25 11:31:15
```

### 2. Hypothèse
#### Formation d'hypothèses
- Basées sur les observations
- Testables et spécifiques
- Priorisées par probabilité

#### Exemple pratique
```
Hypothèses :
1. L'utilisateur n'a pas les bonnes permissions
2. La relation prospect-dossier est incorrecte
3. Le middleware bloque l'accès
```

### 3. Expérimentation
#### Mise en place des tests
- Ajout de points de debug
- Tests unitaires ciblés
- Validation des hypothèses

#### Exemple pratique
```php
// Ajout de logs de debug
Log::debug('Vérification des permissions', [
    'user_id' => auth()->id(),
    'roles' => auth()->user()->roles->pluck('name'),
    'dossier_id' => $dossier->id,
    'prospect_id' => auth()->user()->prospect->id ?? null
]);
```

### 4. Analyse
#### Évaluation des résultats
- Examiner les logs de debug
- Comparer avec les attentes
- Documenter les découvertes

#### Exemple pratique
```
Résultats d'analyse :
✓ Utilisateur a le rôle 'candidat'
✗ Relation prospect->dossier manquante
✓ Permissions correctement définies
```

### 5. Solution
#### Application des corrections
- Implémenter la solution
- Tester la correction
- Documenter les changements

#### Exemple pratique
```php
// Solution implémentée
public function view(User $user, Dossier $dossier): bool
{
    return $user->prospect && 
           $user->prospect->dossier_id === $dossier->id;
}
```

## Bonnes Pratiques

### Documentation
- Noter chaque étape du processus
- Maintenir un journal de débogage
- Partager les leçons apprises

### Communication
- Informer l'équipe des progrès
- Partager les découvertes importantes
- Documenter les solutions

### Prévention
- Créer des tests de régression
- Améliorer le monitoring
- Mettre à jour la documentation

## Exercices Pratiques

### Exercice 1 : Analyse d'Erreur
```
Scénario : Erreur 403 sur l'accès au dossier
Objectif : Appliquer la méthode de débogage systématique
Tâches :
1. Collecter les informations pertinentes
2. Former des hypothèses testables
3. Mettre en place des tests
4. Analyser les résultats
5. Proposer une solution
```

### Exercice 2 : Documentation
```
Scénario : Bug résolu, documentation nécessaire
Objectif : Créer une documentation complète
Tâches :
1. Décrire le problème initial
2. Documenter le processus de débogage
3. Expliquer la solution
4. Créer des tests de régression
```

## Ressources Additionnelles
- Logs système : /storage/logs/debug.log
- Documentation technique : /docs/technical/
- Guide de débogage : /docs/debugging/
- Cas d'études : /docs/learning/modules/debugging/case-studies/
