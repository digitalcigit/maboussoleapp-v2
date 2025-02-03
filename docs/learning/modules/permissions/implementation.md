# Gestion des permissions

## Workflow d'approbation
1. Vérification hiérarchique
2. Validation des contraintes métier
3. Audit automatique des changements

## Tests unitaires
```php
// Exemple de test de permission
public function test_manager_can_edit_assigned_prospects()
{
    $manager = User::factory()->withRole('manager')->create();
    $prospect = Prospect::factory()->create(['assigned_to' => $manager->id]);
    
    $this->actingAs($manager)
         ->get(route('filament.resources.prospects.edit', $prospect))
         ->assertSuccessful();
}
```
