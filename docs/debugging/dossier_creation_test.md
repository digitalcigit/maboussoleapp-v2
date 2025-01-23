# Guide de débogage - Test de création de dossier

Ce guide explique le processus de test pour la création d'un dossier, qui implique plusieurs étapes critiques et dépendances.

## Prérequis du test

1. **Permissions et rôles**
   - Le rôle `admin` doit être créé
   - Le rôle `portail_candidat` doit être créé
   - Les permissions suivantes doivent exister :
     - `dossiers.create`
     - `dossiers.view`
     - `dossiers.update`
     - `dossiers.delete`
     - `dossiers.list`

2. **Utilisateur administrateur**
   - Un utilisateur admin doit être créé
   - L'utilisateur doit avoir le rôle `admin`
   - Les permissions doivent être attribuées au rôle `admin`

## Structure des données de test

```php
$dossierData = [
    'reference_number' => 'DOS-' . $referenceGenerator->generateReference('dossier'),
    'prospect_info' => [
        'first_name' => 'Test',
        'last_name' => 'Candidat',
        'email' => 'test.candidat@example.com',
        'phone' => '0123456789',
        'profession' => 'Développeur',
        'education_level' => 'BAC+5',
        'desired_field' => 'IT',
        'desired_destination' => 'Canada',
    ],
    'current_step' => Dossier::STEP_ANALYSIS,
    'current_status' => Dossier::STATUS_WAITING_DOCS,
    'assigned_to' => $admin->id
]
```

## Points de vérification

Le test vérifie les éléments suivants :

1. **Création du dossier**
   - Le dossier est créé avec le bon numéro de référence
   - L'étape et le statut sont correctement initialisés
   - Le dossier est assigné à l'administrateur

2. **Création du prospect**
   - Les informations du prospect sont correctement enregistrées
   - Le prospect est lié au dossier

3. **Création du compte utilisateur**
   - Un compte utilisateur est créé pour le prospect
   - Le compte a le rôle `portail_candidat`
   - Le mot de passe est généré automatiquement

4. **Envoi de l'email**
   - L'email est envoyé à l'adresse du prospect
   - L'email contient les identifiants de connexion

## Problèmes courants

1. **Erreur de permission manquante**
   ```
   There is no permission named 'dossiers.create' for guard 'web'
   ```
   Solution : Assurez-vous que toutes les permissions sont créées avant d'exécuter le test.

2. **Erreur de type pour l'étape**
   ```
   Argument #1 ($step) must be of type int, string given
   ```
   Solution : Utilisez les constantes du modèle `Dossier` pour les étapes (`STEP_ANALYSIS`, etc.).

3. **Erreur de validation du numéro de référence**
   ```
   Component has errors: "data.reference_number"
   ```
   Solution : Utilisez le `ReferenceGeneratorService` pour générer un numéro de référence valide.

## Conseils de débogage

1. Utilisez `Mail::fake()` pour intercepter les emails pendant les tests
2. Vérifiez que les rôles et permissions sont créés dans le bon ordre
3. Utilisez les constantes du modèle pour les étapes et statuts
4. Assurez-vous que le service de génération de référence est correctement injecté

## Code complet du test

Voir le fichier `tests/Feature/DossierCreationTest.php` pour le code complet du test.
