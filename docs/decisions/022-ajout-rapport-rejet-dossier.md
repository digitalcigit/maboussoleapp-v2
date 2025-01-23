# 22. Ajout d'un rapport de rejet pour les dossiers

Date: 2025-01-21

## État

Accepté

## Contexte

Lorsqu'un dossier est rejeté, il est important de pouvoir fournir un rapport détaillé expliquant les raisons du rejet au prospect. Cette information doit être stockée et pourra être utilisée ultérieurement pour l'affichage dans le tableau de bord du prospect.

## Décision

Nous avons décidé de :

1. Créer une nouvelle table `dossier_rejection_reports` pour stocker l'historique des rapports de rejet
2. Implémenter un éditeur Markdown avec prévisualisation en temps réel pour la saisie des rapports
3. Rendre le rapport obligatoire lors du rejet d'un dossier
4. Associer chaque rapport au dossier et à l'utilisateur qui l'a créé

### Structure de la table

```sql
CREATE TABLE dossier_rejection_reports (
    id bigint PRIMARY KEY,
    dossier_id bigint FOREIGN KEY,
    created_by bigint FOREIGN KEY,
    content text,
    sent_at timestamp,
    created_at timestamp,
    updated_at timestamp,
    deleted_at timestamp
);
```

### Interface utilisateur

- Ajout d'un bouton "Rejeter le dossier" dans les actions du dossier
- Modal avec éditeur Markdown pour la saisie du rapport
- Prévisualisation en temps réel du contenu formaté
- Barre d'outils pour la mise en forme (gras, italique, listes, etc.)

## Conséquences

### Avantages

1. Meilleure traçabilité des rejets de dossiers
2. Interface utilisateur intuitive pour la rédaction des rapports
3. Support du formatage Markdown pour une meilleure lisibilité
4. Conservation de l'historique des rapports

### Inconvénients

1. Complexité accrue du code avec l'ajout de la prévisualisation en temps réel
2. Nécessité de former les utilisateurs à la syntaxe Markdown

## Notes techniques

- Utilisation de Filament pour l'interface utilisateur
- Implémentation d'un composant de prévisualisation Markdown personnalisé
- Intégration avec le système de notifications de Filament
- Soft deletes activés pour préserver l'historique
