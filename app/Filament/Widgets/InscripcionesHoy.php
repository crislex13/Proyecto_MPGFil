<?php

namespace App\Filament\Widgets;

use App\Models\PlanCliente;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Carbon;

class InscripcionesHoy extends BaseWidget
{
    protected function getCards(): array
    {
        $hoy = Carbon::today();

        $inscripcionesHoy = PlanCliente::whereDate('fecha_inicio', $hoy)->count();

        return [
            Card::make('Inscripciones de Hoy', $inscripcionesHoy)
                ->description('Clientes registrados hoy')
                ->descriptionIcon('heroicon-o-user-plus')
                ->color('success'),
        ];
    }
}