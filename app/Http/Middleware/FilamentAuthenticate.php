<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class FilamentAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        // Vérifier si un super admin existe
        $hasSuperAdmin = User::whereHas('roles', function ($query) {
            $query->where('name', 'super_admin');
        })->exists();

        // Si pas de super admin, rediriger vers la page d'initialisation
        if (! $hasSuperAdmin) {
            return redirect()->route('system.initialization');
        }

        return $next($request);
    }
}
