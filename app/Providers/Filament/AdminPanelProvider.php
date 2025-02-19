<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Dashboard;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Navigation\MenuItem;
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

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->viteTheme('resources/css/filament/admin/theme.css')
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
                'gray' => [
                    50 => '250, 250, 250',
                    100 => '244, 244, 245',
                    200 => '228, 228, 231',
                    300 => '209, 209, 214',
                    400 => '156, 156, 163',
                    500 => '102, 102, 102',
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
            ->brandName('Administration - Ma Boussole')
            ->favicon(asset('images/favicon.png'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Le widget de compte est maintenant géré dans le Dashboard
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
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->default()
            ->databaseNotifications(true)
            ->renderHook(
                'panels::scripts.start',
                fn (): string => '<script>window.filamentData = {};</script>'
            );
    }
}
