<?php

namespace App\Observers;

use App\Models\Activity;
use App\Models\Client;
use App\Models\Prospect;
use Illuminate\Support\Facades\Auth;

class ClientObserver
{
    /**
     * Handle the Client "created" event.
     */
    public function created(Client $client): void
    {
        // Mettre à jour le statut du prospect
        if ($client->prospect_id) {
            $prospect = Prospect::find($client->prospect_id);
            if ($prospect) {
                $prospect->update(['status' => Prospect::STATUS_CONVERTED]);

                // Créer une activité de conversion
                Activity::create([
                    'subject_type' => Client::class,
                    'subject_id' => $client->id,
                    'title' => 'Conversion en client',
                    'description' => 'Prospect converti en client',
                    'type' => Activity::TYPE_CONVERSION,
                    'status' => Activity::STATUS_COMPLETED,
                    'user_id' => Auth::id(),
                ]);

                // Mettre à jour les activités du prospect pour les lier au client
                Activity::where('subject_type', Prospect::class)
                    ->where('subject_id', $prospect->id)
                    ->update([
                        'subject_type' => Client::class,
                        'subject_id' => $client->id,
                    ]);
            }
        }
    }
}
