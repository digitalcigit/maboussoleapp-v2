# Documentation API - MaBoussole CRM v2

> Dernière mise à jour : 2024-12-21

## Vue d'ensemble
Documentation des endpoints API disponibles dans MaBoussole CRM v2.

## Authentication

### Login
```http
POST /api/auth/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password"
}

Response (200 OK)
{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "type": "Bearer"
}
```

### Logout
```http
POST /api/auth/logout
Authorization: Bearer {token}

Response (204 No Content)
```

## Prospects

### Liste des Prospects
```http
GET /api/prospects
Authorization: Bearer {token}

Response (200 OK)
{
    "data": [
        {
            "id": 1,
            "reference_number": "PROS001",
            "first_name": "John",
            "last_name": "Doe",
            "status": "new"
        }
    ],
    "meta": {
        "current_page": 1,
        "total": 10
    }
}
```

### Création Prospect
```http
POST /api/prospects
Authorization: Bearer {token}
Content-Type: application/json

{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "phone": "+33612345678"
}

Response (201 Created)
{
    "data": {
        "id": 1,
        "reference_number": "PROS001",
        "first_name": "John",
        "last_name": "Doe"
    }
}
```

## Clients

### Conversion Prospect en Client
```http
POST /api/prospects/{id}/convert
Authorization: Bearer {token}

Response (200 OK)
{
    "data": {
        "id": 1,
        "client_number": "CLI001",
        "status": "active"
    }
}
```

### Mise à Jour Client
```http
PATCH /api/clients/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "status": "active",
    "payment_status": "completed"
}

Response (200 OK)
{
    "data": {
        "id": 1,
        "status": "active",
        "payment_status": "completed"
    }
}
```

## Activités

### Création Activité
```http
POST /api/activities
Authorization: Bearer {token}
Content-Type: application/json

{
    "subject_type": "prospect",
    "subject_id": 1,
    "type": "call",
    "notes": "Premier contact établi"
}

Response (201 Created)
{
    "data": {
        "id": 1,
        "type": "call",
        "status": "completed"
    }
}
```

## Gestion des Erreurs

### Format Standard
```json
{
    "error": {
        "code": "ERROR_CODE",
        "message": "Description de l'erreur",
        "details": {}
    }
}
```

### Codes d'Erreur Communs
```
400 Bad Request
401 Unauthorized
403 Forbidden
404 Not Found
422 Unprocessable Entity
500 Internal Server Error
```

## Pagination

### Format Standard
```json
{
    "data": [],
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 50,
        "total_pages": 4
    },
    "links": {
        "first": "...",
        "last": "...",
        "prev": null,
        "next": "..."
    }
}
```

## Filtres et Tri

### Paramètres URL
```
/api/prospects?sort=-created_at
/api/prospects?filter[status]=new
/api/prospects?include=activities,advisor
```

## Sécurité

### CORS
```php
// config/cors.php
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],
    'allowed_headers' => ['*'],
    'exposed_headers' => false,
    'max_age' => 0,
    'supports_credentials' => false,
];
```

### Rate Limiting
```php
// Routes/api.php
Route::middleware(['throttle:api'])->group(function () {
    // Routes API
});
```

## Tests API

### PHPUnit
```php
class ApiTest extends TestCase
{
    /** @test */
    public function it_can_list_prospects()
    {
        $response = $this->getJson('/api/prospects');
        $response->assertStatus(200)
                ->assertJsonStructure(['data']);
    }
}
```

---
*Documentation générée pour MaBoussole CRM v2*
