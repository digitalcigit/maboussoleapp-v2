<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Filament\Notifications\Notification;

class PortailCandidatMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->hasRole('portail_candidat')) {
            if ($request->is('portail-candidat/login') || $request->is('portail-candidat/password-reset')) {
                return $next($request);
            }

            Notification::make()
                ->title('Accès non autorisé')
                ->body('Vous n\'avez pas les permissions nécessaires pour accéder au portail candidat.')
                ->danger()
                ->send();

            return redirect('/portail-candidat/login');
        }

        return $next($request);
    }
}
