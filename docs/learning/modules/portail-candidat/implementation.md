# Implémentation du Portail Candidat

## Configuration du Panel

Le portail candidat est implémenté comme un panel Filament distinct via `CandidatePanelProvider`. Cette approche permet :
- Une séparation claire entre l'interface admin et l'interface candidat
- Des middlewares et des permissions spécifiques
- Une personnalisation complète de l'expérience utilisateur

### Structure des fichiers
```
app/
  ├── Providers/
  │   └── Filament/
  │       └── CandidatePanelProvider.php
  └── Filament/
      └── Candidat/
          └── Pages/
              └── Auth/
                  └── Login.php
```

### Points d'accès
- URL du portail : `/candidat`
- Page de connexion : `/candidat/login`

### Sécurité
- Middleware d'authentification configuré
- Protection CSRF activée
- Session sécurisée

## Personnalisation

### Interface de connexion
- Template dédié : `resources/views/filament/candidat/pages/auth/login.blade.php`
- Titre personnalisé : "Portail Candidat"
- Style cohérent avec la charte graphique MaBoussole

## Tests et validation

### Tests unitaires recommandés
```php
public function test_candidat_can_access_portal()
{
    $response = $this->get('/candidat');
    $response->assertStatus(200);
}

public function test_candidat_can_login()
{
    $response = $this->post('/candidat/login', [
        'email' => 'candidat@example.com',
        'password' => 'password',
    ]);
    $response->assertRedirect('/candidat');
}
```
