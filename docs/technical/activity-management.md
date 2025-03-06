# Gestion des Activités

## Vue d'ensemble
Le système de gestion des activités permet de suivre et d'enregistrer différentes interactions avec les dossiers de candidats. Ce module a été conçu pour améliorer le suivi des dossiers, automatiser certaines communications et fournir un historique complet des interactions avec chaque candidat.

## Types d'activités
Le système prend en charge plusieurs types d'activités :

1. **Note** : Annotations textuelles concernant un dossier
2. **Appel** : Enregistrement des appels téléphoniques avec les candidats
3. **Email** : Suivi des communications par email
4. **Réunion** : Planification et comptes-rendus de réunions
5. **Document** : Activités liées aux documents (demandes, réceptions)
6. **Conversion** : Activités marquant la progression dans le processus

## Implémentation technique

### Modèle d'activité
Le modèle `Activity` est au cœur de cette fonctionnalité :

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    protected $fillable = [
        'dossier_id',
        'user_id',
        'activity_type',
        'title',
        'description',
        'scheduled_at',
        'completed_at',
        'metadata',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Types d'activités
    const TYPE_NOTE = 'note';
    const TYPE_CALL = 'call';
    const TYPE_EMAIL = 'email';
    const TYPE_MEETING = 'meeting';
    const TYPE_DOCUMENT = 'document';
    const TYPE_CONVERSION = 'conversion';

    /**
     * Obtenir le dossier associé à cette activité
     */
    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    /**
     * Obtenir l'utilisateur qui a créé cette activité
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
```

### Ressource Filament pour les activités
La ressource `ActivityResource` dans `app/Filament/Resources/ActivityResource.php` fournit l'interface d'administration pour gérer les activités :

```php
namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages;
use App\Models\Activity;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-list';
    protected static ?string $navigationLabel = 'Activités';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('dossier_id')
                    ->relationship('dossier', 'reference')
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('activity_type')
                    ->options([
                        Activity::TYPE_NOTE => 'Note',
                        Activity::TYPE_CALL => 'Appel',
                        Activity::TYPE_EMAIL => 'Email',
                        Activity::TYPE_MEETING => 'Réunion',
                        Activity::TYPE_DOCUMENT => 'Document',
                        Activity::TYPE_CONVERSION => 'Conversion',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->rows(3),
                Forms\Components\DateTimePicker::make('scheduled_at'),
                Forms\Components\DateTimePicker::make('completed_at'),
                Forms\Components\Hidden::make('user_id')
                    ->default(fn () => auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dossier.reference')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('activity_type')
                    ->enum([
                        Activity::TYPE_NOTE => 'Note',
                        Activity::TYPE_CALL => 'Appel',
                        Activity::TYPE_EMAIL => 'Email',
                        Activity::TYPE_MEETING => 'Réunion',
                        Activity::TYPE_DOCUMENT => 'Document',
                        Activity::TYPE_CONVERSION => 'Conversion',
                    ])
                    ->colors([
                        'primary' => Activity::TYPE_NOTE,
                        'secondary' => Activity::TYPE_CALL,
                        'success' => Activity::TYPE_EMAIL,
                        'warning' => Activity::TYPE_MEETING,
                        'danger' => Activity::TYPE_DOCUMENT,
                        'info' => Activity::TYPE_CONVERSION,
                    ]),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Créé par')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('activity_type')
                    ->options([
                        Activity::TYPE_NOTE => 'Note',
                        Activity::TYPE_CALL => 'Appel',
                        Activity::TYPE_EMAIL => 'Email',
                        Activity::TYPE_MEETING => 'Réunion',
                        Activity::TYPE_DOCUMENT => 'Document',
                        Activity::TYPE_CONVERSION => 'Conversion',
                    ]),
                Tables\Filters\Filter::make('scheduled')
                    ->query(fn ($query) => $query->whereNotNull('scheduled_at')->whereNull('completed_at')),
                Tables\Filters\Filter::make('completed')
                    ->query(fn ($query) => $query->whereNotNull('completed_at')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                // Action personnalisée pour marquer comme complétée
                Tables\Actions\Action::make('complete')
                    ->label('Marquer comme terminée')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Activity $record) => is_null($record->completed_at))
                    ->action(fn (Activity $record) => $record->update(['completed_at' => now()])),
            ]);
    }
}
```

## Système d'emails automatiques
Une des fonctionnalités clés du système d'activités est l'envoi automatique d'emails de rappel aux personnes n'ayant pas encore fourni de documents après un délai de 7 jours.

### Configuration de la tâche planifiée
Cette fonctionnalité est implémentée via une tâche CRON qui s'exécute quotidiennement :

```php
namespace App\Console\Commands;

use App\Models\Dossier;
use App\Models\Activity;
use App\Mail\DocumentReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDocumentReminders extends Command
{
    protected $signature = 'app:send-document-reminders';
    protected $description = 'Envoie des rappels aux candidats n\'ayant pas fourni leurs documents';

    public function handle()
    {
        // Trouver les dossiers en attente de documents depuis plus de 7 jours
        $dossiers = Dossier::where('current_status', Dossier::STATUS_WAITING_DOCS)
            ->where('last_action_at', '<=', now()->subDays(7))
            ->get();

        $count = 0;
        foreach ($dossiers as $dossier) {
            // Vérifier si un rappel a déjà été envoyé cette semaine
            $recentReminder = Activity::where('dossier_id', $dossier->id)
                ->where('activity_type', Activity::TYPE_EMAIL)
                ->where('title', 'LIKE', '%Rappel de documents%')
                ->where('created_at', '>=', now()->subDays(7))
                ->exists();

            if (!$recentReminder) {
                // Envoyer l'email
                Mail::to($dossier->email)->send(new DocumentReminder($dossier));

                // Enregistrer l'activité
                Activity::create([
                    'dossier_id' => $dossier->id,
                    'user_id' => 1, // ID du système
                    'activity_type' => Activity::TYPE_EMAIL,
                    'title' => 'Rappel de documents automatique',
                    'description' => 'Email automatique de rappel pour les documents manquants',
                    'completed_at' => now(),
                ]);

                $count++;
            }
        }

        $this->info("$count rappels de documents ont été envoyés.");
    }
}
```

### Configuration dans Laravel Scheduler
La commande est configurée dans le fichier `app/Console/Kernel.php` :

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('app:send-document-reminders')
        ->dailyAt('09:00')
        ->timezone('Africa/Abidjan');
}
```

## Flux de travail des activités

### Création d'une activité
1. L'utilisateur accède à la section Activités dans le panel d'administration
2. Il choisit un dossier et un type d'activité
3. Il remplit les détails de l'activité
4. L'utilisateur enregistre l'activité (le champ `user_id` est automatiquement rempli)

### Planification d'activités
1. L'utilisateur peut définir une date future via le champ `scheduled_at`
2. Les activités planifiées apparaissent dans un filtre dédié
3. Le tableau de bord affiche les prochaines activités planifiées

### Complétion d'activités
1. Lorsqu'une activité est terminée, l'utilisateur peut la marquer comme complétée
2. Le champ `completed_at` est rempli avec la date/heure actuelle
3. Les activités complétées sont visibles dans un filtre dédié

## Intégration avec les dossiers
Chaque activité est associée à un dossier spécifique, ce qui permet :
1. De voir toutes les activités liées à un dossier particulier
2. D'analyser les interactions par type et par période
3. De générer des rapports sur l'engagement avec les candidats

## Considérations futures
1. **Notifications** : Envoyer des notifications en temps réel pour les activités planifiées
2. **Intégration calendrier** : Synchroniser les activités avec des calendriers externes (Google, Outlook)
3. **Automatisation avancée** : Créer des flux de travail automatisés basés sur des séquences d'activités
4. **Analytics** : Ajouter des tableaux de bord spécifiques pour analyser l'efficacité des différentes activités
