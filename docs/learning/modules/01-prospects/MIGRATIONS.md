# Documentation des Migrations - Module Prospects

## Vue d'Ensemble des Migrations

### Structure Actuelle
```yaml
Migrations Actives:
  - 2024_12_23_170451_create_consolidated_prospects_table.php

Migrations Archivées (backup):
  - 2023_11_09_093408_create_prospects_table.php
  - 2024_12_23_034400_add_source_to_prospects_table.php
  - 2023_11_01_000002_create_prospects_table.php
  - 2024_12_19_094727_add_notes_to_prospects_table.php
  - 2024_12_19_114029_update_status_column_in_prospects_table.php
```

## Migration Principale

### Table Consolidée des Prospects
```php
// 2024_12_23_170451_create_consolidated_prospects_table.php

public function up()
{
    Schema::create('prospects', function (Blueprint $table) {
        $table->id();
        $table->string('reference_number')->unique();
        $table->string('first_name');
        $table->string('last_name');
        $table->string('email')->unique();
        $table->string('phone');
        $table->date('birth_date');
        $table->string('profession');
        $table->string('education_level');
        $table->string('current_location');
        $table->string('current_field');
        $table->string('desired_field');
        $table->string('desired_destination');
        $table->json('emergency_contact');
        $table->string('status');
        $table->foreignId('assigned_to')->nullable();
        $table->string('commercial_code')->nullable();
        $table->foreignId('partner_id')->nullable();
        $table->timestamp('last_action_at')->nullable();
        $table->timestamp('analysis_deadline')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });
}
```

## Historique des Modifications

### 1. Ajout de la Source
```php
// 2024_12_23_034400_add_source_to_prospects_table.php
$table->string('source')->nullable();
```

### 2. Ajout des Notes
```php
// 2024_12_19_094727_add_notes_to_prospects_table.php
$table->text('notes')->nullable();
```

### 3. Mise à Jour des Statuts
```php
// 2024_12_19_114029_update_status_column_in_prospects_table.php
// Modification des valeurs de statut pour alignement
```

## Points d'Apprentissage

### 1. Structure de la Table
- Champs obligatoires vs optionnels
- Types de données appropriés
- Contraintes et index

### 2. Bonnes Pratiques
```yaml
Conventions:
  - Nommage clair des champs
  - Utilisation de types appropriés
  - Index sur les champs de recherche
  - Clés étrangères pour relations

Sécurité:
  - Validation des données
  - Contraintes d'intégrité
  - Protection contre les doublons
```

### 3. Évolution du Schéma
- Migrations incrémentales
- Gestion des modifications
- Rétrocompatibilité

## Guide de Maintenance

### 1. Ajout de Nouveaux Champs
```php
public function up()
{
    Schema::table('prospects', function (Blueprint $table) {
        $table->string('nouveau_champ')->nullable();
    });
}
```

### 2. Modification de Champs
```php
public function up()
{
    Schema::table('prospects', function (Blueprint $table) {
        $table->string('champ')->change();
    });
}
```

### 3. Suppression de Champs
```php
public function up()
{
    Schema::table('prospects', function (Blueprint $table) {
        $table->dropColumn('champ');
    });
}
```

## Tests et Validation

### Points à Vérifier
1. **Intégrité des Données**
   - Types corrects
   - Contraintes respectées
   - Relations valides

2. **Performance**
   - Index appropriés
   - Requêtes optimisées
   - Taille des données

3. **Maintenance**
   - Documentation à jour
   - Migrations réversibles
   - Données de test
