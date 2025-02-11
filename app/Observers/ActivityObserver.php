<?php

namespace App\Observers;

use App\Models\Activity;
use App\Models\User;
use Filament\Notifications\Notification;

class ActivityObserver
{
    /**
     * Handle the Activity "created" event.
     */
    public function created(Activity $activity): void
    {
        // Notification pour les activités de type réunion
        if ($activity->type === Activity::TYPE_MEETING) {
            // Notifier tout le personnel
            User::query()
                ->whereNot('id', $activity->created_by)
                ->each(function (User $user) use ($activity) {
                    Notification::make()
                        ->title('Nouvelle réunion planifiée')
                        ->body("Une nouvelle réunion a été planifiée pour le " . $activity->scheduled_at->format('d/m/Y H:i'))
                        ->actions([
                            \Filament\Notifications\Actions\Action::make('voir')
                                ->url(route('filament.admin.resources.activities.edit', $activity))
                        ])
                        ->sendToDatabase($user);
                });
        }

        // Notification pour les activités de type document ou rendez-vous
        if (in_array($activity->type, [Activity::TYPE_DOCUMENT, Activity::TYPE_MEETING])) {
            // Notifier le super admin
            User::query()
                ->whereHas('roles', fn($query) => $query->where('name', 'super_admin'))
                ->each(function (User $user) use ($activity) {
                    $typeLabel = $activity->getTypeLabel();
                    $creatorName = $activity->creator?->name ?? 'Inconnu';
                    
                    Notification::make()
                        ->title("Nouvelle activité : {$typeLabel}")
                        ->body("Une nouvelle activité de type {$typeLabel} a été créée par {$creatorName}")
                        ->actions([
                            \Filament\Notifications\Actions\Action::make('voir')
                                ->url(route('filament.admin.resources.activities.edit', $activity))
                        ])
                        ->sendToDatabase($user);
                });
        }
    }
}
