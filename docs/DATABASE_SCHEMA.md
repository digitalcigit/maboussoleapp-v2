# Schéma de Base de Données - MaBoussole CRM v2

## Tables Principales

### 1. Users
```sql
users
├── id (bigint, primary key)
├── name (varchar)
├── email (varchar, unique)
├── password (varchar)
├── role (enum: super_admin, manager, advisor, partner, commercial)
├── phone (varchar)
├── status (enum: active, inactive)
├── email_verified_at (timestamp)
├── phone_verified_at (timestamp)
├── last_login_at (timestamp)
└── timestamps (created_at, updated_at)
```

### 2. Profiles
```sql
profiles
├── id (bigint, primary key)
├── user_id (bigint, foreign key)
├── avatar (varchar, nullable)
├── address (text, nullable)
├── city (varchar)
├── country (varchar)
├── bio (text, nullable)
├── settings (json)
└── timestamps
```

### 3. Prospects
```sql
prospects
├── id (bigint, primary key)
├── reference_number (varchar, unique)
├── first_name (varchar)
├── last_name (varchar)
├── email (varchar)
├── phone (varchar)
├── birth_date (date)
├── profession (varchar)
├── education_level (varchar)
├── current_location (varchar)
├── current_field (varchar)
├── desired_field (varchar)
├── desired_destination (varchar)
├── emergency_contact (json)
├── status (enum: new, analyzing, approved, rejected, converted)
├── assigned_to (bigint, foreign key -> users.id)
├── commercial_code (varchar, nullable)
├── partner_id (bigint, foreign key -> users.id, nullable)
├── last_action_at (timestamp)
├── analysis_deadline (timestamp)
└── timestamps
```

### 4. Clients
```sql
clients
├── id (bigint, primary key)
├── prospect_id (bigint, foreign key)
├── client_number (varchar, unique)
├── passport_number (varchar)
├── passport_expiry (date)
├── visa_status (enum: not_started, in_progress, obtained, rejected)
├── travel_preferences (json)
├── payment_status (enum: pending, partial, completed)
├── total_amount (decimal)
├── paid_amount (decimal)
└── timestamps
```

### 5. Documents
```sql
documents
├── id (bigint, primary key)
├── documentable_id (bigint)
├── documentable_type (varchar)
├── name (varchar)
├── type (enum: passport, cv, diploma, etc)
├── path (varchar)
├── size (bigint)
├── status (enum: pending, validated, rejected)
├── validated_by (bigint, foreign key -> users.id)
├── validation_date (timestamp)
├── comments (text)
└── timestamps
```

### 6. Activities
```sql
activities
├── id (bigint, primary key)
├── user_id (bigint, foreign key)
├── subject_type (varchar)
├── subject_id (bigint)
├── type (enum: note, call, email, meeting, etc)
├── description (text)
├── scheduled_at (timestamp, nullable)
├── completed_at (timestamp, nullable)
└── timestamps
```

### 7. Notifications
```sql
notifications
├── id (uuid, primary key)
├── type (varchar)
├── notifiable_type (varchar)
├── notifiable_id (bigint)
├── data (json)
├── read_at (timestamp)
└── timestamps
```

### 8. Steps
```sql
steps
├── id (bigint, primary key)
├── client_id (bigint, foreign key)
├── type (enum: document_submission, visa_application, etc)
├── status (enum: pending, in_progress, completed, failed)
├── start_date (timestamp)
├── due_date (timestamp)
├── completed_at (timestamp)
├── assigned_to (bigint, foreign key -> users.id)
├── notes (text)
└── timestamps
```

## Relations

### One-to-One
- User -> Profile
- Prospect -> Client

### One-to-Many
- User -> Prospects (assigned_to)
- User -> Activities
- Client -> Steps

### Many-to-Many
- Users <-> Roles (via role_user)
- Users <-> Permissions (via permission_user)

### Polymorphic
- Documents (documentable)
- Activities (subject)
- Notifications (notifiable)

## Indexes
```sql
-- Users
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);

-- Prospects
CREATE INDEX idx_prospects_status ON prospects(status);
CREATE INDEX idx_prospects_assigned_to ON prospects(assigned_to);
CREATE INDEX idx_prospects_commercial_code ON prospects(commercial_code);

-- Clients
CREATE INDEX idx_clients_visa_status ON clients(visa_status);
CREATE INDEX idx_clients_payment_status ON clients(payment_status);

-- Documents
CREATE INDEX idx_documents_status ON documents(status);
CREATE INDEX idx_documents_type ON documents(type);

-- Activities
CREATE INDEX idx_activities_subject ON activities(subject_type, subject_id);
CREATE INDEX idx_activities_scheduled ON activities(scheduled_at);
```

## Triggers
```sql
-- Mise à jour automatique last_action_at sur les prospects
CREATE TRIGGER update_prospect_last_action
AFTER INSERT ON activities
FOR EACH ROW
WHEN NEW.subject_type = 'prospect'
BEGIN
    UPDATE prospects 
    SET last_action_at = NEW.created_at
    WHERE id = NEW.subject_id;
END;

-- Notification automatique pour les deadlines approchantes
CREATE TRIGGER check_prospect_deadline
AFTER UPDATE ON prospects
FOR EACH ROW
WHEN NEW.analysis_deadline < NOW() + INTERVAL '24 HOUR'
BEGIN
    INSERT INTO notifications (...)
    VALUES (...);
END;
```
