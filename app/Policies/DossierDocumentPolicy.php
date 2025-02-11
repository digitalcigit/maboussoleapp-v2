<?php

namespace App\Policies;

use App\Models\DossierDocument;
use App\Models\User;

class DossierDocumentPolicy
{
    /**
     * Determine if the user can view the document.
     */
    public function view(User $user, DossierDocument $document): bool
    {
        // Les super admins peuvent tout voir
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Les managers peuvent voir les documents des dossiers qu'ils gèrent
        if ($user->hasRole('manager')) {
            return $document->dossier->manager_id === $user->id;
        }

        // Les clients peuvent voir leurs propres documents
        if ($user->hasRole('client')) {
            return $document->dossier->client_id === $user->id;
        }

        return false;
    }

    /**
     * Determine if the user can download the document.
     */
    public function download(User $user, DossierDocument $document): bool
    {
        return $this->view($user, $document);
    }
}
