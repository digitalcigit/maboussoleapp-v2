<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;

class FilamentInitializationMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Si ce n'est pas une route admin, on laisse passer
        if (! $request->is('admin/*')) {
            return $next($request);
        }

        // Si c'est la page d'initialisation, on laisse passer
        if ($request->is('admin/system-initialization')) {
            return $next($request);
        }

        try {
            // Vérifier si la table roles existe
            if (! Schema::hasTable('roles')) {
                Log::error('La table roles n\'existe pas');

                return redirect('/admin/system-initialization');
            }

            // Vérifier si un super admin existe
            $role = Role::where('name', 'super-admin')->first();

            if (! $role) {
                Log::error('Le rôle super-admin n\'existe pas');

                return redirect('/admin/system-initialization');
            }

            // Vérifier si au moins un utilisateur a le rôle super-admin
            $hasSuperAdmin = User::role('super-admin')->exists();

            if (! $hasSuperAdmin) {
                Log::error('Aucun utilisateur n\'a le rôle super-admin');

                return redirect('/admin/system-initialization');
            }

            return $next($request);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification du super admin : '.$e->getMessage());
            Log::error('Stack trace : '.$e->getTraceAsString());

            return redirect('/admin/system-initialization');
        }
    }
}
