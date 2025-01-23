# Guide d'Implémentation des Flux Fonctionnels

## Méthodologie Pratique

### 1. Analyse Préliminaire

#### a. Collecte d'Informations
```php
// Exemple de checklist
$informationsRequises = [
    'acteurs' => ['Qui sont les utilisateurs ?', 'Quels systèmes sont impliqués ?'],
    'actions' => ['Quelles sont les étapes principales ?', 'Quels sont les points de décision ?'],
    'données' => ['Quelles informations sont nécessaires ?', 'Où sont-elles stockées ?'],
    'règles' => ['Quelles sont les conditions ?', 'Quelles validations appliquer ?']
];
```

#### b. Identification des Acteurs
```php
// Exemple de notre projet Ma Boussole
class ActeursSysteme {
    const TYPES = [
        'CANDIDAT' => [
            'role' => 'portail_candidat',
            'actions' => ['voir_dossier', 'upload_document', 'modifier_profil']
        ],
        'CONSEILLER' => [
            'role' => 'conseiller',
            'actions' => ['créer_dossier', 'valider_document', 'assigner_tâches']
        ],
        'SYSTEME' => [
            'role' => 'automatique',
            'actions' => ['notifier', 'générer_pdf', 'vérifier_statut']
        ]
    ];
}
```

### 2. Modélisation du Flux

#### a. Structure de Base
```php
class FluxFonctionnel {
    private $etapes = [];
    private $conditions = [];
    private $transitions = [];
    
    public function ajouterEtape($nom, $description, $acteur) {
        $this->etapes[] = [
            'nom' => $nom,
            'description' => $description,
            'acteur' => $acteur
        ];
    }
    
    public function ajouterCondition($etapeSource, $condition, $etapeCible) {
        $this->conditions[] = [
            'source' => $etapeSource,
            'condition' => $condition,
            'cible' => $etapeCible
        ];
    }
}
```

#### b. Exemple Concret : Création de Compte Candidat
```php
$flux = new FluxFonctionnel();

// Définition des étapes
$flux->ajouterEtape(
    'création_dossier',
    'Le conseiller crée un nouveau dossier',
    ActeursSysteme::TYPES['CONSEILLER']
);

$flux->ajouterEtape(
    'génération_compte',
    'Le système génère un compte utilisateur',
    ActeursSysteme::TYPES['SYSTEME']
);

// Ajout des conditions
$flux->ajouterCondition(
    'création_dossier',
    'dossier_validé == true',
    'génération_compte'
);
```

### 3. Validation et Tests

#### a. Points de Vérification
```php
class ValidationFlux {
    public static function vérifierCohérence($flux) {
        // Vérifier que chaque étape a une suite logique
        foreach ($flux->etapes as $etape) {
            if (!self::aUneSuite($etape, $flux->transitions)) {
                throw new Exception("Étape {$etape['nom']} sans suite");
            }
        }
    }
    
    public static function vérifierCompletude($flux) {
        // Vérifier que tous les cas sont couverts
        foreach ($flux->conditions as $condition) {
            if (!self::aToutesLesBranches($condition)) {
                throw new Exception("Condition incomplète");
            }
        }
    }
}
```

#### b. Tests Unitaires
```php
class FluxFonctionnelTest extends TestCase {
    public function testCréationCompteCandidat() {
        $flux = new FluxFonctionnel();
        
        // Test du flux normal
        $this->assertTrue($flux->exécuter([
            'dossier_validé' => true,
            'email_valide' => true
        ]));
        
        // Test des cas d'erreur
        $this->assertFalse($flux->exécuter([
            'dossier_validé' => false
        ]));
    }
}
```

## Exercices Pratiques

### Exercice 1 : Modélisation Simple
Créez un flux pour la validation d'un document :

```php
// TODO: Implémenter le flux suivant
// 1. Candidat upload document
// 2. Système vérifie format
// 3. Conseiller valide contenu
// 4. Système met à jour statut
```

### Exercice 2 : Gestion des Erreurs
Ajoutez la gestion des cas d'erreur au flux précédent :

```php
// TODO: Ajouter
// - Que faire si format invalide ?
// - Que faire si contenu rejeté ?
// - Comment notifier l'utilisateur ?
```

### Exercice 3 : Flux Complexe
Modélisez le processus complet d'inscription :

```php
// TODO: Créer un flux qui inclut
// - Création compte
// - Validation email
// - Complétion profil
// - Soumission documents
// - Validation finale
```

## Outils de Développement

### 1. IDE et Extensions
- PHPStorm avec PlantUML plugin
- VS Code avec Draw.io Integration

### 2. Bibliothèques PHP
```bash
# Installation des packages utiles
composer require symfony/workflow    # Pour la gestion des workflows
composer require myclabs/php-enum    # Pour les énumérations
```

### 3. Outils de Documentation
```bash
# Génération de documentation
composer require phpdocumentor/phpdocumentor
```

## Bonnes Pratiques

### 1. Nomenclature
```php
// Nommage clair et descriptif
class ValidationDocumentWorkflow {
    const ETAPES = [
        'UPLOAD_INITIAL' => 'upload_initial',
        'VERIFICATION_FORMAT' => 'verification_format',
        'VALIDATION_CONTENU' => 'validation_contenu'
    ];
}
```

### 2. Documentation
```php
/**
 * Gère le flux de validation des documents
 * 
 * @param Document $document Document à valider
 * @param array $options Options de validation
 * @return bool Résultat de la validation
 */
public function validerDocument(Document $document, array $options = []): bool
```

### 3. Tests
```php
// Toujours tester les cas limites
public function testValidationDocumentAvecFormatInvalide() {
    $document = new Document('test.txt');
    $this->expectException(FormatInvalideException::class);
    $this->workflow->validerDocument($document);
}
```

## Conclusion

L'implémentation des flux fonctionnels nécessite :
- Une analyse approfondie
- Une modélisation claire
- Des tests rigoureux
- Une documentation complète

N'oubliez pas :
1. Commencer simple
2. Itérer et améliorer
3. Tester chaque modification
4. Documenter les changements
