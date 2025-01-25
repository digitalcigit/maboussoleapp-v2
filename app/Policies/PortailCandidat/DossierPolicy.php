<?php

namespace App\Policies\PortailCandidat;

use App\Models\Dossier;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DossierPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true; // Le candidat peut voir la liste (qui sera filtrée)
    }

    public function view(User $user, Dossier $dossier): bool
    {
        return $user->prospect && $user->prospect->dossier_id === $dossier->id;
    }

    public function create(User $user): bool
    {
        return false; // Les dossiers sont créés par l'admin
    }

    public function update(User $user, Dossier $dossier): bool
    {
        return $user->prospect && $user->prospect->dossier_id === $dossier->id;
    }

    public function delete(User $user, Dossier $dossier): bool
    {
        return false; // Les candidats ne peuvent pas supprimer leur dossier
    }
}
