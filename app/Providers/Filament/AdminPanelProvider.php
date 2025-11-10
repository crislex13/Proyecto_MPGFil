<?php

namespace App\Providers\Filament;

use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
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
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Actions\Auth\CustomLogoutResponse;
use Filament\Navigation\NavigationItem;
use Filament\Navigation\NavigationGroup;
use App\Http\Livewire\Auth\LoginCustom;


class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->maxContentWidth('full')
            ->renderHook('panels::body.start', fn() => '<style>.fi-main { padding-left: 1rem; padding-right: 1rem; }</style>')
            ->id('admin')
            ->path('admin')
            ->login(LoginCustom::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->brandName('MAXPOWERGYM')
            ->brandLogo(fn() => asset('storage/LogosMPG/Recurso 3.png'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            //->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->pages([
                \App\Filament\Pages\InstructorDashboard::class,
                \App\Filament\Pages\ClienteDashboard::class,
                \App\Filament\Pages\KioskAsistencias::class,
            ])
            ->widgets(
                []
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                \App\Http\Middleware\RedireccionPorRol::class,
                DispatchServingFilamentEvent::class,

            ])
            ->authMiddleware([
                Authenticate::class,
                \App\Http\Middleware\ValidarAccesoPorConfiguracion::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->navigationItems([
                NavigationItem::make('Kiosco de Asistencias')
                    ->icon('heroicon-o-rectangle-stack')
                    ->group('Control de Accesos')
                    ->url(fn() => url('/admin/kiosk'))   // <-- usa tu slug real
                    ->openUrlInNewTab()                   // <-- abrir en otra pestaña
                    ->sort(10)
                    ->visible(fn() => auth()->user()?->can('view_any_asistencia')
                        || auth()->user()?->hasRole('recepcionista')),
            ])
            ->renderHook('panels::body.end', function () {
                // CSS global que ya tenías (scroll horizontal en tablas)
                $global = <<<HTML
<style>
  /* Estilo global para que las tablas puedan hacer scroll horizontal */
  .fi-ta { overflow-x: auto !important; }
  .fi-ta table { min-width: 1000px; }
</style>
HTML;

                // CSS SOLO para la página /admin/kiosk (ocultar sidebar/topbar y expandir layout)
                $kiosk = '';
                if (request()->is('admin/kiosk')) {
                    $kiosk = <<<HTML
<style>
  .fi-sidebar,
  .fi-topbar,
  .fi-sidebar-header,
  .fi-header,
  [data-sidebar],
  [data-topbar] { display: none !important; }

  .fi-main, .fi-body, .fi-content {
    margin: 0 !important;
    padding-left: 0 !important;
    grid-template-columns: 1fr !important;
    width: 100% !important;
  }

  .fi-layout, .fi-main > div { max-width: 100% !important; }
  .fi-main .fi-page { padding: 0 !important; }
</style>
HTML;
                }

                return $global . $kiosk;
            });
    }
}