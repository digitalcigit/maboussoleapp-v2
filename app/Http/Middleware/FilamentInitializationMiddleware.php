<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class FilamentInitializationMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Si c'est la page d'initialisation, permettre l'accès
        if ($request->is('admin/system-initialization')) {
            return $next($request);
        }

        // Pour toutes les autres routes
        $hasSuperAdmin = User::whereHas('roles', function ($query) {
            $query->where('name', 'super-admin');
        })->exists();

        // Si pas de super admin et ce n'est pas la page d'initialisation
        if (! $hasSuperAdmin && ! $request->is('admin/system-initialization')) {
            return redirect()->to('/admin/system-initialization');
        }

        return $next($request);
    }
}
