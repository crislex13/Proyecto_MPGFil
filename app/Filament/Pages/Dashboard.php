<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\Widget;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Panel de Control';
    protected static ?string $title = 'Panel de Control - MaxPowerGym';
    protected static bool $shouldRegisterNavigation = true; // ✅ ¡Esto es clave!
    protected function getHeaderWidgets(): array
    {
        return [
            // \App\Filament\Widgets\ResumenEstadistico::class,
            // \App\Filament\Widgets\FlujoCajaDiaWidget::class,
            // \App\Filament\Widgets\InstructorTopWidget::class,
            // \App\Filament\Widgets\ProductoTopWidget::class,
            // \App\Filament\Widgets\InscripcionesPorDia::class,
            // \App\Filament\Widgets\FlujoCajaSemana::class,

        ];
    }
    public function getHeaderWidgetsColumns(): int|array
    {
        return [
            'default' => 4,
            'xl' => 4,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Widgets\InscripcionesPorDia::class,
            //\App\Filament\Widgets\SesionesTotalesWidget::class,
            //\App\Filament\Widgets\FlujoCajaSemana::class,
            
        ];
    }
}