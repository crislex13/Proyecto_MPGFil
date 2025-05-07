<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\PlanCliente;
use App\Models\VentaProducto;
use App\Models\Casillero;
use App\Models\SesionAdicional;
use App\Models\IngresoProducto;
use App\Models\PagoPersonal;
use Filament\Widgets\ChartWidget;

class FlujoCajaSemana extends ChartWidget
{
    protected static ?string $heading = '📊 Flujo de Caja - Últimos 7 días';
    protected static ?int $sort = 5;

    protected function getData(): array
    {
        $labels = [];
        $ingresos = [];
        $egresos = [];
        $utilidades = [];

        for ($i = 6; $i >= 0; $i--) {
            $dia = Carbon::today()->subDays($i)->toDateString();
            $labels[] = Carbon::parse($dia)->format('d M');

            // Ingresos
            $totalIngresos =
                PlanCliente::whereDate('fecha_inicio', $dia)->sum('total') +
                VentaProducto::whereDate('fecha', $dia)->sum('total') +
                Casillero::whereDate('fecha_entrega_llave', $dia)->sum('costo_mensual') +
                Casillero::whereDate('fecha_entrega_llave', $dia)->sum('monto_reposiciones') +
                SesionAdicional::whereDate('fecha', $dia)->sum('precio');

            // Egresos
            $totalEgresos =
                IngresoProducto::whereDate('fecha', $dia)->sum('precio_unitario') +
                IngresoProducto::whereDate('fecha', $dia)->sum('precio_paquete') +
                PagoPersonal::whereDate('fecha', $dia)->where('pagado', 1)->sum('monto');

            $ingresos[] = $totalIngresos;
            $egresos[] = $totalEgresos;
            $utilidades[] = $totalIngresos - $totalEgresos;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Ingresos',
                    'data' => $ingresos,
                    'borderColor' => 'rgb(255, 215, 0)',
                    'backgroundColor' => 'rgba(255, 215, 0, 0.2)',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Egresos',
                    'data' => $egresos,
                    'borderColor' => 'rgb(255, 69, 0)',
                    'backgroundColor' => 'rgba(255, 69, 0, 0.2)',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Utilidad',
                    'data' => $utilidades,
                    'borderColor' => 'rgb(50, 205, 50)',
                    'backgroundColor' => 'rgba(50, 205, 50, 0.2)',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    public function getColumnSpan(): int|string
    {
        return 1;
    }
}