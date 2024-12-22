# Guide de Contribution - MaBoussole CRM v2

> Dernière mise à jour : 2024-12-21

## Vue d'ensemble
Guide complet pour contribuer au projet MaBoussole CRM v2.

## Workflow Git

### Branches
```bash
main        # Production
develop     # Développement
feature/*   # Nouvelles fonctionnalités
bugfix/*    # Corrections de bugs
hotfix/*    # Corrections urgentes
release/*   # Préparation release
```

### Convention de Nommage
```bash
# Features
feature/add-prospect-validation
feature/improve-document-upload

# Bugfix
bugfix/fix-notification-delay
bugfix/correct-payment-calculation

# Hotfix
hotfix/security-vulnerability
hotfix/critical-performance-issue
```

### Commits
```bash
# Format
<type>(<scope>): <description>

# Types
feat:     Nouvelle fonctionnalité
fix:      Correction de bug
docs:     Documentation
style:    Formatage
refactor: Refactoring
test:     Tests
chore:    Maintenance

# Exemples
feat(prospects): ajouter validation automatique
fix(auth): corriger expiration token
docs(api): mettre à jour documentation
```

## Process de Contribution

### 1. Création Issue
```markdown
## Description
[Description claire du problème ou de la fonctionnalité]

## Comportement Attendu
- Point 1
- Point 2

## Comportement Actuel (si bug)
- Point 1
- Point 2

## Étapes de Reproduction (si bug)
1. Étape 1
2. Étape 2

## Captures d'écran
[Si applicable]

## Informations Techniques
- Version: x.y.z
- Environnement: production/staging
```

### 2. Création Branche
```bash
# Depuis develop
git checkout develop
git pull origin develop
git checkout -b feature/ma-feature

# Développement
git add .
git commit -m "feat(scope): description"
git push origin feature/ma-feature
```

### 3. Pull Request
```markdown
## Description
[Description des changements]

## Type de Changement
- [ ] Nouvelle fonctionnalité
- [ ] Correction de bug
- [ ] Documentation
- [ ] Autre

## Tests Effectués
- [ ] Tests unitaires
- [ ] Tests d'intégration
- [ ] Tests manuels

## Captures d'écran
[Si applicable]

## Issue Liée
Fixes #123
```

## Standards de Code

### PHP
```php
// PSR-12
declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Exceptions\ValidationException;

class UserService
{
    public function __construct(
        private readonly UserRepository $repository
    ) {
    }

    public function createUser(array $data): User
    {
        $this->validateData($data);

        return $this->repository->create($data);
    }

    private function validateData(array $data): void
    {
        if (empty($data['email'])) {
            throw new ValidationException('Email is required');
        }
    }
}
```

### JavaScript
```javascript
// ESLint + Prettier
import { ref, onMounted } from 'vue'
import { useProspectStore } from '@/stores/prospect'

export default {
  name: 'ProspectList',
  
  setup() {
    const prospects = ref([])
    const store = useProspectStore()

    const fetchProspects = async () => {
      try {
        prospects.value = await store.fetchAll()
      } catch (error) {
        console.error('Failed to fetch prospects:', error)
      }
    }

    onMounted(fetchProspects)

    return {
      prospects
    }
  }
}
```

## Tests

### Tests Requis
```php
class ProspectTest extends TestCase
{
    /** @test */
    public function it_requires_valid_email()
    {
        $this->expectException(ValidationException::class);

        $prospect = Prospect::factory()->make([
            'email' => 'invalid-email'
        ]);

        $prospect->save();
    }

    /** @test */
    public function it_can_be_assigned_to_advisor()
    {
        $advisor = User::factory()->create();
        $prospect = Prospect::factory()->create();

        $prospect->assignTo($advisor);

        $this->assertEquals($advisor->id, $prospect->advisor_id);
    }
}
```

### Coverage Minimal
```yaml
Global: 80%
Models: 90%
Services: 85%
Controllers: 75%
```

## Documentation

### PHPDoc
```php
/**
 * Convert a prospect to a client.
 *
 * @param Prospect $prospect The prospect to convert
 * @param array $options Additional conversion options
 * @throws ConversionException If prospect is not eligible
 * @return Client The newly created client
 */
public function convertToClient(Prospect $prospect, array $options = []): Client
{
    // Implementation
}
```

### Markdown
```markdown
## Fonction/Feature

### Description
Description claire et concise

### Utilisation
```php
$example = new Example();
$result = $example->method();
```

### Paramètres
- `param1`: Description
- `param2`: Description

### Retour
Description de la valeur de retour
```

## Review Process

### Checklist
```markdown
## Code
- [ ] Suit les standards de code
- [ ] Tests ajoutés/mis à jour
- [ ] Documentation mise à jour
- [ ] Pas de code mort
- [ ] Optimisé pour la performance

## Sécurité
- [ ] Validation des entrées
- [ ] Protection XSS
- [ ] Gestion des permissions

## UX
- [ ] Interface intuitive
- [ ] Messages d'erreur clairs
- [ ] Responsive design
```

### Feedback
```markdown
## Points Positifs
- Point 1
- Point 2

## À Améliorer
- Suggestion 1
- Suggestion 2

## Questions
- Question 1
- Question 2
```

## CI/CD

### GitHub Actions
```yaml
name: CI

on:
  push:
    branches: [ develop, main ]
  pull_request:
    branches: [ develop ]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          
      - name: Install Dependencies
        run: composer install
        
      - name: Run Tests
        run: php artisan test
```

## Maintenance

### Commandes Utiles
```bash
# Vérification style
./vendor/bin/pint

# Tests
php artisan test --coverage

# Documentation
php artisan ide-helper:generate
php artisan ide-helper:models
```

---
*Documentation générée pour MaBoussole CRM v2*
