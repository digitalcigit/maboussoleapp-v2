# Structure de la Base de Données - MaBoussole CRM v2

## Vue d'Ensemble
Le système utilise une architecture relationnelle avec soft deletes et relations polymorphiques pour le suivi des activités.

## Tables Principales

### 1. Prospects (`prospects`)

#### Structure
```sql
CREATE TABLE prospects (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    reference_number varchar(255) UNIQUE,     -- Numéro de référence unique
    first_name varchar(255),                  -- Prénom
    last_name varchar(255),                   -- Nom
    email varchar(255) NULL,                  -- Email
    phone varchar(255) NULL,                  -- Téléphone
    birth_date date NULL,                     -- Date de naissance
    profession varchar(255) NULL,             -- Profession actuelle
    education_level varchar(255) NULL,        -- Niveau d'études
    current_location varchar(255) NULL,       -- Localisation actuelle
    current_field varchar(255) NULL,          -- Domaine actuel
    desired_field varchar(255) NULL,          -- Domaine souhaité
    desired_destination varchar(255) NULL,    -- Destination souhaitée
    emergency_contact json NULL,              -- Contact d'urgence (JSON)
    status enum(                              -- Statut du prospect
        'nouveau',                            -- Nouveau prospect
        'en_analyse',                         -- En cours d'analyse
        'validé',                             -- Dossier validé
        'rejeté',                            -- Dossier rejeté
        'converti'                           -- Converti en client
    ) DEFAULT 'nouveau',
    assigned_to bigint unsigned NULL,         -- Conseiller assigné
    commercial_code varchar(255) NULL,        -- Code commercial
    partner_id bigint unsigned NULL,          -- Partenaire associé
    last_action_at timestamp NULL,            -- Dernière action
    analysis_deadline timestamp NULL,         -- Date limite d'analyse
    notes text NULL,                          -- Notes générales
    created_at timestamp NULL,                -- Date de création
    updated_at timestamp NULL,                -- Date de mise à jour
    deleted_at timestamp NULL,                -- Date de suppression (soft delete)
    PRIMARY KEY (id)
);
```

#### Relations
- `assigned_to` → `users.id` (Conseiller)
- `partner_id` → `users.id` (Partenaire)
- `activities` ← Relation polymorphique

### 2. Clients (`clients`)

#### Structure
```sql
CREATE TABLE clients (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    prospect_id bigint unsigned,              -- ID du prospect d'origine
    client_number varchar(255) UNIQUE,        -- Numéro client unique
    passport_number varchar(255) NULL,        -- Numéro de passeport
    passport_expiry date NULL,                -- Date d'expiration du passeport
    visa_status enum(                         -- Statut du visa
        'non_commencé',                       -- Processus non démarré
        'en_cours',                          -- En cours de traitement
        'obtenu',                            -- Visa obtenu
        'rejeté'                             -- Visa rejeté
    ) DEFAULT 'non_commencé',
    travel_preferences json NULL,             -- Préférences de voyage (JSON)
    payment_status enum(                      -- Statut du paiement
        'en_attente',                        -- Paiement en attente
        'partiel',                           -- Paiement partiel
        'complété'                           -- Paiement complet
    ) DEFAULT 'en_attente',
    total_amount decimal(10,2) DEFAULT 0,     -- Montant total
    paid_amount decimal(10,2) DEFAULT 0,      -- Montant payé
    status enum(                              -- Statut du client
        'actif',                             -- Client actif
        'inactif',                           -- Client inactif
        'en_attente',                        -- En attente
        'archivé'                            -- Archivé
    ) DEFAULT 'actif',
    created_at timestamp NULL,                -- Date de création
    updated_at timestamp NULL,                -- Date de mise à jour
    deleted_at timestamp NULL,                -- Date de suppression (soft delete)
    PRIMARY KEY (id)
);
```

#### Relations
- `prospect_id` → `prospects.id`
- `activities` ← Relation polymorphique

### 3. Activities (`activities`)

#### Structure
```sql
CREATE TABLE activities (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    user_id bigint unsigned NULL,             -- Utilisateur responsable
    subject_type varchar(255),                -- Type de sujet (polymorphique)
    subject_id bigint unsigned,               -- ID du sujet (polymorphique)
    type enum(                                -- Type d'activité
        'note',                              -- Note simple
        'appel',                             -- Appel téléphonique
        'email',                             -- Email
        'rendez_vous',                       -- Rendez-vous
        'document',                          -- Document
        'conversion'                         -- Conversion prospect→client
    ),
    description text,                         -- Description de l'activité
    scheduled_at timestamp NULL,              -- Date planifiée
    completed_at timestamp NULL,              -- Date de réalisation
    status enum(                              -- Statut de l'activité
        'en_attente',                        -- En attente
        'en_cours',                          -- En cours
        'terminé',                           -- Terminé
        'annulé'                             -- Annulé
    ) DEFAULT 'en_attente',
    created_by bigint unsigned NULL,          -- Créé par
    created_at timestamp NULL,                -- Date de création
    updated_at timestamp NULL,                -- Date de mise à jour
    deleted_at timestamp NULL,                -- Date de suppression (soft delete)
    PRIMARY KEY (id)
);
```

#### Relations
- `user_id` → `users.id` (Responsable)
- `created_by` → `users.id` (Créateur)
- `subject` → Relation polymorphique vers `prospects` ou `clients`

## Indexation et Performance

### Indexes Clés
1. **Prospects**
   - `reference_number` (UNIQUE)
   - `status`
   - `assigned_to`
   - `last_action_at`

2. **Clients**
   - `client_number` (UNIQUE)
   - `prospect_id`
   - `visa_status`
   - `payment_status`

3. **Activities**
   - `subject_type, subject_id`
   - `type`
   - `status`
   - `scheduled_at`

## Contraintes et Intégrité

### Soft Deletes
Toutes les tables principales utilisent le soft delete via `deleted_at`

### Clés Étrangères
1. **Prospects**
   ```sql
   ALTER TABLE prospects
   ADD CONSTRAINT fk_prospects_assigned_to FOREIGN KEY (assigned_to)
   REFERENCES users (id) ON DELETE SET NULL,
   ADD CONSTRAINT fk_prospects_partner FOREIGN KEY (partner_id)
   REFERENCES users (id) ON DELETE SET NULL;
   ```

2. **Clients**
   ```sql
   ALTER TABLE clients
   ADD CONSTRAINT fk_clients_prospect FOREIGN KEY (prospect_id)
   REFERENCES prospects (id) ON DELETE CASCADE;
   ```

3. **Activities**
   ```sql
   ALTER TABLE activities
   ADD CONSTRAINT fk_activities_user FOREIGN KEY (user_id)
   REFERENCES users (id) ON DELETE SET NULL,
   ADD CONSTRAINT fk_activities_creator FOREIGN KEY (created_by)
   REFERENCES users (id) ON DELETE SET NULL;
   ```

## Validation des Données

### Règles de Validation
1. **Prospects**
   - `reference_number` : unique, format spécifique
   - `email` : format email valide
   - `phone` : format téléphone valide
   - `birth_date` : date valide, passée

2. **Clients**
   - `client_number` : unique, format spécifique
   - `passport_expiry` : date future
   - `paid_amount` : ≤ `total_amount`

3. **Activities**
   - `completed_at` : null si status ≠ 'terminé'
   - `scheduled_at` : requis pour rendez_vous

## Notes d'Implémentation

### Bonnes Pratiques
1. Toujours utiliser les constantes des modèles pour les énumérations
2. Valider les données avant insertion/mise à jour
3. Utiliser les relations Eloquent définies dans les modèles
4. Gérer les transactions pour les opérations multiples

### Exemple d'Utilisation
```php
// Création d'un prospect avec activité
DB::transaction(function () {
    $prospect = Prospect::create([
        'reference_number' => 'PROS-' . date('Ymd') . '-' . rand(1000, 9999),
        'first_name' => 'Jean',
        'last_name' => 'Dupont',
        'status' => Prospect::STATUS_NEW
    ]);

    $prospect->activities()->create([
        'type' => Activity::TYPE_NOTE,
        'description' => 'Création du prospect',
        'status' => Activity::STATUS_COMPLETED,
        'completed_at' => now(),
        'created_by' => auth()->id()
    ]);
});
```
