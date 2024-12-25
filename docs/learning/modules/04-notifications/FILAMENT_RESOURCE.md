# Documentation de la Resource Filament Notification

## Structure des Fichiers

```yaml
NotificationResource/
  ├── NotificationResource.php           # Resource principale
  ├── RelationManagers/
  │   └── NotificationsRelationManager.php  # Pour autres ressources
  └── Pages/
      ├── ListNotifications.php         # Liste des notifications
      ├── ViewNotification.php          # Vue détaillée
      └── ManageNotifications.php       # Gestion globale
```

## Resource Principale

### Configuration Globale
```php
class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;
    protected static ?string $navigationIcon = 'heroicon-o-bell';
    protected static ?string $navigationGroup = 'Système';
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::unread()->count();
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::unread()->count() > 0
            ? 'warning'
            : 'primary';
    }
}
```

### Table
```php
public static function table(Table $table): Table
{
    return $table
        ->columns([
            IconColumn::make('type')
                ->icon(fn (Notification $record): string => $record->icon)
                ->color(fn (Notification $record): string => 
                    $record->isRead() ? 'secondary' : 'primary'
                ),
                
            TextColumn::make('data.title')
                ->label('Titre')
                ->searchable()
                ->sortable(),
                
            BadgeColumn::make('data.priority')
                ->label('Priorité')
                ->colors([
                    'danger' => 'high',
                    'warning' => 'medium',
                    'success' => 'low',
                ]),
                
            TextColumn::make('notifiable_type')
                ->label('Type')
                ->formatStateUsing(fn ($state) => class_basename($state)),
                
            TextColumn::make('formatted_date')
                ->label('Date')
                ->sortable('created_at'),
                
            IconColumn::make('read_at')
                ->label('Lu')
                ->boolean(),
        ])
        ->filters([
            SelectFilter::make('type')
                ->options([
                    Notification::TYPE_PROSPECT => 'Prospects',
                    Notification::TYPE_DOCUMENT => 'Documents',
                    Notification::TYPE_CLIENT => 'Clients',
                    Notification::TYPE_SYSTEM => 'Système',
                ]),
                
            Filter::make('unread')
                ->query(fn ($query) => $query->whereNull('read_at')),
                
            Filter::make('priority_high')
                ->query(fn ($query) => $query->whereJsonContains('data->priority', 'high')),
        ])
        ->actions([
            Action::make('markAsRead')
                ->icon('heroicon-o-check')
                ->action(fn (Notification $record) => $record->markAsRead())
                ->visible(fn (Notification $record) => !$record->isRead()),
                
            Action::make('markAsUnread')
                ->icon('heroicon-o-x')
                ->action(fn (Notification $record) => $record->markAsUnread())
                ->visible(fn (Notification $record) => $record->isRead()),
                
            ViewAction::make(),
        ])
        ->bulkActions([
            BulkAction::make('markAsRead')
                ->action(fn (Collection $records) => $records->each->markAsRead())
                ->deselectRecordsAfterCompletion(),
                
            DeleteBulkAction::make(),
        ]);
}
```

### Page de Vue
```php
class ViewNotification extends ViewRecord
{
    protected function getHeaderActions(): array
    {
        return [
            Action::make('markAsRead')
                ->icon('heroicon-o-check')
                ->action(fn () => $this->record->markAsRead())
                ->visible(fn () => !$this->record->isRead()),
                
            Action::make('goToSource')
                ->icon('heroicon-o-arrow-right')
                ->url(fn () => $this->getSourceUrl())
                ->openUrlInNewTab(),
        ];
    }
    
    protected function getSourceUrl(): ?string
    {
        return match($this->record->notifiable_type) {
            'App\\Models\\Prospect' => 
                ProspectResource::getUrl('view', ['record' => $this->record->notifiable_id]),
            'App\\Models\\Client' =>
                ClientResource::getUrl('view', ['record' => $this->record->notifiable_id]),
            'App\\Models\\Document' =>
                DocumentResource::getUrl('view', ['record' => $this->record->notifiable_id]),
            default => null,
        };
    }
}
```

## RelationManager

```php
class NotificationsRelationManager extends RelationManager
{
    protected static string $relationship = 'notifications';
    
    protected static ?string $recordTitleAttribute = 'title';
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('type')
                    ->icon(fn ($record) => $record->icon),
                    
                TextColumn::make('data.title')
                    ->label('Titre'),
                    
                TextColumn::make('formatted_date')
                    ->label('Date'),
                    
                IconColumn::make('read_at')
                    ->boolean(),
            ])
            ->filters([
                Filter::make('unread')
                    ->query(fn ($query) => $query->whereNull('read_at')),
            ])
            ->headerActions([
                Action::make('markAllAsRead')
                    ->action(fn () => $this->getRelationship()->update(['read_at' => now()])),
            ])
            ->actions([
                ViewAction::make(),
                Action::make('markAsRead')
                    ->icon('heroicon-o-check')
                    ->action(fn ($record) => $record->markAsRead()),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
```

## Points d'Apprentissage

### 1. Interface Utilisateur
```yaml
Composants:
  - Badges dynamiques
  - Icônes contextuelles
  - Actions rapides
  - Filtres efficaces
```

### 2. Interactions
```yaml
Actions:
  - Marquer comme lu/non lu
  - Navigation source
  - Suppression
  - Actions groupées
```

### 3. Performance
```yaml
Optimisations:
  - Lazy loading
  - Eager loading
  - Cache
  - Pagination
```

## Exemples d'Utilisation

### 1. Widget Notifications
```php
class NotificationsWidget extends Widget
{
    protected static ?int $sort = -2;
    
    protected int|string|array $columnSpan = 'full';
    
    public function getNotifications(): Collection
    {
        return Notification::unread()
            ->latest()
            ->take(5)
            ->get();
    }
    
    public function render(): View
    {
        return view('filament.widgets.notifications', [
            'notifications' => $this->getNotifications(),
        ]);
    }
}
```

### 2. Actions Personnalisées
```php
Action::make('processNotification')
    ->icon('heroicon-o-cog')
    ->action(function (Notification $record) {
        match($record->type) {
            Notification::TYPE_PROSPECT => $this->handleProspectNotification($record),
            Notification::TYPE_DOCUMENT => $this->handleDocumentNotification($record),
            default => null,
        };
    })
    ->requiresConfirmation();
```

### 3. Filtres Avancés
```php
Filter::make('dateRange')
    ->form([
        DatePicker::make('created_from'),
        DatePicker::make('created_until'),
    ])
    ->query(function ($query, array $data) {
        return $query
            ->when(
                $data['created_from'],
                fn ($query) => $query->whereDate('created_at', '>=', $data['created_from'])
            )
            ->when(
                $data['created_until'],
                fn ($query) => $query->whereDate('created_at', '<=', $data['created_until'])
            );
    });
```
