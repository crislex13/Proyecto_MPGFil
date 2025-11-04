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
    protected static ?string $heading = 'Composición Semanal (Torta)';
    protected static ?int $sort = 6;

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    protected function getData(): array
    {
        $ingTotal = 0;
        $egrTotal = 0;

        for ($i = 6; $i >= 0; $i--) {
            $dia = Carbon::today()->subDays($i)->toDateString();

            $ingTotal +=
                PlanCliente::whereDate('fecha_inicio', $dia)->sum('total') +
                VentaProducto::whereDate('fecha', $dia)->sum('total') +
                Casillero::whereDate('fecha_entrega_llave', $dia)->sum('costo_mensual') +
                Casillero::whereDate('fecha_entrega_llave', $dia)->sum('monto_reposiciones') +
                SesionAdicional::whereDate('fecha', $dia)->sum('precio');

            $egrTotal +=
                IngresoProducto::whereDate('fecha', $dia)->sum('precio_unitario') +
                IngresoProducto::whereDate('fecha', $dia)->sum('precio_paquete') +
                PagoPersonal::whereDate('fecha', $dia)->where('pagado', 1)->sum('monto');
        }

        $utiTotal = $ingTotal - $egrTotal;

        return [
            'datasets' => [
                [
                    'label' => 'Semana',
                    'data' => [$ingTotal, $egrTotal, $utiTotal],
                    'backgroundColor' => [
                        'rgba(255, 215, 0, 0.8)', // Ingresos
                        'rgba(255, 69, 0, 0.8)',  // Egresos
                        'rgba(50, 205, 50, 0.8)', // Utilidad
                    ],
                    'borderColor' => [
                        'rgb(255, 215, 0)',
                        'rgb(255, 69, 0)',
                        'rgb(50, 205, 50)',
                    ],
                    'borderWidth' => 1,
                ]
            ],
            'labels' => ['Ingresos', 'Egresos', 'Utilidad'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut'; // o 'pie'
    }

    protected function getChartOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',        // ↓
                    'labels' => [
                        'boxWidth' => 10,          // ↓
                        'font' => ['size' => 10],  // ↓
                    ],
                ],
                'tooltip' => [
                    'bodyFont' => ['size' => 11],  // ↓
                    'titleFont' => ['size' => 11], // ↓
                ],
            ],
            'cutout' => '65%', // dona un poco más delgada
        ];
    }

    public function getColumnSpan(): int|string
    {
        return 1;
    }

    public function getExtraAttributes(): array
    {
        return ['class' => 'h-[160px] max-h-[160px] overflow-hidden']; // ↓
    }
}
