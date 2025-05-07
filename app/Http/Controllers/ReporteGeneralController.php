<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReporteGeneralController extends Controller
{
    public function reporteFinancieroDiario(Request $request)
    {
        $fecha = $request->input('fecha', Carbon::today()->toDateString());

        // === INSCRIPCIONES ===
        $planes = \App\Models\PlanCliente::whereDate('fecha_inicio', $fecha)->count();
        $sesiones = \App\Models\SesionAdicional::whereDate('fecha', $fecha)->count();
        $casilleros = \App\Models\Casillero::whereDate('fecha_entrega_llave', $fecha)->count();
        $repLlave = \App\Models\Casillero::whereDate('fecha_entrega_llave', $fecha)->sum('reposicion_llave'); // suponiendo booleano o cantidad
        $qrPlanes = \App\Models\PlanCliente::whereDate('fecha_inicio', $fecha)->where('metodo_pago', 'QR')->sum('total');

        // === INGRESOS POR PRODUCTOS ===
        $ventas = \App\Models\VentaProducto::with('detalles.producto')
            ->whereDate('fecha', $fecha)
            ->get();

        $ingresosProductos = [];
        $totalQR = 0;
        $totalEfectivo = 0;

        foreach ($ventas as $venta) {
            foreach ($venta->detalles as $detalle) {
                $categoria = $detalle->producto->categoria?->nombre ?? 'Sin categoría';
                $ingresosProductos[$categoria] = ($ingresosProductos[$categoria] ?? 0) + $detalle->subtotal;
            }

            if ($venta->metodo_pago === 'QR') {
                $totalQR += $venta->total;
            } else {
                $totalEfectivo += $venta->total;
            }
        }

        $ingresosArray = collect($ingresosProductos)->map(function ($monto, $categoria) {
            return ['categoria' => $categoria, 'total' => $monto];
        })->values();

        // === EGRESOS ===
        $egresos = [];

        $pagosInstructores = \App\Models\PagoPersonal::whereDate('fecha', $fecha)->sum('monto');
        $egresos[] = ['categoria' => 'Pago Instructores', 'total' => $pagosInstructores];

        $comprasProductos = \App\Models\IngresoProducto::whereDate('fecha', $fecha)
            ->get()
            ->sum(function ($ing) {
                return ($ing->cantidad_unidades * $ing->precio_unitario) + ($ing->cantidad_paquetes * $ing->precio_paquete);
            });

        $egresos[] = ['categoria' => 'Ingreso de Productos', 'total' => $comprasProductos];

        // === RESUMEN TOTAL ===
        $totalIngresos = array_sum(array_column($ingresosArray->toArray(), 'total')) +
            \App\Models\PlanCliente::whereDate('fecha_inicio', $fecha)->sum('total') +
            \App\Models\SesionAdicional::whereDate('fecha', $fecha)->sum('precio') +
            \App\Models\Casillero::whereDate('fecha_entrega_llave', $fecha)->sum('costo_mensual') +
            \App\Models\Casillero::whereDate('fecha_entrega_llave', $fecha)->sum('monto_reposiciones');

        $totalEgresos = array_sum(array_column($egresos, 'total'));

        $data = [
            'fecha' => $fecha,
            'inscripciones' => [
                'planes' => $planes,
                'sesiones' => $sesiones,
                'rep_llave' => $repLlave,
                'casilleros' => $casilleros,
                'qr' => $qrPlanes,
                'total' => $planes + $sesiones + $casilleros + $repLlave,
            ],
            'instructores' => [], // lo llenamos luego si deseás
            'ingresos' => $ingresosArray,
            'egresos' => $egresos,
            'totales' => [
                'ingresos' => $totalIngresos,
                'egresos' => $totalEgresos,
                'utilidad' => $totalIngresos - $totalEgresos,
                'qr' => $totalQR,
                'efectivo' => $totalEfectivo,
            ],
            'observaciones' => '',
        ];

        $pdf = Pdf::loadView('pdf.reporte-financiero-diario', $data)->setPaper('A4');
        return $pdf->stream('reporte_financiero_diario_' . $fecha . '.pdf');
    }

    public function reporteFinanciero(Request $request)
    {
        $tipo = $request->input('tipo', 'diario'); // diario, mensual, anual
        $fecha = Carbon::parse($request->input('fecha', Carbon::today()));

        switch ($tipo) {
            case 'mensual':
                $desde = $fecha->copy()->startOfMonth();
                $hasta = $fecha->copy()->endOfMonth();
                break;
            case 'anual':
                $desde = $fecha->copy()->startOfYear();
                $hasta = $fecha->copy()->endOfYear();
                break;
            default:
                $desde = $fecha->copy()->startOfDay();
                $hasta = $fecha->copy()->endOfDay();
                break;
        }

        // === INSCRIPCIONES ===
        $planes = \App\Models\PlanCliente::whereBetween('fecha_inicio', [$desde, $hasta])->count();
        $sesiones = \App\Models\SesionAdicional::whereBetween('fecha', [$desde, $hasta])->count();
        $casilleros = \App\Models\Casillero::whereBetween('fecha_entrega_llave', [$desde, $hasta])->count();
        $repLlave = \App\Models\Casillero::whereBetween('fecha_entrega_llave', [$desde, $hasta])->sum('reposicion_llave');
        $qrPlanes = \App\Models\PlanCliente::whereBetween('fecha_inicio', [$desde, $hasta])
            ->where('metodo_pago', 'QR')
            ->sum('total');

        // === INGRESOS POR PRODUCTOS ===
        $ventas = \App\Models\VentaProducto::with('detalles.producto')
            ->whereBetween('fecha', [$desde, $hasta])
            ->get();

        $ingresosProductos = [];
        $totalQR = 0;
        $totalEfectivo = 0;

        foreach ($ventas as $venta) {
            foreach ($venta->detalles as $detalle) {
                $categoria = $detalle->producto->categoria?->nombre ?? 'Sin categoría';
                $ingresosProductos[$categoria] = ($ingresosProductos[$categoria] ?? 0) + $detalle->subtotal;
            }

            if ($venta->metodo_pago === 'QR') {
                $totalQR += $venta->total;
            } else {
                $totalEfectivo += $venta->total;
            }
        }

        $ingresosArray = collect($ingresosProductos)->map(function ($monto, $categoria) {
            return ['categoria' => $categoria, 'total' => $monto];
        })->values();

        // === EGRESOS ===
        $egresos = [];

        $pagosInstructores = \App\Models\PagoPersonal::whereBetween('fecha', [$desde, $hasta])->sum('monto');
        $egresos[] = ['categoria' => 'Pago Instructores', 'total' => $pagosInstructores];

        $comprasProductos = \App\Models\IngresoProducto::whereBetween('fecha', [$desde, $hasta])
            ->get()
            ->sum(function ($ing) {
                return ($ing->cantidad_unidades * $ing->precio_unitario) + ($ing->cantidad_paquetes * $ing->precio_paquete);
            });

        $egresos[] = ['categoria' => 'Ingreso de Productos', 'total' => $comprasProductos];

        // === TOTALES GENERALES ===
        $totalIngresos = array_sum(array_column($ingresosArray->toArray(), 'total')) +
            \App\Models\PlanCliente::whereBetween('fecha_inicio', [$desde, $hasta])->sum('total') +
            \App\Models\SesionAdicional::whereBetween('fecha', [$desde, $hasta])->sum('precio') +
            \App\Models\Casillero::whereBetween('fecha_entrega_llave', [$desde, $hasta])->sum('costo_mensual') +
            \App\Models\Casillero::whereBetween('fecha_entrega_llave', [$desde, $hasta])->sum('monto_reposiciones');

        $totalEgresos = array_sum(array_column($egresos, 'total'));

        $data = [
            'fecha' => $fecha->toDateString(),
            'tipo' => $tipo,
            'desde' => $desde->format('Y-m-d'),
            'hasta' => $hasta->format('Y-m-d'),
            'inscripciones' => [
                'planes' => $planes,
                'sesiones' => $sesiones,
                'rep_llave' => $repLlave,
                'casilleros' => $casilleros,
                'qr' => $qrPlanes,
                'total' => $planes + $sesiones + $casilleros + $repLlave,
            ],
            'instructores' => [],
            'ingresos' => $ingresosArray,
            'egresos' => $egresos,
            'totales' => [
                'ingresos' => $totalIngresos,
                'egresos' => $totalEgresos,
                'utilidad' => $totalIngresos - $totalEgresos,
                'qr' => $totalQR,
                'efectivo' => $totalEfectivo,
            ],
            'observaciones' => '',
        ];

        $pdf = Pdf::loadView('pdf.reporte-financiero-diario', $data)->setPaper('A4');
        return $pdf->stream("reporte_financiero_{$tipo}_" . $fecha->format('Y_m_d') . '.pdf');
    }

}