<?php

namespace App\Observers;

use App\Models\Prospect;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\ProspectPortalAccess;

class ProspectObserver
{
    /**
     * Gère la création automatique du compte portail lors de la création d'un prospect
     */
    public function created(Prospect $prospect): void
    {
        // Générer un mot de passe aléatoire
        $password = Str::random(10);

        // Créer le compte utilisateur
        $user = User::create([
            'name' => $prospect->full_name,
            'email' => $prospect->email,
            'password' => Hash::make($password)
        ]);

        // Assigner le rôle portail_candidat
        $user->assignRole('portail_candidat');

        // Lier l'utilisateur au prospect
        $prospect->update([
            'user_id' => $user->id
        ]);

        // Envoyer les identifiants par email
        try {
            Mail::to($prospect->email)->send(new ProspectPortalAccess($user, $password));
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas bloquer le processus
            \Log::error("Erreur lors de l'envoi du mail d'accès au portail : " . $e->getMessage());
        }
    }

    /**
     * Nettoie le compte utilisateur associé lors de la suppression d'un prospect
     */
    public function deleting(Prospect $prospect): void
    {
        if ($prospect->user) {
            $prospect->user->delete();
        }
    }
}
