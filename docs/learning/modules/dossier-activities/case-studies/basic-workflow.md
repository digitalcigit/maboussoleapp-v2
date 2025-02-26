# Cas d'étude : Workflow de base des activités

## Contexte

Un gestionnaire de dossier doit suivre toutes les interactions avec un prospect pendant le processus d'admission.

## Scénario

1. **Première interaction**
   - Création d'une note après le premier contact
   - Planification d'un appel de suivi

2. **Suivi téléphonique**
   - Enregistrement de l'appel comme activité
   - Documentation des points discutés
   - Planification d'une réunion

3. **Réunion de présentation**
   - Création d'une activité de type réunion
   - Ajout des documents présentés
   - Suivi des actions décidées

## Implémentation

```php
// Création d'une note
$dossier->activities()->create([
    'type' => Activity::TYPE_NOTE,
    'description' => 'Premier contact établi par email',
    'created_by' => auth()->id(),
]);

// Planification d'un appel
$dossier->activities()->create([
    'type' => Activity::TYPE_CALL,
    'description' => 'Appel de suivi pour discuter des documents requis',
    'scheduled_at' => now()->addDays(2),
    'created_by' => auth()->id(),
]);

// Enregistrement de la réunion
$dossier->activities()->create([
    'type' => Activity::TYPE_MEETING,
    'description' => 'Présentation du programme et des conditions d\'admission',
    'scheduled_at' => $meetingDate,
    'completed_at' => $meetingDate->addHours(1),
    'created_by' => auth()->id(),
]);
```

## Tests

```php
public function test_activity_workflow()
{
    $dossier = Dossier::factory()->create();
    
    // Test création note
    $note = $dossier->activities()->create([
        'type' => Activity::TYPE_NOTE,
        'description' => 'Test note',
        'created_by' => 1,
    ]);
    $this->assertNotNull($note);
    
    // Test planification appel
    $call = $dossier->activities()->create([
        'type' => Activity::TYPE_CALL,
        'description' => 'Test call',
        'scheduled_at' => now()->addDay(),
        'created_by' => 1,
    ]);
    $this->assertNotNull($call->scheduled_at);
}
```

## Leçons apprises

1. **Organisation**
   - Importance de la chronologie des activités
   - Nécessité de planifier les suivis
   - Valeur de la documentation détaillée

2. **Bonnes pratiques**
   - Créer les activités immédiatement après l'interaction
   - Utiliser les types appropriés
   - Maintenir des descriptions claires

3. **Points d'attention**
   - Validation des dates
   - Gestion des activités planifiées
   - Suivi des activités non réalisées

## Applications possibles

1. **Automatisation**
   - Rappels automatiques pour les activités planifiées
   - Génération de rapports d'activité
   - Intégration avec le calendrier

2. **Analyse**
   - Suivi des temps de réponse
   - Analyse des types d'interactions
   - Mesure de l'engagement
