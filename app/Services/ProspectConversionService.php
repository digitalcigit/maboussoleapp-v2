<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Client;
use App\Models\Prospect;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProspectConversionService
{
    /**
     * Convertit un prospect en client
     *
     * @param Prospect $prospect Le prospect à convertir
     * @param array $additionalData Données supplémentaires pour le client (passport, visa, etc.)
     * @return Client Le client créé
     * @throws AuthorizationException Si l'utilisateur n'a pas la permission
     * @throws \Exception Si le prospect n'est pas approuvé
     */
    public function convertToClient(Prospect $prospect, array $additionalData = []): Client
    {
        // Vérifier les permissions
        if (! Auth::user()->can('prospects.convert')) {
            throw new AuthorizationException('Vous n\'avez pas la permission de convertir les prospects en clients.');
        }

        // Vérifier que le prospect est approuvé
        if ($prospect->status !== Prospect::STATUS_APPROVED) {
            throw new \Exception('Seuls les prospects approuvés peuvent être convertis en clients.');
        }

        try {
            DB::beginTransaction();

            // Créer le client
            $client = Client::create([
                'prospect_id' => $prospect->id,
                'client_number' => $this->generateClientNumber(),
                'passport_number' => $additionalData['passport_number'] ?? null,
                'passport_expiry' => $additionalData['passport_expiry'] ?? null,
                'visa_status' => Client::VISA_STATUS_NOT_STARTED,
                'travel_preferences' => $additionalData['travel_preferences'] ?? null,
                'payment_status' => Client::PAYMENT_STATUS_PENDING,
                'total_amount' => $additionalData['total_amount'] ?? 0,
                'paid_amount' => $additionalData['paid_amount'] ?? 0,
            ]);

            // Mettre à jour le statut du prospect
            $prospect->update(['status' => Prospect::STATUS_CONVERTED]);

            // Créer une activité pour la conversion
            Activity::create([
                'type' => 'conversion',
                'subject_type' => Client::class,
                'subject_id' => $client->id,
                'description' => 'Prospect converti en client',
                'created_by' => Auth::id(),
                'status' => 'completed',
            ]);

            DB::commit();

            return $client;

        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Génère un numéro de client unique
     */
    private function generateClientNumber(): string
    {
        $lastClient = Client::orderBy('id', 'desc')->first();
        $nextId = $lastClient ? $lastClient->id + 1 : 1;

        return 'CLI' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }
}
