# Guide d'implémentation du suivi des dossiers

## Structure de la base de données

### Table `dossiers`
```sql
ALTER TABLE dossiers
ADD COLUMN created_by BIGINT UNSIGNED NULL AFTER id,
ADD CONSTRAINT fk_dossiers_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;
```

## Modèle Dossier

```php
class Dossier extends Model
{
    protected $fillable = [
        'created_by',
        // autres champs...
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
```

## Widget de métriques

```php
class UserDossiersWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        
        return [
            Stat::make('Total des dossiers', 
                Dossier::where('created_by', $user->id)->count()
            ),
            // autres statistiques...
        ];
    }
}
```

## Formulaire Filament

```php
Forms\Components\Hidden::make('created_by')
    ->default(auth()->id())
```

## Tests unitaires

```php
class DossierTest extends TestCase
{
    public function test_dossier_tracks_creator()
    {
        $user = User::factory()->create();
        $dossier = Dossier::factory()->create([
            'created_by' => $user->id
        ]);
        
        $this->assertEquals($user->id, $dossier->creator->id);
    }
}
```
