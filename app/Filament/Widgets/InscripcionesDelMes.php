<?php

namespace App\Filament\Widgets;

use App\Models\PlanCliente;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Carbon;

class InscripcionesDelMes extends BaseWidget
{
    protected function getCards(): array
    {
        $mesActual = Carbon::now()->month;
        $añoActual = Carbon::now()->year;

        $inscripcionesMes = PlanCliente::whereMonth('fecha_inicio', $mesActual)
            ->whereYear('fecha_inicio', $añoActual)
            ->count();

        return [
            Card::make('Inscripciones del Mes', $inscripcionesMes)
                ->description('Clientes registrados este mes')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('info'),
        ];
    }
}
