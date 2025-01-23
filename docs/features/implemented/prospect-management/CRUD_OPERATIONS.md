# Opérations CRUD - Gestion des Prospects

## Actions Individuelles

### Menu d'actions (⋮)
Le menu d'actions vertical (trois points) propose les options suivantes :

1. **Modifier**
   - Accès au formulaire d'édition complet du prospect
   - Nécessite la permission `prospects.edit`

2. **Convertir en client**
   - Transforme le prospect en client
   - Nécessite la permission `clients.create`
   - Non visible si le prospect est déjà converti
   - Processus de conversion :
     - Création d'un nouveau client avec les informations du prospect
     - Mise à jour du statut du prospect à "Converti"
     - Redirection vers la page du nouveau client

## Actions en Masse

Accessibles via la sélection multiple de prospects :

1. **Suppression en masse**
   - Supprime plusieurs prospects simultanément
   - Utilise le SoftDelete de Laravel

2. **Mise à jour en masse**
   - Modification du statut pour plusieurs prospects
   - Options disponibles :
     - Nouveau
     - En analyse
     - Approuvé
     - Refusé
     - Converti

## Interface de Liste

### Colonnes affichées
- Référence (PROS-XXXXX)
- Prénom
- Nom
- Email
- Téléphone
- Statut (avec code couleur)
- Assigné à
- Partenaire
- Date limite
- Date de création

### Fonctionnalités de la liste
- Tri par colonnes
- Recherche globale
- Filtres par statut et source
- Pagination
- Persistance des préférences de tri en session

## Formulaire de Création/Édition

### Sections du formulaire

1. **Informations Personnelles**
   - Numéro de référence (auto-généré)
   - Prénom
   - Nom
   - Email
   - Téléphone
   - Date de naissance
   - Profession
   - Niveau d'études

2. **Situation Professionnelle**
   - Localisation actuelle
   - Domaine actuel
   - Domaine souhaité
   - Destination souhaitée

3. **Contact d'urgence**
   - Nom du contact
   - Relation
   - Téléphone

4. **Suivi Commercial**
   - Statut
   - Assigné à
   - Partenaire
   - Code commercial
   - Date limite d'analyse
   - Notes

## Système de Notifications

Des notifications sont envoyées pour :
- Création réussie d'un prospect
- Modification des informations
- Conversion en client
- Erreurs (ex: tentative de conversion d'un prospect déjà converti)

## Permissions Requises

- `prospects.view` : Voir la liste des prospects
- `prospects.create` : Créer un nouveau prospect
- `prospects.edit` : Modifier un prospect existant
- `prospects.delete` : Supprimer un prospect
- `clients.create` : Convertir un prospect en client
