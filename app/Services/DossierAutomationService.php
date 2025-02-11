<?php

namespace App\Services;

use App\Models\Dossier;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\DossierRetardMail;
use Carbon\Carbon;

class DossierAutomationService
{
    /**
     * Vérifie les dossiers en retard et envoie les notifications
     */
    public function checkDelayedDossiers()
    {
        // Récupère les dossiers en attente de document depuis plus de 7 jours
        $delayedDossiers = Dossier::where('current_status', 'attente_document')
            ->where('status_updated_at', '<=', now()->subDays(7))
            ->whereNull('delay_notified_at')
            ->with(['client', 'assignedTo'])
            ->get();

        foreach ($delayedDossiers as $dossier) {
            $this->handleDelayedDossier($dossier);
        }
    }

    /**
     * Traite un dossier en retard
     */
    private function handleDelayedDossier(Dossier $dossier)
    {
        // Envoie l'email au client
        Mail::to($dossier->client->email)
            ->send(new DossierRetardMail($dossier));

        // Crée une activité pour le manager
        $manager = User::role('manager')->first();
        
        Activity::create([
            'subject_type' => Dossier::class,
            'subject_id' => $dossier->id,
            'type' => Activity::TYPE_EMAIL,
            'description' => "Email automatique envoyé au client {$dossier->client->name} concernant le retard dans la soumission des documents. Dossier en attente depuis " . $dossier->status_updated_at->diffForHumans(),
            'user_id' => $manager->id,
            'created_by' => $manager->id,
            'completed_at' => now(),
        ]);

        // Met à jour le dossier pour marquer la notification comme envoyée
        $dossier->update([
            'delay_notified_at' => now()
        ]);
    }
}
