# Bonnes Pratiques - MaBoussole CRM v2

> Dernière mise à jour : 2024-12-21

## Vue d'ensemble
Guide des bonnes pratiques pour le développement de MaBoussole CRM v2.

## Architecture

### SOLID Principles
```php
// Single Responsibility Principle
class ProspectService
{
    public function __construct(
        private readonly ProspectRepository $repository,
        private readonly ValidationService $validator,
        private readonly NotificationService $notifier
    ) {
    }

    public function createProspect(array $data): Prospect
    {
        $this->validator->validate($data);
        $prospect = $this->repository->create($data);
        $this->notifier->notifyNewProspect($prospect);
        
        return $prospect;
    }
}

// Open/Closed Principle
interface NotificationChannel
{
    public function send(string $message, User $recipient): void;
}

class EmailNotification implements NotificationChannel
{
    public function send(string $message, User $recipient): void
    {
        // Implementation
    }
}

class SMSNotification implements NotificationChannel
{
    public function send(string $message, User $recipient): void
    {
        // Implementation
    }
}
```

### Design Patterns

#### Repository Pattern
```php
interface ProspectRepositoryInterface
{
    public function find(int $id): ?Prospect;
    public function create(array $data): Prospect;
    public function update(Prospect $prospect, array $data): Prospect;
}

class ProspectRepository implements ProspectRepositoryInterface
{
    public function find(int $id): ?Prospect
    {
        return Prospect::find($id);
    }
    
    // Implementation
}
```

#### Service Pattern
```php
class DocumentService
{
    public function __construct(
        private readonly StorageService $storage,
        private readonly ValidationService $validator
    ) {
    }

    public function uploadDocument(UploadedFile $file, array $metadata): Document
    {
        $this->validator->validateFile($file);
        $path = $this->storage->store($file);
        
        return Document::create([
            'path' => $path,
            'metadata' => $metadata
        ]);
    }
}
```

## Sécurité

### Validation
```php
class ProspectRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'unique:prospects'],
            'phone' => ['required', 'regex:/^\+?[1-9]\d{1,14}$/'],
            'documents.*' => ['file', 'mimes:pdf,jpg,png', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Ce prospect existe déjà.',
            'phone.regex' => 'Format de téléphone invalide.',
            'documents.*.max' => 'Le document ne doit pas dépasser 10Mo.',
        ];
    }
}
```

### Autorisations
```php
class ProspectPolicy
{
    public function view(User $user, Prospect $prospect): bool
    {
        return $user->hasRole('admin') || 
               $prospect->advisor_id === $user->id;
    }

    public function update(User $user, Prospect $prospect): bool
    {
        return $user->hasPermissionTo('prospects.edit') && 
               ($user->hasRole('admin') || 
                $prospect->advisor_id === $user->id);
    }
}
```

## Performance

### Cache
```php
class ProspectStatistics
{
    public function getMonthlyStats(): array
    {
        return Cache::remember('prospect_monthly_stats', 3600, function () {
            return DB::table('prospects')
                ->select(DB::raw('COUNT(*) as count'), 'status')
                ->whereMonth('created_at', now()->month)
                ->groupBy('status')
                ->get()
                ->toArray();
        });
    }
}
```

### N+1 Query Prevention
```php
// ✅ Bon
$prospects = Prospect::with(['advisor', 'documents'])
    ->whereStatus('active')
    ->get();

// ❌ Mauvais
$prospects = Prospect::whereStatus('active')->get();
foreach ($prospects as $prospect) {
    $prospect->advisor;  // N+1 query!
}
```

## Tests

### Test Data
```php
class ProspectFactory extends Factory
{
    protected $model = Prospect::class;

    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'status' => $this->faker->randomElement(['new', 'analyzing', 'validated']),
        ];
    }

    public function validated(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'validated',
                'validated_at' => now(),
            ];
        });
    }
}
```

### Tests Unitaires
```php
class ProspectValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_requires_valid_email()
    {
        $this->expectException(ValidationException::class);

        $prospect = Prospect::factory()->make([
            'email' => 'invalid-email'
        ]);

        $prospect->save();
    }

    /** @test */
    public function it_can_be_validated()
    {
        $prospect = Prospect::factory()->create(['status' => 'new']);
        $advisor = User::factory()->create();

        $prospect->validate($advisor);

        $this->assertEquals('validated', $prospect->status);
        $this->assertNotNull($prospect->validated_at);
        $this->assertEquals($advisor->id, $prospect->validator_id);
    }
}
```

## Logging

### Structured Logging
```php
class ProspectController
{
    public function store(ProspectRequest $request): JsonResponse
    {
        try {
            $prospect = $this->service->create($request->validated());

            Log::info('Prospect created', [
                'prospect_id' => $prospect->id,
                'advisor_id' => $prospect->advisor_id,
                'source' => $request->input('source'),
            ]);

            return response()->json($prospect, 201);
        } catch (Exception $e) {
            Log::error('Failed to create prospect', [
                'error' => $e->getMessage(),
                'data' => $request->validated(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
```

## Documentation

### API Documentation
```php
/**
 * @OA\Post(
 *     path="/api/prospects",
 *     summary="Create a new prospect",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email","first_name","last_name"},
 *             @OA\Property(property="email", type="string"),
 *             @OA\Property(property="first_name", type="string"),
 *             @OA\Property(property="last_name", type="string"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Prospect created successfully"
 *     )
 * )
 */
public function store(ProspectRequest $request): JsonResponse
{
    // Implementation
}
```

## Error Handling

### Custom Exceptions
```php
class ProspectException extends Exception
{
    public static function notEligible(Prospect $prospect): self
    {
        return new self(
            "Prospect {$prospect->id} is not eligible for conversion",
            422
        );
    }

    public static function alreadyConverted(Prospect $prospect): self
    {
        return new self(
            "Prospect {$prospect->id} has already been converted",
            422
        );
    }
}
```

### Exception Handler
```php
class Handler extends ExceptionHandler
{
    protected $dontReport = [
        ProspectException::class,
    ];

    public function register(): void
    {
        $this->renderable(function (ProspectException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ], 422);
        });
    }
}
```

## Code Review

### Checklist
```markdown
## Qualité du Code
- [ ] Suit les standards PSR-12
- [ ] Utilise les type hints
- [ ] Documentation PHPDoc complète
- [ ] Tests ajoutés/mis à jour

## Sécurité
- [ ] Validation des entrées
- [ ] Autorisations vérifiées
- [ ] Pas de vulnérabilités SQL
- [ ] Gestion sécurisée des fichiers

## Performance
- [ ] Pas de N+1 queries
- [ ] Utilisation appropriée du cache
- [ ] Indexes de base de données
- [ ] Optimisation des requêtes
```

---
*Documentation générée pour MaBoussole CRM v2*
