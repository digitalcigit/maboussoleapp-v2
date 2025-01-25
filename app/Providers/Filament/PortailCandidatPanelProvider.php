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

class PortailCandidatPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('portail-candidat')
            ->path('portail')
            ->login()
            // Retrait de ->registration() pour désactiver l'inscription directe
            ->passwordReset()
            ->emailVerification()
            ->profile()
            ->colors([
                'primary' => [
                    50 => '245, 240, 255',  // Très clair
                    100 => '235, 225, 255',
                    200 => '215, 200, 255',
                    300 => '190, 165, 255',
                    400 => '160, 130, 255',
                    500 => '102, 51, 153',   // Violet MaBoussole
                    600 => '92, 46, 138',
                    700 => '82, 41, 123',
                    800 => '71, 36, 107',
                    900 => '61, 31, 92',
                    950 => '41, 20, 61',    // Très foncé
                ],
                'gray' => [
                    50 => '250, 250, 250',
                    100 => '244, 244, 245',
                    200 => '228, 228, 231',
                    300 => '209, 209, 214',
                    400 => '156, 156, 163',
                    500 => '102, 102, 102',  // Gris du slogan
                    600 => '82, 82, 91',
                    700 => '63, 63, 70',
                    800 => '39, 39, 42',
                    900 => '24, 24, 27',
                    950 => '9, 9, 11',
                ],
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger' => Color::Rose,
                'info' => Color::Violet,
            ])
            ->font('Poppins')
            ->brandName('Portail Candidat - Ma Boussole')
            ->favicon(asset('images/favicon.png'))
            ->viteTheme('resources/css/filament/portail-candidat/theme.css')
            ->discoverResources(in: app_path('Filament/PortailCandidat/Resources'), for: 'App\\Filament\\PortailCandidat\\Resources')
            ->discoverPages(in: app_path('Filament/PortailCandidat/Pages'), for: 'App\\Filament\\PortailCandidat\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/PortailCandidat/Widgets'), for: 'App\\Filament\\PortailCandidat\\Widgets')
            ->widgets([
                DossierProgressWidget::class,
                Widgets\AccountWidget::class,
            ])
            ->navigationItems([
                \Filament\Navigation\NavigationItem::make('Mon Dossier')
                    ->icon('heroicon-o-folder')
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.portail-candidat.resources.mon-dossier.*'))
                    ->url(fn (): string => DossierResource::getUrl('index'))
                    ->sort(1),
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
                PortailCandidatMiddleware::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->databaseNotifications()
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->topNavigation()
            ->spa();
    }
}
