# Spécification - Gestion de Dossier (Portail Candidat)

## Contexte
Le portail candidat nécessite une interface permettant aux candidats de gérer leur dossier de manière autonome, tout en gardant une cohérence avec l'interface administrative existante.

## Objectifs
- Permettre aux candidats de gérer leurs informations et documents
- Maintenir une séparation claire entre les accès admin et candidat
- Réutiliser les composants existants pour une maintenance facilitée et une centralisation des ressources

## Fonctionnalités

### 1. Interface "Gérer mon dossier"

#### Informations visibles
- Informations personnelles du candidat
- Documents requis et leur statut
- Montants à payer (frais d'agence et scolarité)
- État d'avancement du dossier
- Preuves de paiement soumises
- Contact d'urgence

#### Informations masquées
- Notes internes
- Champs administratifs (assignation, référence interne)
- Validations internes des documents

#### Actions autorisées
- Mise à jour des informations personnelles
- Upload/remplacement de documents
- Téléchargement des documents soumis
- Consultation des montants à payer
- Soumission des preuves de paiement

### 2. Workflow

#### Étape 1 : Analyse
- Soumission des documents initiaux
- Vérification des informations personnelles

#### Étape 2 : Admission
- Soumission des documents complémentaires
- Paiement des frais d'admission

#### Étape 3 : Paiement
- Visualisation des montants (agence et scolarité)
- Upload des preuves de paiement

#### Étape 4 : Visa
- Soumission des documents pour le visa
- Suivi du processus visa

## Aspects Techniques

### Relations entre Modèles

#### Relation Prospect-Dossier
La relation entre les prospects et les dossiers est bidirectionnelle :

```php
// Dans Prospect.php
public function dossier()
{
    return $this->belongsTo(Dossier::class);
}

// Dans Dossier.php
public function prospect()
{
    return $this->belongsTo(Prospect::class);
}
```

Structure de la base de données :
- Table `prospects` : Contient `dossier_id` (nullable, foreign key)
- Table `dossiers` : Contient `prospect_id` (foreign key)

Cette relation permet :
- Une navigation facile entre prospect et dossier
- Une meilleure intégrité des données
- Une gestion simplifiée des permissions

### Système de Permissions

#### Permissions du Portail Candidat
Les permissions sont gérées via les Gates Laravel :

```php
// Vérification de l'accès à un dossier
Gate::define('portail-candidat.dossier.view', function ($user, $dossier) {
    $hasRole = $user->hasRole('portail_candidat');
    $hasDossier = $user->prospect && $dossier->prospect_id === $user->prospect->id;
    return $hasRole && $hasDossier;
});
```

Vérifications effectuées :
1. L'utilisateur a le rôle "portail_candidat"
2. L'utilisateur a un prospect associé
3. Le prospect de l'utilisateur correspond au dossier

#### Permissions Disponibles
- `portail-candidat.dossier.viewAny` : Voir la liste des dossiers
- `portail-candidat.dossier.view` : Voir un dossier spécifique
- `portail-candidat.dossier.update` : Modifier un dossier
- `portail-candidat.dossier.create` : Créer un nouveau dossier

### Architecture
- Namespace dédié : `App\Filament\PortailCandidat`
- Réutilisation des modèles existants
- Permissions spécifiques via Spatie
- Composants Filament partagés avec l'admin

### Sécurité
- Accès limité aux données personnelles
- Validation des types de fichiers
- Vérification des permissions à chaque action

### Interface Utilisateur
- Design cohérent avec l'interface administrative
- Messages d'aide contextuels
- Indicateurs visuels de progression
- Formulaires adaptés au contexte candidat

## Validation des Documents
- Taille maximale : 5MB par document
- Formats acceptés : PDF, JPG, PNG
- Nommage automatique des fichiers
- Vérification antivirus

## Notifications
- Confirmation de soumission de document
- Alerte de document manquant
- Rappel de paiement
- Mise à jour de statut

## Prochaines Étapes
1. Implémentation de la page "Gérer mon dossier"
2. Tests utilisateurs
3. Retours d'expérience et ajustements
4. Documentation utilisateur
