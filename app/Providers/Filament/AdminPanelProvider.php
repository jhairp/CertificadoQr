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
                'primary' => Color::hex('#19499C'), // Azul Principal TAM
                'warning' => Color::hex('#FFCD05'), // Amarillo TAM
                
                // Mapeamos los grises del sistema para que coincidan con el Login (#090d16)
                'gray' => [
                    50 => '#f8fafc',
                    100 => '#f1f5f9',
                    200 => '#e2e8f0',
                    300 => '#cbd5e1',
                    400 => '#94a3b8',
                    500 => '#64748b',
                    600 => '#475569',
                    700 => '#334155',
                    800 => '#1e293b',
                    900 => '#111827', // Modo Oscuro: Fondo de Tarjetas, Tablas y Widgets
                    950 => '#090d16', // Modo Oscuro: Fondo General de la Pantalla
                ],
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
            // 1. INYECTAMOS SWEETALERT2
            // ==========================================
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => Blade::render('
                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                    <script>
                        document.addEventListener("livewire:init", () => {
                            Livewire.on("swal", (event) => {
                                const data = event[0] || event;
                                const isDark = document.documentElement.classList.contains("dark");
                                
                                Swal.fire({
                                    title: data.title,
                                    html: data.html,
                                    icon: data.icon,
                                    confirmButtonColor: "#19499C",
                                    background: isDark ? "#111827" : "#ffffff",
                                    color: isDark ? "#ffffff" : "#374151",
                                });
                            });
                        });
                    </script>
                ')
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => Blade::render('
                    <style>
                        /* FIX: Forzar la barra superior por encima de los botones */
                        .fi-topbar { z-index: 60 !important; }
                        
                        /* ==========================================
                           SIDEBAR: Fondo nativo con línea divisoria
                           ========================================== */
                        .fi-sidebar { 
                            box-shadow: 2px 0 10px rgba(0,0,0,0.03); 
                            border-right: 1px solid #e2e8f0 !important; 
                        }
                        
                        .dark .fi-sidebar { 
                            background-color: #111827 !important; /* Fondo nativo oscuro */
                            border-right: 1px solid #1f2937 !important; /* Línea divisoria sutil */
                        }
                        
                        /* ==========================================
                           FIX: Tabla custom de Reportes
                           ========================================== */
                        .dark .tabla-excel th { background-color: #111827 !important; border-color: #374151 !important; }
                        .dark .tabla-excel td { border-color: #374151 !important; }
                        .dark .fila-par { background-color: #090d16 !important; }
                        .dark .fila-impar { background-color: #111827 !important; }
                    </style>
                ')
            );
    }
}