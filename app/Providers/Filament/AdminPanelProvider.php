<?php

namespace App\Providers\Filament;

// --- ASEGÚRATE DE TENER ESTAS IMPORTACIONES ARRIBA ---
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
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
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

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
            ->favicon(asset('images/escudo-ciac.png'))
            ->colors([
                'primary' => Color::hex('#19499C'),
                'warning' => Color::hex('#FFCD05'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
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
            ])
            // ==========================================
            // FIX SWEETALERT2: Inyectamos la librería al final del Body
            // ==========================================
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => Blade::render('
                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                    <script>
                        document.addEventListener("livewire:init", () => {
                            Livewire.on("swal", (event) => {
                                const data = event[0] || event;
                                Swal.fire({
                                    title: data.title,
                                    html: data.html,
                                    icon: data.icon,
                                    confirmButtonColor: "#19499C",
                                });
                            });
                        });
                    </script>
                ')
            );
    }
}