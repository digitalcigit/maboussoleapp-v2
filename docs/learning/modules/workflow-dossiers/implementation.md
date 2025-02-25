# Guide d'implémentation du Workflow des Dossiers

## 1. Structure du code

### Modèles principaux

```php
// app/Models/Dossier.php
class Dossier extends Model
{
    // Constantes des étapes
    public const STEP_INITIAL = 1;
    public const STEP_DOCUMENTS = 2;
    public const STEP_VALIDATION = 3;
    public const STEP_PAYMENT = 4;
    public const STEP_PROCESSING = 5;
    public const STEP_COMPLETED = 6;
    public const STEP_REJECTED = 7;

    // Constantes des statuts
    public const STATUS_WAITING_DOCS = 'waiting_docs';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_BLOCKED = 'blocked';
    public const STATUS_COMPLETED = 'completed';
}

// app/Models/DossierStep.php
class DossierStep extends Model
{
    public static function getStepNames(): array
    {
        return [
            Dossier::STEP_INITIAL => 'Création du dossier',
            Dossier::STEP_DOCUMENTS => 'Collecte des documents',
            Dossier::STEP_VALIDATION => 'Validation du dossier',
            Dossier::STEP_PAYMENT => 'Paiement des droits',
            Dossier::STEP_PROCESSING => 'Traitement en cours',
            Dossier::STEP_COMPLETED => 'Dossier terminé',
            Dossier::STEP_REJECTED => 'Dossier rejeté',
        ];
    }
}
```

### Interface d'administration

```php
// app/Filament/Resources/DossierResource.php
class DossierResource extends Resource
{
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Champs du formulaire
                TextInput::make('reference')
                    ->required()
                    ->unique(ignorable: fn ($record) => $record),
                Select::make('current_step')
                    ->options(DossierStep::getStepNames())
                    ->required(),
                Select::make('current_status')
                    ->options([
                        Dossier::STATUS_WAITING_DOCS => 'En attente de documents',
                        Dossier::STATUS_IN_PROGRESS => 'En cours',
                        Dossier::STATUS_BLOCKED => 'Bloqué',
                        Dossier::STATUS_COMPLETED => 'Terminé',
                    ])
                    ->required(),
                // ...autres champs
            ]);
    }
}
```

## 2. Migrations

```php
// database/migrations/2025_02_25_000008_update_dossier_workflow.php
public function up()
{
    Schema::table('dossiers', function (Blueprint $table) {
        if (!Schema::hasColumn('dossiers', 'current_step')) {
            $table->unsignedTinyInteger('current_step')
                ->default(Dossier::STEP_INITIAL);
        }
        
        if (!Schema::hasColumn('dossiers', 'current_status')) {
            $table->string('current_status')
                ->default(Dossier::STATUS_WAITING_DOCS);
        }
        
        // Ajout des champs d'information personnelle
        $table->string('name')->nullable();
        $table->string('email')->nullable();
        $table->string('phone')->nullable();
        // ...autres champs
    });
}
```

## 3. Widgets et composants UI

```php
// app/Filament/PortailCandidat/Widgets/DossierProgressWidget.php
class DossierProgressWidget extends Widget
{
    protected function getSteps(): array
    {
        return [
            Dossier::STEP_INITIAL => [
                'label' => 'Création du dossier',
                'icon' => 'heroicon-o-document-plus',
            ],
            // ...autres étapes
        ];
    }
}
```

## 4. Validation et règles métier

```php
// app/Rules/DossierStepValidation.php
class DossierStepValidation implements Rule
{
    public function passes($attribute, $value)
    {
        $dossier = Dossier::find($value);
        
        // Vérification des documents requis
        if (!$dossier->hasRequiredDocuments()) {
            return false;
        }
        
        // Vérification du paiement si nécessaire
        if ($dossier->current_step >= Dossier::STEP_PAYMENT) {
            return $dossier->hasValidPayment();
        }
        
        return true;
    }
}
```

## 5. Events et listeners

```php
// app/Events/DossierStepChanged.php
class DossierStepChanged
{
    public function __construct(
        public Dossier $dossier,
        public int $oldStep,
        public int $newStep
    ) {}
}

// app/Listeners/UpdateDossierStatus.php
class UpdateDossierStatus
{
    public function handle(DossierStepChanged $event)
    {
        // Mise à jour automatique du statut
        if ($event->dossier->needsDocuments()) {
            $event->dossier->update([
                'current_status' => Dossier::STATUS_WAITING_DOCS
            ]);
        }
    }
}
```

## 6. Tests

```php
// tests/Unit/DossierTest.php
class DossierTest extends TestCase
{
    public function test_dossier_status_changes_correctly()
    {
        $dossier = Dossier::factory()->create([
            'current_step' => Dossier::STEP_INITIAL
        ]);
        
        $dossier->moveToNextStep();
        
        $this->assertEquals(
            Dossier::STEP_DOCUMENTS,
            $dossier->current_step
        );
    }
    
    public function test_prospect_client_status()
    {
        $dossier = Dossier::factory()->create([
            'current_step' => Dossier::STEP_INITIAL
        ]);
        
        $this->assertTrue($dossier->isProspect());
        $this->assertFalse($dossier->isClient());
        
        $dossier->update(['current_step' => Dossier::STEP_PAYMENT]);
        
        $this->assertFalse($dossier->isProspect());
        $this->assertTrue($dossier->isClient());
    }
}
```

## 7. Optimisation des performances

### Indexes de base de données

```php
// database/migrations/2025_02_25_000009_add_dossier_indexes.php
public function up()
{
    Schema::table('dossiers', function (Blueprint $table) {
        $table->index(['current_step', 'current_status']);
        $table->index('email');
    });
}
```

### Cache

```php
// app/Models/Dossier.php
public function getRequiredDocuments(): Collection
{
    return Cache::remember(
        "dossier.{$this->id}.required_documents",
        now()->addHours(24),
        fn () => $this->calculateRequiredDocuments()
    );
}
```
