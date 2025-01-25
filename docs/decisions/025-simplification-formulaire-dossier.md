# Simplification du Formulaire de Création de Dossier

Date: 2025-01-22
Statut: Accepté

## Contexte

Le formulaire de création de dossier contenait de nombreux champs obligatoires, ce qui pouvait être un frein à l'adoption d'une approche participative où les candidats complètent progressivement leur profil.

## Décision

Nous avons décidé de réduire les champs obligatoires au strict minimum nécessaire pour initier un dossier :

### Champs Obligatoires Maintenus
- Étape actuelle
- Statut actuel
- Prénom du prospect
- Nom du prospect
- Email du prospect

### Cas Particulier : Champ "Assigné à"
Le champ "Assigné à" reste techniquement obligatoire mais est géré de manière transparente :
- Valeur par défaut : utilisateur connecté
- Invisible pour les utilisateurs sans permission d'assignation
- Modifiable uniquement par les rôles autorisés
- Garantit la traçabilité et la responsabilité des dossiers

### Champs Rendus Optionnels
- Téléphone
- Date de naissance
- Profession actuelle
- Niveau d'études
- Filière souhaitée
- Destination souhaitée
- Informations de contact d'urgence

## Conséquences

### Positives
- Création de dossier plus rapide et plus fluide
- Réduction des barrières à l'entrée
- Possibilité pour les candidats de compléter leur profil progressivement
- Meilleure adoption du portail candidat

### Négatives
- Risque de dossiers incomplets
- Nécessité de relancer les candidats pour compléter leurs informations

## Implémentation

Les modifications ont été apportées dans le fichier `DossierResource.php` en retirant l'attribut `required()` des champs non essentiels tout en maintenant leur structure et leurs validations existantes.
