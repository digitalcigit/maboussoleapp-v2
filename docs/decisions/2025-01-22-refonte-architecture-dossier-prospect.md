# Refonte de l'Architecture Dossier-Prospect

Date: 2025-01-22
Statut: Proposé (À implémenter après la v1)

## Contexte

Suite à la simplification du formulaire de création de dossier, nous avons identifié plusieurs problèmes architecturaux dans la relation entre les dossiers et les prospects :
- Couplage fort entre la création de dossier et de prospect
- Gestion complexe des activités et événements
- Incohérences dans la validation des données
- Difficultés à maintenir la flexibilité des champs obligatoires/optionnels

## Problèmes Actuels

1. **Couplage Fort**
   - La création d'un dossier force la création d'un prospect
   - Les validations sont interdépendantes
   - Les modifications impactent les deux entités

2. **Gestion des Événements**
   - Utilisation de traits pour le tracking des changements
   - Manque de flexibilité dans la gestion des activités
   - Risques d'effets de bord non désirés

3. **Validation des Données**
   - Incohérence entre les contraintes du formulaire et de la base de données
   - Difficulté à gérer des champs optionnels/obligatoires selon le contexte

## Solution Proposée (Post v1)

### 1. Architecture des Services
```
app/
├── Services/
│   ├── Prospect/
│   │   ├── ProspectService.php
│   │   └── ProspectCreationService.php
│   └── Dossier/
│       ├── DossierService.php
│       └── DossierCreationService.php
```

### 2. Système d'Événements
```php
// Events
ProspectCreated
ProspectAssigned
DossierCreated

// Listeners
LogProspectActivity
UpdateDossierStatus
NotifyAssignedUser
```

### 3. Validation Contextuelle
```php
class CreateDossierRequest extends FormRequest
{
    public function rules()
    {
        return $this->context === 'minimal' 
            ? $this->getMinimalRules()
            : $this->getFullRules();
    }
}
```

## Plan de Migration

### Phase 1 : Services (2-3 jours)
- Création des services métier
- Séparation des responsabilités
- Tests unitaires

### Phase 2 : Événements (1-2 jours)
- Mise en place du système d'événements
- Migration depuis les traits
- Tests d'intégration

### Phase 3 : Base de Données (1 jour)
- Révision des contraintes
- Migration des données
- Tests de migration

### Phase 4 : Tests (1-2 jours)
- Tests end-to-end
- Validation fonctionnelle
- Documentation

## Décision

Pour respecter les délais de la v1, nous allons :
1. Corriger les bugs immédiats liés aux champs obligatoires
2. Documenter les problèmes architecturaux identifiés
3. Planifier cette refonte pour la v2 du projet

## Conséquences

### Court Terme (v1)
- Corrections ponctuelles des bugs
- Maintien de l'architecture actuelle
- Documentation des points d'amélioration

### Long Terme (v2)
- Code plus maintenable
- Meilleure séparation des responsabilités
- Tests plus robustes
- Flexibilité accrue pour les évolutions futures

## Notes Techniques

La refonte devra prendre en compte :
- La rétrocompatibilité avec les données existantes
- La période de transition entre les deux architectures
- La formation de l'équipe aux nouvelles pratiques

## Prochaines Étapes

1. Finaliser la v1 avec les corrections urgentes
2. Planifier un workshop de revue d'architecture pour la v2
3. Établir un calendrier détaillé de migration
4. Préparer la documentation technique complète
