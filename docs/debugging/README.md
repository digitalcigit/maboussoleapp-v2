# Documentation de Débogage - MaBoussole CRM v2

Ce dossier contient la documentation détaillée des sessions de débogage du projet. L'objectif est de capitaliser sur l'expérience acquise lors de la résolution des problèmes pour faciliter le débogage futur.

## Structure

```
debugging/
├── middleware/     # Problèmes liés aux middlewares et à la gestion des requêtes
├── database/      # Problèmes liés à la base de données et aux modèles
└── ui/            # Problèmes d'interface utilisateur et de rendu
```

## Principes de Documentation

Chaque document de débogage doit suivre la structure suivante :

1. **Contexte Initial**
   - Description du problème
   - Impact sur l'application
   - Environnement concerné

2. **Chronologie**
   - Date et heure des interventions
   - Tentatives de résolution
   - Résultats obtenus

3. **Solution**
   - Description de la solution retenue
   - Code modifié
   - Tests effectués

4. **Leçons Apprises**
   - Points clés à retenir
   - Pièges à éviter
   - Recommandations pour l'avenir

## Convention de Nommage

Les fichiers de documentation suivent la convention :
```
[CATEGORIE]_[COMPOSANT].md
```

Exemple : 
- `DEBUG_MIDDLEWARE.md`
- `DATABASE_RELATIONS.md`
- `UI_RENDERING.md`
