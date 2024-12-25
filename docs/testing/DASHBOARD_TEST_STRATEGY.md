# Stratégie de Test du Dashboard

## Types de Tests

### 1. Tests Automatisés (Laravel Dusk)
Laravel Dusk est un outil d'automatisation qui :
- Pilote un vrai navigateur Chrome
- Exécute des scénarios prédéfinis
- Vérifie le rendu et les interactions
- Prend des captures d'écran
- S'intègre dans le CI/CD

```php
// Exemple de test Dusk
$browser->loginAs($superAdmin)
        ->visit('/admin')
        ->assertSee('Tableau de Bord');
```

### 2. Tests Manuels d'Acceptation
Ces tests sont effectués par des humains pour :
- Valider l'expérience utilisateur réelle
- Détecter des problèmes subtils
- Évaluer l'aspect esthétique
- Vérifier les cas d'utilisation réels

```yaml
Scénario Manuel:
  1. Se connecter en tant que super admin
  2. Observer l'apparence générale
  3. Interagir naturellement avec les widgets
  4. Noter les impressions subjectives
```

## Checklist de Test Manuel

### 1. Connexion et Premier Accès
```yaml
À Vérifier:
  - Temps de chargement ressenti
  - Fluidité des animations
  - Clarté des informations
  - Cohérence visuelle
  - Lisibilité des données
```

### 2. Interaction avec les KPIs
```yaml
Actions à Tester:
  - Survoler les indicateurs
  - Vérifier la lisibilité des chiffres
  - Observer les changements de couleur
  - Valider la pertinence des tendances
```

### 3. Graphique Financier
```yaml
Points d'Attention:
  - Clarté des légendes
  - Facilité d'interprétation
  - Réactivité au survol
  - Cohérence des couleurs
  - Lisibilité des axes
```

### 4. Liste des Transactions
```yaml
À Expérimenter:
  - Filtrage intuitif
  - Tri naturel
  - Navigation dans les pages
  - Recherche de transactions
  - Export des données
```

## Processus de Test Combiné

### 1. Phase Automatisée
```yaml
Étape CI/CD:
  1. Exécution des tests Dusk
  2. Vérification des captures d'écran
  3. Validation des métriques
  4. Tests de performance
```

### 2. Phase Manuelle
```yaml
Validation Humaine:
  1. Test utilisateur réel
  2. Validation esthétique
  3. Test d'accessibilité
  4. Vérification UX
```

### 3. Documentation des Résultats
```yaml
Pour Chaque Session:
  - Screenshots annotés
  - Feedback utilisateur
  - Points d'amélioration
  - Validation finale
```

## Formulaire de Test Manuel

```markdown
# Session de Test Manuel - Dashboard Super Admin

Date: [DATE]
Testeur: [NOM]
Version: [VERSION]

## Première Impression
- Design général: [1-5]
- Clarté des informations: [1-5]
- Temps de chargement: [1-5]
- Commentaires: [...]

## KPIs
- Lisibilité: [1-5]
- Pertinence: [1-5]
- Suggestions: [...]

## Graphique
- Facilité de lecture: [1-5]
- Interactivité: [1-5]
- Améliorations possibles: [...]

## Liste Transactions
- Utilisabilité: [1-5]
- Performance: [1-5]
- Points à améliorer: [...]

## Notes Générales
[Observations importantes]

## Validation
□ Accepté
□ Accepté avec réserves
□ Refusé

Signature: ____________
```

## Configuration CI/CD

### 1. GitHub Actions
```yaml
name: Dashboard Tests

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

jobs:
  dusk-tests:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: testing
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping"

    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
          
      - name: Install Chrome Driver
        run: php artisan dusk:chrome-driver
          
      - name: Start Chrome Driver
        run: ./vendor/laravel/dusk/bin/chromedriver-linux &
          
      - name: Run Laravel Server
        run: php artisan serve --no-reload &
          
      - name: Run Dusk Tests
        env:
          APP_URL: "http://127.0.0.1:8000"
        run: php artisan dusk
        
      - name: Store Screenshots
        if: failure()
        uses: actions/upload-artifact@v2
        with:
          name: screenshots
          path: tests/Browser/screenshots

  visual-regression:
    needs: dusk-tests
    runs-on: ubuntu-latest
    
    steps:
      - name: Run Percy
        run: npx percy exec -- php artisan dusk
        env:
          PERCY_TOKEN: ${{ secrets.PERCY_TOKEN }}
```

### 2. GitLab CI
```yaml
image: php:8.1

services:
  - mysql:8.0

variables:
  MYSQL_DATABASE: testing
  MYSQL_ROOT_PASSWORD: password

stages:
  - test
  - visual

dusk:
  stage: test
  script:
    - apt-get update && apt-get install -y wget unzip
    - wget -q -O chrome.zip https://chromedriver.storage.googleapis.com/LATEST_RELEASE/chromedriver_linux64.zip
    - unzip chrome.zip
    - mv chromedriver /usr/local/bin/
    - php artisan dusk:chrome-driver
    - php artisan serve --no-reload &
    - php artisan dusk
  artifacts:
    when: on_failure
    paths:
      - tests/Browser/screenshots
    expire_in: 1 week

percy:
  stage: visual
  script:
    - npm install -g @percy/cli
    - percy exec -- php artisan dusk
  only:
    - main
    - develop
```

## Intégration avec Percy.io

```php
// Dans DuskTestCase.php
use Percy\Client;

class DuskTestCase extends BaseTestCase
{
    protected function captureSnapshot($name)
    {
        if (env('PERCY_ENABLED')) {
            Percy::snapshot($name, [
                'widths' => [375, 768, 1280],
                'minHeight' => 1024,
            ]);
        }
    }
}
```

Cette approche combinée nous permet de :
1. Automatiser les tests répétitifs
2. Maintenir une qualité constante
3. Capturer les problèmes visuels
4. Valider l'expérience utilisateur réelle

La documentation des tests manuels est aussi importante que les tests automatisés car elle capture des aspects que l'automatisation ne peut pas détecter.
