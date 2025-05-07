<?php

namespace App\Filament\Widgets;

use App\Models\PlanCliente;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\SesionAdicional;


class ResumenEstadistico extends BaseWidget
{
    protected function getCards(): array
    {
        $activos = PlanCliente::where('estado', 'vigente')->count();
        $hoy = PlanCliente::whereDate('fecha_inicio', today())->count();
        $mes = PlanCliente::whereMonth('fecha_inicio', now()->month)->count();
        $anio = PlanCliente::whereYear('fecha_inicio', now()->year)->count();

        $mesActual = SesionAdicional::whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->count();

        $añoActual = SesionAdicional::whereYear('fecha', now()->year)
            ->count();

        return [
            Card::make('Clientes Activos', $activos)
                ->description('Con plan vigente hoy')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('success'),

            Card::make('Inscripciones del Año', $anio)
                ->description('Clientes registrados este año')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('warning'),

            Card::make('Inscripciones del Mes', $mes)
                ->description('Clientes registrados este mes')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('info'),

            Card::make('Inscripciones de Hoy', $hoy)
                ->description('Clientes registrados hoy')
                ->descriptionIcon('heroicon-o-clock')
                ->color('primary'),

                Card::make('📦 Sesiones este mes', $mesActual)
                ->description('Contratadas en ' . now()->format('F'))
                ->color('info')
                ->icon('heroicon-o-calendar-days'),

            Card::make('📆 Sesiones este año', $añoActual)
                ->description('Total en ' . now()->format('Y'))
                ->color('success')
                ->icon('heroicon-o-chart-bar'),
        ];
    }
    protected function getColumns(): int
    {
        return 3;
    }
}