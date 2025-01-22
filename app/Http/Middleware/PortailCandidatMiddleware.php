<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PortailCandidatMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur est authentifié
        if (!auth()->check()) {
            return redirect()->route('portail.login');
        }

        // Vérifier si l'utilisateur a le rôle 'portail_candidat'
        if (!auth()->user()->hasRole('portail_candidat')) {
            // Log la tentative d'accès non autorisée
            \Illuminate\Support\Facades\Log::warning('Tentative d\'accès non autorisé au portail candidat', [
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
            ]);
            
            // Rediriger vers la page d'accueil avec un message d'erreur
            return redirect()->route('home')->with('error', 'Accès non autorisé au portail candidat.');
        }

        return $next($request);
    }
}
