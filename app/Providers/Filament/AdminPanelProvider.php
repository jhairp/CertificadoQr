<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Auth\Login;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->brandLogo(fn () => view('filament.admin.logo'))
            ->brandLogoHeight('3.5rem')
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
                fn (): string => Blade::render('
                    <style>
                        @media (min-width: 1024px) {
                            /* Imagen de fondo anclada a la derecha */
                            body::before {
                                content: "";
                                position: fixed;
                                top: 0;
                                right: 0;
                                width: 90vw;
                                height: 100vh;
                                background: url("{{ asset(\'images/fondo-tam.webp\') }}") no-repeat center center;
                                background-size: cover;
                                border-left: 6px solid #FFCD05;
                                z-index: -1;
                            }
                            
                            /* Forzamos el contenedor principal a la izquierda */
                            .fi-simple-layout {
                                position: absolute !important;
                                top: 0 !important;
                                left: 0 !important;
                                width: 50vw !important;
                                min-height: 100vh !important;
                                display: flex !important;
                                flex-direction: column !important;
                                align-items: center !important;
                                justify-content: center !important;
                                padding: 0 2rem !important;
                                margin: 0 !important;
                                background-color: white !important;
                            }
                            
                            /* Ajustamos la caja del formulario */
                            .fi-simple-main {
                                width: 100% !important;
                                max-width: 420px !important;
                                margin: 0 !important;
                                box-shadow: none !important;
                                border: none !important;
                                background: transparent !important;
                            }
                        }
                    </style>
                ')
            )
            ->colors([
                'primary' => Color::hex('#19499C'),
                'warning' => Color::hex('#FFCD05'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                // Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
