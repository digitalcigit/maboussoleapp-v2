# Documentation des Migrations - Module Clients

## Vue d'Ensemble des Migrations

### Structure Actuelle
```yaml
Migrations Actives:
  - 2024_12_23_170452_create_consolidated_clients_table.php

Migrations Archivées (backup):
  - 2023_11_09_093409_create_clients_table.php
  - 2024_12_23_003500_update_client_enum_values.php
  - 2024_12_23_045001_add_deleted_at_to_clients_table.php
  - 2024_12_23_035000_add_contract_dates_to_clients_table.php
  - 2024_12_12_224200_add_client_id_to_activities_table.php
```

## Migration Principale

### Table Consolidée des Clients
```php
// 2024_12_23_170452_create_consolidated_clients_table.php

public function up()
{
    Schema::create('clients', function (Blueprint $table) {
        $table->id();
        $table->foreignId('prospect_id')->constrained();
        $table->string('client_number')->unique();
        $table->string('passport_number');
        $table->date('passport_expiry');
        $table->string('visa_status');
        $table->json('travel_preferences');
        $table->string('payment_status');
        $table->decimal('total_amount', 10, 2);
        $table->decimal('paid_amount', 10, 2);
        $table->string('status');
        $table->timestamps();
        $table->softDeletes();
    });
}
```

## Historique des Modifications

### 1. Mise à Jour des Énumérations
```php
// 2024_12_23_003500_update_client_enum_values.php
// Modification des valeurs d'énumération pour alignement
```

### 2. Ajout de la Suppression Douce
```php
// 2024_12_23_045001_add_deleted_at_to_clients_table.php
$table->softDeletes();
```

### 3. Ajout des Dates de Contrat
```php
// 2024_12_23_035000_add_contract_dates_to_clients_table.php
$table->date('contract_start')->nullable();
$table->date('contract_end')->nullable();
```

### 4. Relation avec les Activités
```php
// 2024_12_12_224200_add_client_id_to_activities_table.php
$table->foreignId('client_id')->nullable()->constrained();
```

## Points d'Apprentissage

### 1. Structure de la Table
```yaml
Champs Clés:
  - ID et Relations:
    - id (auto-increment)
    - prospect_id (foreign key)
  
  - Informations Client:
    - client_number (unique)
    - passport_number
    - passport_expiry
  
  - Statuts:
    - visa_status
    - payment_status
    - status
  
  - Finances:
    - total_amount
    - paid_amount
  
  - Métadonnées:
    - created_at
    - updated_at
    - deleted_at
```

### 2. Bonnes Pratiques
```yaml
Conventions:
  - Nommage explicite
  - Types appropriés
  - Contraintes nécessaires
  - Index performants

Relations:
  - Clés étrangères
  - Contraintes référentielles
  - Cascade appropriée
```

### 3. Évolution du Schéma
- Migrations incrémentales
- Rétrocompatibilité
- Gestion des données

## Guide de Maintenance

### 1. Ajout de Champs
```php
public function up()
{
    Schema::table('clients', function (Blueprint $table) {
        $table->string('nouveau_champ')->nullable();
    });
}
```

### 2. Modification de Champs
```php
public function up()
{
    Schema::table('clients', function (Blueprint $table) {
        $table->string('champ')->change();
    });
}
```

### 3. Suppression de Champs
```php
public function up()
{
    Schema::table('clients', function (Blueprint $table) {
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

### Exemple de Test de Migration
```php
/** @test */
public function test_client_table_has_required_columns()
{
    $columns = Schema::getColumnListing('clients');
    
    $this->assertTrue(in_array('client_number', $columns));
    $this->assertTrue(in_array('passport_number', $columns));
    $this->assertTrue(in_array('visa_status', $columns));
}
```
