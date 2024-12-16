<?php

namespace App\Http\Middleware;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class FilamentAuthenticate extends Middleware
{
    protected function authenticate($request, array $guards): void
    {
        $guard = config('filament.auth.guard', 'web');

        if (!$this->auth->guard($guard)->check()) {
            $this->unauthenticated($request, $guards);
            return;
        }

        $this->auth->shouldUse($guard);
        $user = $this->auth->guard($guard)->user();

        if (! $user instanceof FilamentUser) {
            $this->unauthenticated($request, $guards);
            return;
        }
    }

    protected function redirectTo($request): string
    {
        return route('filament.admin.auth.login');
    }
}