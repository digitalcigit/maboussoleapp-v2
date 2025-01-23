# Workflow des Statuts de Dossier

## Vue d'Ensemble
Le workflow des dossiers est divisé en 4 étapes principales, chacune avec ses propres statuts spécifiques.

## 1. Étape d'Analyse
1. En attente de documents
2. Analyse en cours
3. Analyse terminée

## 2. Étape d'Admission
1. En attente de documents physiques
2. Documents physiques reçus
3. Frais d'admission payés
4. Dossier soumis
5. Soumission acceptée/rejetée

## 3. Étape de Paiement
1. En attente de paiement des frais d'agence
2. Frais d'agence payés
3. Paiement partiel scolarité
4. Paiement total scolarité
5. Dossier abandonné (optionnel)

## 4. Étape Visa
1. Dossier visa prêt
2. Frais visa payés
3. Visa soumis
4. Visa obtenu/refusé
5. Frais finaux payés

## Transitions Automatiques

### Passage à l'Étape d'Admission
- Statut initial : "En attente de documents physiques"
- Déclencheur : Analyse terminée

### Passage à l'Étape de Paiement
- Statut initial : "En attente de paiement des frais d'agence"
- Déclencheur : Soumission acceptée

### Passage à l'Étape Visa
- Statut initial : "Dossier visa prêt"
- Déclencheur : Paiement total effectué

## Implémentation Technique

### Dans le Modèle Dossier
```php
// Définition des étapes
const STEP_ANALYSIS = 1;
const STEP_ADMISSION = 2;
const STEP_PAYMENT = 3;
const STEP_VISA = 4;

// Exemples de statuts
const STATUS_WAITING_DOCS = 'attente_documents';
const STATUS_WAITING_PHYSICAL_DOCS = 'attente_documents_physiques';
const STATUS_WAITING_AGENCY_PAYMENT = 'attente_paiement_frais_agence';
```

### Gestion des Transitions
```php
protected function getInitialStatus($step)
{
    return match ($step) {
        self::STEP_ADMISSION => self::STATUS_WAITING_PHYSICAL_DOCS,
        self::STEP_PAYMENT => self::STATUS_WAITING_AGENCY_PAYMENT,
        self::STEP_VISA => self::STATUS_VISA_DOCS_READY,
        default => null,
    };
}
```

## Migrations
Les changements de structure des statuts sont gérés par des migrations dédiées :

1. `2025_01_20_001515_add_waiting_physical_docs_status.php`
   - Ajout du statut d'attente des documents physiques
   - Migration des dossiers existants

2. `2025_01_20_003346_add_waiting_agency_payment_status.php`
   - Ajout du statut d'attente de paiement des frais d'agence
   - Migration des dossiers existants

## Bonnes Pratiques
1. Toujours utiliser les constantes définies dans le modèle
2. Vérifier la validité des transitions de statut
3. Maintenir la cohérence des statuts entre les étapes
4. Documenter tout nouveau statut ajouté
