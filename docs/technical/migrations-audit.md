# Audit des Migrations - 23 décembre 2023

## Table `activities`

### Migrations Existantes
1. `2023_11_01_000004_create_activities_table.php`
2. `2023_11_09_093406_create_activities_table.php`
3. `2024_12_12_220800_add_title_and_status_to_activities_table.php`
4. `2024_12_12_221800_add_missing_columns_to_activities_table.php`
5. `2024_12_12_224200_add_client_id_to_activities_table.php`
6. `2024_12_12_224500_add_subject_fields_to_activities_table.php`
7. `2024_12_12_224600_fix_activities_table_columns.php`
8. `2024_12_12_224700_remove_duplicate_activities_columns.php`
9. `2024_12_12_224800_update_activities_table_structure.php`
10. `2024_12_12_224900_ensure_activities_table_structure.php`
11. `2024_12_12_225000_finalize_activities_table.php`
12. `2024_12_20_080600_update_activity_status_values.php`
13. `2024_12_22_203900_fix_activities_status_column.php`

### Problèmes Identifiés
- Double création de la table
- Multiples modifications de la structure
- Plusieurs mises à jour des valeurs enum

## Table `prospects`

### Migrations Existantes
1. `2023_11_01_000002_create_prospects_table.php`
2. `2023_11_09_093408_create_prospects_table.php`
3. `2024_12_19_094727_add_notes_to_prospects_table.php`
4. `2024_12_19_114029_update_status_column_in_prospects_table.php`
5. `2024_12_23_034400_add_source_to_prospects_table.php`

### Problèmes Identifiés
- Double création de la table
- Modifications successives des colonnes de statut

## Table `clients`

### Migrations Existantes
1. `2023_11_01_000003_create_clients_table.php`
2. `2023_11_09_093409_create_clients_table.php`
3. `2024_12_23_003500_update_client_enum_values.php`
4. `2024_12_23_035000_add_contract_dates_to_clients_table.php`
5. `2024_12_23_045001_add_deleted_at_to_clients_table.php`

### Problèmes Identifiés
- Double création de la table
- Modifications des valeurs enum
- Ajouts successifs de colonnes

## Plan de Consolidation

### 1. Table `activities`
```sql
-- Structure finale souhaitée
CREATE TABLE activities (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    title varchar(255) NOT NULL,
    description text,
    type enum(...) NOT NULL DEFAULT 'other',
    status enum(...) NOT NULL DEFAULT 'pending',
    start_date datetime,
    end_date datetime,
    client_id bigint unsigned,
    prospect_id bigint unsigned,
    created_by bigint unsigned,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY (id)
);
```

### 2. Table `prospects`
```sql
-- Structure finale souhaitée
CREATE TABLE prospects (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    email varchar(255),
    phone varchar(255),
    status enum(...) NOT NULL DEFAULT 'new',
    source varchar(255),
    notes text,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY (id)
);
```

### 3. Table `clients`
```sql
-- Structure finale souhaitée
CREATE TABLE clients (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    email varchar(255),
    phone varchar(255),
    status enum(...) NOT NULL DEFAULT 'active',
    contract_start_date date,
    contract_end_date date,
    deleted_at timestamp NULL DEFAULT NULL,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY (id)
);
```

## Prochaines Étapes
1. Créer les nouvelles migrations consolidées
2. Tester la migration complète
3. Mettre à jour les seeders pour utiliser les nouvelles structures
4. Supprimer les anciennes migrations
