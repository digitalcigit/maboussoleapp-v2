# États des Dossiers : Prospects et Clients

## Vue d'ensemble

Dans notre système, un dossier peut être soit un prospect, soit un client, en fonction de son état d'avancement. Cette documentation détaille la gestion de ces états et les transitions possibles.

## États possibles

### Prospect
- Un dossier est considéré comme prospect lorsque :
  - Il est nouvellement créé (`is_prospect = true`)
  - Le paiement n'a pas encore été effectué (`is_client = false`)
  - Il est en cours de validation ou de traitement

### Client
- Un dossier devient client lorsque :
  - Le paiement des droits d'agent a été effectué
  - `is_client = true` et `is_prospect = false`
  - La date de paiement et le montant sont enregistrés

## Transitions d'états

### De Prospect à Client
- Déclenchée par le paiement des droits d'agent
- Utiliser la méthode `marquerCommeClient($montant)`
- Met à jour automatiquement :
  - Les flags `is_prospect` et `is_client`
  - La date de transition (`client_depuis`)
  - Les informations de paiement

## Informations stockées

### Données personnelles
- Nom et prénom
- Email (unique)
- Téléphone
- Date et lieu de naissance
- Nationalité
- Adresse complète
  - Pays de résidence
  - Ville
  - Code postal

### Données professionnelles
- Profession
- Niveau d'études
- Domaine d'activité
- Expérience professionnelle

### Données de suivi
- Date de début comme prospect
- Date de transition vers client
- Montant payé
- Date de paiement

## Utilisation dans le code

### Filtrage des dossiers
```php
// Obtenir tous les prospects
$prospects = Dossier::prospects()->get();

// Obtenir tous les clients
$clients = Dossier::clients()->get();
```

### Transition d'état
```php
// Marquer un prospect comme client
$dossier->marquerCommeClient(1000.00);
```

## Statistiques et rapports

Les états permettent de générer facilement des statistiques :
- Nombre total de prospects
- Nombre total de clients
- Taux de conversion prospect vers client
- Revenus générés par les conversions
