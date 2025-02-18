<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;

class NewUserNotification extends Notification
{
    use Queueable;

    public function __construct(
        public User $newUser,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Nouvel utilisateur',
            'message' => "L'utilisateur {$this->newUser->name} vient de s'inscrire",
            'user_id' => $this->newUser->id,
        ];
    }

    public function toFilament(object $notifiable): FilamentNotification
    {
        return FilamentNotification::make()
            ->title('Nouvel utilisateur')
            ->icon('heroicon-o-user-plus')
            ->body("L'utilisateur {$this->newUser->name} vient de s'inscrire")
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->button()
                    ->label('Voir le profil')
                    ->url(route('filament.admin.resources.users.edit', $this->newUser))
            ]);
    }
}
