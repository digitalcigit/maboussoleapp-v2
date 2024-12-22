# Standards de Code - MaBoussole CRM v2

> Dernière mise à jour : 2024-12-21

## Vue d'ensemble
Documentation des standards de code pour MaBoussole CRM v2.

## PHP

### PSR-12
```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Exceptions\ValidationException;

class UserService
{
    public function __construct(
        private readonly UserRepository $repository,
        private readonly ValidationService $validator
    ) {
    }

    public function createUser(array $data): User
    {
        $this->validator->validate($data, [
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            'role' => 'required|in:admin,user',
        ]);

        return $this->repository->create($data);
    }
}
```

### Nommage

#### Classes
```php
// ✅ Bon
class ProspectService
class ClientRepository
class DocumentUploadJob

// ❌ Mauvais
class prospectService
class Client_Repository
class documentUploadJob
```

#### Méthodes
```php
// ✅ Bon
public function createProspect()
public function updateClientStatus()
public function handleDocumentUpload()

// ❌ Mauvais
public function CreateProspect()
public function update_client_status()
public function handleDocumentupload()
```

#### Variables
```php
// ✅ Bon
$userId
$clientEmail
$documentPath

// ❌ Mauvais
$UserID
$client_email
$documentpath
```

### Type Hinting
```php
// ✅ Bon
public function updateClient(Client $client, array $data): Client
{
    return $client->update($data);
}

// ❌ Mauvais
public function updateClient($client, $data)
{
    return $client->update($data);
}
```

## JavaScript

### ESLint Configuration
```javascript
// .eslintrc.js
module.exports = {
    root: true,
    env: {
        node: true,
        browser: true,
        es2021: true
    },
    extends: [
        'plugin:vue/vue3-recommended',
        'eslint:recommended'
    ],
    rules: {
        'vue/multi-word-component-names': 'error',
        'vue/no-unused-components': 'error',
        'no-console': process.env.NODE_ENV === 'production' ? 'error' : 'warn',
        'no-debugger': process.env.NODE_ENV === 'production' ? 'error' : 'warn'
    }
}
```

### Vue Components
```javascript
// ✅ Bon
<script setup>
import { ref, onMounted } from 'vue'
import { useProspectStore } from '@/stores/prospect'

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
</script>

<template>
  <div class="prospects-list">
    <ProspectCard
      v-for="prospect in prospects"
      :key="prospect.id"
      :prospect="prospect"
    />
  </div>
</template>

// ❌ Mauvais
<script>
export default {
  data() {
    return {
      prospects: []
    }
  },
  mounted() {
    this.getProspects()
  },
  methods: {
    getProspects() {
      this.$store.dispatch('fetchProspects')
        .then(data => this.prospects = data)
    }
  }
}
</script>
```

### Import/Export
```javascript
// ✅ Bon
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { ProspectService } from '@/services'

export { default as ProspectList } from './ProspectList.vue'

// ❌ Mauvais
import * as Vue from 'vue'
var router = require('vue-router')
export ProspectList from './ProspectList.vue'
```

## CSS/SCSS

### BEM Methodology
```scss
// ✅ Bon
.prospect-card {
    &__header {
        // ...
    }

    &__content {
        // ...
    }

    &--highlighted {
        // ...
    }
}

// ❌ Mauvais
.prospectCard {
    .header {
        // ...
    }

    .content {
        // ...
    }
}
```

### Variables
```scss
// ✅ Bon
$color-primary: #4a90e2;
$spacing-unit: 8px;
$font-size-base: 16px;

.element {
    color: $color-primary;
    padding: $spacing-unit * 2;
    font-size: $font-size-base;
}

// ❌ Mauvais
.element {
    color: #4a90e2;
    padding: 16px;
    font-size: 16px;
}
```

## Tests

### PHPUnit
```php
// ✅ Bon
class ProspectTest extends TestCase
{
    /** @test */
    public function it_validates_email_format()
    {
        $this->expectException(ValidationException::class);

        $prospect = Prospect::factory()->make([
            'email' => 'invalid-email'
        ]);

        $prospect->save();
    }
}

// ❌ Mauvais
class ProspectTest extends TestCase
{
    public function testEmail()
    {
        try {
            $prospect = new Prospect();
            $prospect->email = 'bad-email';
            $prospect->save();
        } catch (Exception $e) {
            $this->assertTrue(true);
        }
    }
}
```

### Jest
```javascript
// ✅ Bon
describe('ProspectService', () => {
    it('should fetch prospects successfully', async () => {
        const prospects = await ProspectService.fetchAll()
        expect(prospects).toHaveLength(2)
        expect(prospects[0]).toHaveProperty('id')
    })
})

// ❌ Mauvais
test('prospects', () => {
    ProspectService.fetchAll().then(data => {
        expect(data.length).toBe(2)
    })
})
```

## Documentation

### PHPDoc
```php
// ✅ Bon
/**
 * Convert a prospect to a client.
 *
 * @param Prospect $prospect The prospect to convert
 * @param array $options Additional conversion options
 * @throws ConversionException If prospect is not eligible
 * @return Client The newly created client
 */
public function convertToClient(Prospect $prospect, array $options = []): Client

// ❌ Mauvais
// Convert prospect to client
public function convert($prospect, $options = null)
```

### JSDoc
```javascript
// ✅ Bon
/**
 * Fetch prospects based on filters.
 * @param {Object} filters - The filter criteria
 * @param {string} filters.status - Prospect status
 * @returns {Promise<Array<Prospect>>} List of prospects
 * @throws {ApiError} When API request fails
 */
async function fetchProspects(filters) {
    // Implementation
}

// ❌ Mauvais
// Get prospects
async function getProspects(filters) {
    // Implementation
}
```

## Git

### Messages de Commit
```bash
# ✅ Bon
feat(prospects): add email validation
fix(auth): correct token expiration
docs(api): update endpoint documentation

# ❌ Mauvais
update code
fix bug
add new feature
```

### Branches
```bash
# ✅ Bon
feature/add-prospect-validation
bugfix/fix-notification-delay
hotfix/security-vulnerability

# ❌ Mauvais
new-feature
bug-fix
john-changes
```

## Outils de Vérification

### PHP CS Fixer
```bash
# Installation
composer require --dev friendsofphp/php-cs-fixer

# Configuration
.php-cs-fixer.dist.php

# Exécution
./vendor/bin/php-cs-fixer fix
```

### ESLint
```bash
# Installation
npm install --save-dev eslint

# Configuration
.eslintrc.js

# Exécution
npx eslint --fix src/
```

### Prettier
```bash
# Installation
npm install --save-dev prettier

# Configuration
.prettierrc.json

# Exécution
npx prettier --write src/
```

---
*Documentation générée pour MaBoussole CRM v2*
