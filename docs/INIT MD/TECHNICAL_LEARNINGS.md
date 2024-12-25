# Apprentissages Techniques du Projet MaBoussole

Ce document recense les apprentissages techniques et les bonnes pratiques découvertes lors du développement de MaBoussole. Il servira de référence pour les futurs projets.

## Framework et Architecture

### Filament Admin Panel

#### Structure des Routes
- Les routes Filament suivent une structure spécifique : `/admin/{resource}/{action}`
- Les actions standards sont : 
  - `create` : GET pour afficher le formulaire, POST pour créer
  - `edit/{id}` : GET pour afficher, PATCH pour mettre à jour
  - `{id}` : DELETE pour supprimer

#### Gestion des Formulaires
- Les formulaires sont définis dans la méthode `form()` des Resources
- Configuration via une API fluide : `Forms\Components\Section::make()`
- Validation intégrée avec les règles Laravel
- Support multilingue natif pour les labels

#### Tests
- Les tests doivent inclure le token CSRF : `'_token' => csrf_token()`
- Les redirections après actions pointent vers `/admin/{resource}`
- Les validations utilisent `assertSessionHasErrors()`
- Structure des tests :
  1. Préparation (factory, make/create)
  2. Action (GET/POST/PATCH/DELETE)
  3. Assertions (redirection, base de données)

### Bonnes Pratiques

#### Tests
```php
// Exemple de test de création
public function test_it_can_create_resource()
{
    $data = Model::factory()->make();
    
    // 1. Vérifier l'affichage du formulaire
    $response = $this->get('/admin/resources/create');
    $response->assertSuccessful();
    
    // 2. Tester la création
    $response = $this->post('/admin/resources', [
        'field' => $data->field,
        '_token' => csrf_token(),
    ]);
    
    // 3. Vérifier la redirection et les données
    $response->assertRedirect('/admin/resources');
    $this->assertDatabaseHas('table', ['field' => $data->field]);
}
```

## Sécurité

### CSRF Protection
- Tous les formulaires nécessitent un token CSRF
- Le token est automatiquement géré par Filament dans l'interface
- Dans les tests, ajouter manuellement : `'_token' => csrf_token()`

### Validation
- Validation au niveau du modèle et du formulaire
- Règles communes : required, unique, email, max:255
- Messages d'erreur personnalisables par champ

## Interface Utilisateur

### Composants Filament
- Sections pour grouper les champs
- Colonnes configurables (columns(2))
- Champs avec recherche et préchargement
- Support des relations (BelongsTo, HasMany)

## Tests Filament avec Livewire

#### Tests des Ressources
- Les tests de ressources Filament utilisent le composant Livewire sous-jacent
- Structure recommandée pour les tests de ressources :
  ```php
  Livewire::test(ResourceClass::class)
      ->assertSuccessful()        // Vérifie le chargement
      ->fillForm($data)           // Remplit le formulaire
      ->call('save')              // Appelle une action
      ->assertHasNoFormErrors();  // Vérifie la validation
  ```

#### Validation des Énumérations
- Utiliser les constantes de modèle pour les énumérations dans les formulaires
- Exemple de configuration sécurisée :
  ```php
  Forms\Components\Select::make('type')
      ->required()
      ->options([
          Model::TYPE_ONE => 'One',
          Model::TYPE_TWO => 'Two',
      ])
      ->in([
          Model::TYPE_ONE,
          Model::TYPE_TWO,
      ])
  ```

#### Gestion des Permissions
- Bonnes pratiques pour les tests avec permissions :
  1. Utiliser un trait `HasTestPermissions` pour centraliser la logique
  2. Créer toutes les permissions au démarrage des tests
  3. Assigner les rôles appropriés pour chaque test
  ```php
  class TestCase extends Base
  {
      use HasTestPermissions;
      
      protected function setUp(): void
      {
          parent::setUp();
          $this->setUpTestPermissions();
      }
  }
  ```

#### Initialisation des Formulaires
- Pour les formulaires d'édition, initialiser correctement dans la méthode `mount` :
  ```php
  public function mount($record): void
  {
      parent::mount($record);
      $this->form->fill($this->record->attributesToArray());
  }
  ```

### Leçons Apprises

#### Tests de Ressources Filament
1. **Permissions et Rôles**
   - Toujours vérifier les permissions nécessaires pour chaque action
   - Utiliser les rôles prédéfinis (ex: 'manager') pour les tests
   - Penser à la hiérarchie des permissions (view, create, edit, delete)

2. **Validation des Formulaires**
   - Tester explicitement les cas d'erreur de validation
   - Utiliser les constantes de modèle pour les énumérations
   - Vérifier la validation côté serveur ET côté client

3. **État du Formulaire**
   - S'assurer que le formulaire est correctement initialisé
   - Vérifier les valeurs par défaut
   - Tester la persistance des données après soumission

4. **Bonnes Pratiques**
   - Organiser les tests par fonctionnalité
   - Utiliser des factories pour les données de test
   - Isoler la configuration des permissions
   - Documenter les cas de test complexes

## Scripts de Sécurité et d'Automatisation

### validate-command.php

#### Contexte et Motivation
Créé le 20 décembre 2024 suite à une erreur de manipulation des commandes de migration. Le script est né du besoin de :
- Éviter les erreurs d'environnement (prod vs test)
- Standardiser l'utilisation des commandes sensibles
- Protéger contre les manipulations accidentelles

#### Fonctionnalités
1. **Validation des Commandes Critiques**
   ```php
   private array $criticalCommands = [
       'migrate' => [
           'required_flags' => ['--env'],
           'test_values' => ['testing'],
           'error_message' => 'La commande migrate doit inclure --env=testing pendant les tests'
       ],
       // ...
   ];
   ```

2. **Vérification des Flags**
   - Vérifie la présence des flags requis
   - Valide les valeurs autorisées
   - Bloque les commandes non conformes

3. **Messages d'Erreur Explicites**
   ```bash
   ⚠️  ERREUR : La commande migrate doit inclure --env=testing pendant les tests
   ✅ Suggestion : php artisan migrate --env=testing
   ```

#### Utilisation
```bash
# Validation d'une commande
php scripts/validate-command.php migrate --env=testing

# Résultats possibles
✅ Commande valide
# ou
⚠️  ERREUR : [Message d'erreur]
✅ Suggestion : [Commande suggérée]
```

#### Commandes Surveillées
1. **migrate**
   - Flags requis : --env
   - Valeurs autorisées : testing
   - Usage : Migrations de base de données

2. **db:seed**
   - Flags requis : --env
   - Valeurs autorisées : testing
   - Usage : Peuplement de la base de données

3. **config:cache**
   - Flags requis : --env
   - Valeurs autorisées : testing, local
   - Usage : Mise en cache de la configuration

#### Extension du Script
Pour ajouter une nouvelle commande à surveiller :
```php
'nouvelle_commande' => [
    'required_flags' => ['--flag1', '--flag2'],
    'test_values' => ['valeur1', 'valeur2'],
    'error_message' => 'Message d'erreur personnalisé'
]
```

#### Bonnes Pratiques
1. **Toujours utiliser le script pour les commandes critiques**
   ```bash
   php scripts/validate-command.php [commande] [options]
   ```

2. **Valider les nouvelles commandes critiques**
   - Discuter en équipe avant d'ajouter
   - Documenter dans PROJECT_MEMORY.md
   - Mettre à jour le README du script

3. **Maintenance**
   - Ajouter de nouvelles commandes selon les besoins
   - Mettre à jour les messages d'erreur
   - Garder la documentation à jour

#### Pourquoi ce Script ?
1. **Prévention des Erreurs**
   - Évite les manipulations accidentelles
   - Standardise les commandes
   - Guide les développeurs

2. **Documentation Active**
   - Force la réflexion sur l'environnement
   - Explicite les bonnes pratiques
   - Facilite l'onboarding

3. **Amélioration Continue**
   - Permet d'ajouter facilement des règles
   - S'adapte aux besoins du projet
   - Maintient la qualité du code

## À Faire / Améliorations Possibles
- [ ] Implémenter des tests pour les actions en masse
- [ ] Ajouter des tests pour les permissions
- [ ] Documenter la structure des factories
- [ ] Explorer les possibilités de personnalisation de l'interface

## Ressources Utiles
- [Documentation Filament](https://filamentphp.com/)
- [Documentation Laravel](https://laravel.com/docs)
- [Bonnes pratiques de test Laravel](https://laravel.com/docs/testing)
