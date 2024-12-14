<?php

namespace App\Http\Middleware;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class FilamentAuthenticate extends Middleware
{
    protected function authenticate($request, array $guards): void
    {
        $guard = config('filament.auth.guard', 'web');
        
        // Débogage direct dans la page
        echo "<!--\n";
        echo "Debug FilamentAuthenticate:\n";
        echo "Guard: " . $guard . "\n";
        echo "Is Authenticated: " . ($this->auth->guard($guard)->check() ? 'yes' : 'no') . "\n";
        echo "-->\n";

        if (!$this->auth->guard($guard)->check()) {
            $this->unauthenticated($request, $guards);
            return;
        }

        $this->auth->shouldUse($guard);
        $user = $this->auth->guard($guard)->user();

        echo "<!--\n";
        echo "User found:\n";
        echo "Email: " . ($user ? $user->email : 'none') . "\n";
        echo "Is FilamentUser: " . ($user instanceof FilamentUser ? 'yes' : 'no') . "\n";
        echo "-->\n";

        if ($user instanceof FilamentUser) {
            return;
        }

        $this->unauthenticated($request, $guards);
    }

    protected function redirectTo($request): string
    {
        return route('filament.admin.auth.login');
    }
}