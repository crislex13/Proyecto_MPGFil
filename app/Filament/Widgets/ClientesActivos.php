<?php

namespace App\Filament\Widgets;

use App\Models\PlanCliente;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Carbon;

class ClientesActivos extends BaseWidget
{
    protected function getCards(): array
    {
        $hoy = Carbon::today();
        $clientesActivos = PlanCliente::where('estado', 'vigente')
            ->where('fecha_inicio', '<=', $hoy)
            ->where('fecha_final', '>=', $hoy)
            ->distinct('cliente_id')
            ->count();

        return [
            Card::make('Clientes Activos', $clientesActivos)
                ->description('Con plan vigente hoy')
                ->descriptionIcon('heroicon-o-users')
                ->color('primary'),
        ];
    }
}