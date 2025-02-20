<?php

namespace App\Filament\Candidat\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    protected static string $view = 'filament.candidat.pages.auth.login';

    public static function getSlug(): string
    {
        return 'login';
    }

    protected function getHeading(): string
    {
        return __('Portail Candidat');
    }
}
