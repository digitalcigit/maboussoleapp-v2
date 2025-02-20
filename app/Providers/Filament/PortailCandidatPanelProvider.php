<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Http\Middleware\PortailCandidatMiddleware;
use App\Filament\PortailCandidat\Resources\DossierResource;
use App\Filament\PortailCandidat\Widgets\DossierProgressWidget;
use App\Filament\PortailCandidat\Pages\Profile;

class PortailCandidatPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('portail-candidat')
            ->path('portail-candidat')
            ->login(\App\Filament\PortailCandidat\Auth\Login::class)
            ->passwordReset()
            ->emailVerification()
            ->brandName('Ma Boussole')
            ->colors([
                'primary' => [
                    50 => '245, 240, 255',
                    100 => '235, 225, 255',
                    200 => '215, 200, 255',
                    300 => '190, 165, 255',
                    400 => '160, 130, 255',
                    500 => '102, 51, 153',   // Violet MaBoussole
                    600 => '92, 46, 138',
                    700 => '82, 41, 123',
                    800 => '71, 36, 107',
                    900 => '61, 31, 92',
                    950 => '41, 20, 61',
                ],
            ])
            ->discoverResources(in: app_path('Filament/PortailCandidat/Resources'), for: 'App\\Filament\\PortailCandidat\\Resources')
            ->discoverPages(in: app_path('Filament/PortailCandidat/Pages'), for: 'App\\Filament\\PortailCandidat\\Pages')
            ->pages([
                \App\Filament\PortailCandidat\Pages\Dashboard::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make()
            ])
            ->authGuard('web')
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full');
    }
}
