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
            ->login(\App\Http\Livewire\Auth\LoginCustom::class)
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
            ->renderHook('panels::body.end', fn() => <<<HTML
    <style>
        /* Estilo global para que las tablas puedan hacer scroll horizontal */
        .fi-ta {
            overflow-x: auto !important;
        }

        .fi-ta table {
            min-width: 1000px; /* Ajusta según cuántas columnas tengas */
        }
    </style>
HTML);
    }
}