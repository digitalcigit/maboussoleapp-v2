# Guide d'implémentation : Activités dans les Dossiers

## Structure du code

1. **Modèle Dossier**
```php
public function activities(): MorphMany
{
    return $this->morphMany(Activity::class, 'subject');
}
```

2. **DossierResource**
```php
public static function getRelations(): array
{
    return [
        RelationManagers\ActivitiesRelationManager::class,
    ];
}
```

3. **ActivitiesRelationManager**
- Gestion du formulaire de création/édition
- Configuration de la table d'affichage
- Définition des actions disponibles
- Filtres et tri

## Points d'attention

1. **Performance**
   - Utilisation de l'eager loading pour les relations
   - Pagination des résultats
   - Indexation des colonnes de tri

2. **Validation**
   - Validation des dates (scheduled_at, completed_at)
   - Vérification des types d'activités
   - Contrôle des permissions

3. **Interface utilisateur**
   - Badges colorés pour les types
   - Formatage des dates
   - Messages de confirmation

## Tests

1. **Tests unitaires**
```php
public function test_can_create_activity_for_dossier()
{
    $dossier = Dossier::factory()->create();
    $activity = $dossier->activities()->create([
        'type' => Activity::TYPE_NOTE,
        'description' => 'Test note',
        'created_by' => 1,
    ]);
    
    $this->assertDatabaseHas('activities', [
        'id' => $activity->id,
        'subject_type' => Dossier::class,
        'subject_id' => $dossier->id,
    ]);
}
```

2. **Tests d'intégration**
   - Vérification de l'affichage des activités
   - Test des filtres et du tri
   - Validation des permissions

## Maintenance

1. **Surveillance**
   - Logs des erreurs
   - Monitoring des performances
   - Analyse des usages

2. **Mises à jour**
   - Ajout de nouveaux types d'activités
   - Optimisation des requêtes
   - Amélioration de l'interface
