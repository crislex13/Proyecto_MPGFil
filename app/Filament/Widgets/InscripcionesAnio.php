<?php

namespace App\Filament\Widgets;

use App\Models\PlanCliente;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Carbon;

class InscripcionesAnio extends BaseWidget
{
    protected function getCards(): array
    {
        $inscripcionesAnio = PlanCliente::whereYear('fecha_inicio', now()->year)->count();

        return [
            Card::make('Inscripciones del Año', $inscripcionesAnio)
                ->description('Clientes registrados este año')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('warning'),
        ];
    }
}