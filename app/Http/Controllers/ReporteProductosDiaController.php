<?php

namespace App\Http\Controllers;

use App\Models\IngresoProducto;
use App\Models\VentaProducto;
use App\Models\Productos;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReporteProductosDiaController extends Controller
{
    public function diario()
    {
        $hoy = Carbon::today(); // zona por defecto de tu app

        // Carga base
        $ingresos = IngresoProducto::with(['producto', 'registradoPor'])
            ->whereDate('fecha', $hoy)
            ->get();

        $ventas = VentaProducto::with(['detalles.producto'])
            ->whereDate('fecha', $hoy)
            ->get();

        // === KPIs generales del día ===
        // Subtotal ingreso = (unidades*P.U) + (paquetes*P.Pack)
        $totalCostoIngresos = $ingresos->sum(
            fn($i) =>
            ($i->cantidad_unidades ?? 0) * ($i->precio_unitario ?? 0)
            + ($i->cantidad_paquetes ?? 0) * ($i->precio_paquete ?? 0)
        );

        $registrosIngreso = $ingresos->count();
        $unidadesIngreso = (int) $ingresos->sum('cantidad_unidades');
        $paquetesIngreso = (int) $ingresos->sum('cantidad_paquetes');

        $detallesVentas = $ventas->flatMap->detalles;
        $registrosVentas = $detallesVentas->count(); // nivel detalle
        $totalVentas = (float) $ventas->sum('total');

        $totalQR = (float) $ventas->where('metodo_pago', 'qr')->sum('total');
        $totalEfectivo = (float) $ventas->where('metodo_pago', 'efectivo')->sum('total');

        $ticketPromIngreso = $registrosIngreso > 0 ? round($totalCostoIngresos / $registrosIngreso, 2) : 0.0;
        $ticketPromVenta = $registrosVentas > 0 ? round($totalVentas / $registrosVentas, 2) : 0.0;

        // === Top 5 por costo de ingreso (para barras) ===
        $porProductoIngreso = $ingresos
            ->groupBy(fn($i) => $i->producto?->nombre ?? '—')
            ->map(function ($items, $nombre) {
                $unid = (int) $items->sum('cantidad_unidades');
                $paq = (int) $items->sum('cantidad_paquetes');
                $costo = $items->sum(
                    fn($i) =>
                    ($i->cantidad_unidades ?? 0) * ($i->precio_unitario ?? 0)
                    + ($i->cantidad_paquetes ?? 0) * ($i->precio_paquete ?? 0)
                );
                return (object) [
                    'producto' => $nombre,
                    'unidades' => $unid,
                    'paquetes' => $paq,
                    'costo_total' => $costo,
                ];
            })
            ->values()
            ->sortByDesc('costo_total');

        $maxCostoIngreso = max($porProductoIngreso->max('costo_total') ?? 1, 1);
        $top5Ingreso = $porProductoIngreso->take(5);

        // === Top 5 por monto vendido (detalle) ===
        $porProductoVenta = $detallesVentas
            ->groupBy(fn($d) => $d->producto?->nombre ?? '—')
            ->map(function ($items, $nombre) {
                return (object) [
                    'producto' => $nombre,
                    'cantidad' => (int) $items->sum('cantidad'),
                    'monto' => (float) $items->sum('subtotal'),
                ];
            })
            ->values()
            ->sortByDesc('monto');

        $maxMontoVenta = max($porProductoVenta->max('monto') ?? 1, 1);
        $top5Venta = $porProductoVenta->take(5);

        // === Control de stock (inicial/ingresado/vendido/final) ===
        // Stock final real = $producto->stock_unidades
        // => inicial = final - ingresado + vendido
        $productos = Productos::with([])->get();
        $controlStock = $productos->mapWithKeys(function ($p) use ($ingresos, $detallesVentas) {
            $id = $p->id;
            $ing = (int) $ingresos->where('producto_id', $id)->sum('cantidad_unidades');
            $ven = (int) $detallesVentas->where('producto_id', $id)->sum('cantidad');
            $final = (int) $p->stock_unidades; // accessor tuyo
            $inicial = $final - $ing + $ven;

            return [
                $p->nombre => [
                    'inicial' => $inicial,
                    'ingresado' => $ing,
                    'vendido' => $ven,
                    'final' => $final,
                ]
            ];
        });

        // === Totales por producto vendido (para “Resumen del día”) ===
        $totalesPorProducto = [];
        foreach ($detallesVentas as $d) {
            $nombre = $d->producto->nombre ?? '—';
            $totalesPorProducto[$nombre] = ($totalesPorProducto[$nombre] ?? 0) + (float) $d->subtotal;
        }

        // Payload para el PDF (mismo estilo que tus otros reportes con barras/KPIs)
        $data = [
            'titulo' => 'Reporte Diario — Ingresos y Ventas de Productos',
            'periodo' => $hoy->isoFormat('dddd D [de] MMMM YYYY'),
            'generado_por' => optional(auth()->user())->name ?? 'Sistema',
            'generado_el' => now()->isoFormat('dddd D [de] MMMM YYYY, HH:mm'),

            'registrosIngreso' => $registrosIngreso,
            'unidadesIngreso' => $unidadesIngreso,
            'paquetesIngreso' => $paquetesIngreso,
            'totalCostoIngresos' => $totalCostoIngresos,
            'ticketPromIngreso' => $ticketPromIngreso,

            'registrosVentas' => $registrosVentas,
            'totalVentas' => $totalVentas,
            'ticketPromVenta' => $ticketPromVenta,
            'totalQR' => $totalQR,
            'totalEfectivo' => $totalEfectivo,

            'top5Ingreso' => $top5Ingreso,
            'maxCostoIngreso' => $maxCostoIngreso,
            'top5Venta' => $top5Venta,
            'maxMontoVenta' => $maxMontoVenta,

            'ingresos' => $ingresos,
            'detalleVentas' => $detallesVentas,
            'controlStock' => $controlStock,
            'totalesPorProducto' => $totalesPorProducto,
        ];

        return Pdf::loadView('pdf.reporte-productos-diario-kpi', $data)
            ->setPaper('A4', 'portrait')
            ->stream('reporte-productos-diario.pdf');
    }
}
