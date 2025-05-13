<?php

namespace App\Filament\Widgets;

use App\Models\Casillero;
use App\Models\PlanCliente;
use App\Models\SesionAdicional;
use App\Models\VentaProducto;
use App\Models\IngresoProducto;
use App\Models\PagoPersonal;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class FlujoCajaDiaWidget extends StatsOverviewWidget
{
    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    protected static ?int $sort = 4;

    protected function getCards(): array
    {
        $hoy = Carbon::today()->toDateString();

        $ingresosCasilleros = Casillero::whereDate('fecha_entrega_llave', $hoy)
            ->sum('costo_mensual') + Casillero::whereDate('fecha_entrega_llave', $hoy)->sum('monto_reposiciones');

        $ingresosPlanes = PlanCliente::whereDate('fecha_inicio', $hoy)->sum('total');

        $ingresosSesiones = SesionAdicional::whereDate('fecha', $hoy)->sum('precio');

        $ingresosVentas = VentaProducto::whereDate('fecha', $hoy)->sum('total');

        $totalIngresos = $ingresosCasilleros + $ingresosPlanes + $ingresosSesiones + $ingresosVentas;

        $egresosProductos = IngresoProducto::whereDate('fecha', $hoy)
            ->sum('precio_unitario') + IngresoProducto::whereDate('fecha', $hoy)->sum('precio_paquete');

        $egresosPagos = PagoPersonal::whereDate('fecha', $hoy)->where('pagado', 1)->sum('monto');

        $totalEgresos = $egresosProductos + $egresosPagos;

        return [
            Card::make('Ingresos del día', number_format($totalIngresos, 2) . ' Bs')
                ->description('Ventas, sesiones, planes, casilleros')
                ->color('success')
                ->icon('heroicon-o-currency-dollar'),

            Card::make('Egresos del día', number_format($totalEgresos, 2) . ' Bs')
                ->description('Pagos a personal y productos')
                ->color('danger')
                ->icon('heroicon-o-arrow-trending-down'),

            Card::make('Utilidad del día', number_format($totalIngresos - $totalEgresos, 2) . ' Bs')
                ->description('Balance neto del día')
                ->color('primary')
                ->icon('heroicon-o-banknotes'),
        ];
    }

}