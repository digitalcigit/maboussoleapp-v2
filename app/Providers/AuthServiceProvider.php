<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Filament\PortailCandidat\Auth\PortailCandidatAuth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Dossier::class => \App\Policies\DossierPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function ($user, $ability) {
            file_put_contents(
                storage_path('logs/debug.log'),
                "Gate::before called for ability: {$ability}\n",
                FILE_APPEND
            );
            Log::debug('Gate check', [
                'ability' => $ability,
                'user_id' => $user->id,
                'roles' => $user->roles->pluck('name')
            ]);
        });

        Gate::define('portail-candidat.dossier.viewAny', function ($user) {
            file_put_contents(
                storage_path('logs/debug.log'),
                "Gate viewAny called\n",
                FILE_APPEND
            );
            Log::debug('portail-candidat.dossier.viewAny check', [
                'user_id' => $user->id,
                'has_role_candidat' => $user->hasRole('portail_candidat')
            ]);
            return $user->hasRole('portail_candidat');
        });

        Gate::define('portail-candidat.dossier.view', function ($user, $dossier) {
            file_put_contents(
                storage_path('logs/debug.log'),
                "Gate view called for dossier {$dossier->id}\n",
                FILE_APPEND
            );
            $hasRole = $user->hasRole('portail_candidat');
            $hasDossier = $user->prospect && $dossier->prospect_id === $user->prospect->id;
            
            Log::debug('portail-candidat.dossier.view check', [
                'user_id' => $user->id,
                'dossier_id' => $dossier->id,
                'has_role_candidat' => $hasRole,
                'has_prospect' => isset($user->prospect),
                'prospect_id' => $user->prospect?->id,
                'dossier_prospect_id' => $dossier->prospect_id,
                'has_dossier' => $hasDossier
            ]);
            
            return $hasRole && $hasDossier;
        });

        Gate::define('portail-candidat.dossier.update', function ($user, $dossier) {
            $hasRole = $user->hasRole('portail_candidat');
            $hasDossier = $user->prospect && $dossier->prospect_id === $user->prospect->id;
            
            Log::debug('portail-candidat.dossier.update check', [
                'user_id' => $user->id,
                'dossier_id' => $dossier->id,
                'has_role_candidat' => $hasRole,
                'has_prospect' => isset($user->prospect),
                'prospect_id' => $user->prospect?->id,
                'dossier_prospect_id' => $dossier->prospect_id,
                'has_dossier' => $hasDossier
            ]);
            
            return $hasRole && $hasDossier;
        });

        Gate::define('portail-candidat.dossier.create', [\App\Policies\PortailCandidat\DossierPolicy::class, 'create']);
        Gate::define('portail-candidat.dossier.delete', [\App\Policies\PortailCandidat\DossierPolicy::class, 'delete']);

        Auth::extend('portail_candidat', function ($app, $name, array $config) {
            return new PortailCandidatAuth();
        });
    }
}
