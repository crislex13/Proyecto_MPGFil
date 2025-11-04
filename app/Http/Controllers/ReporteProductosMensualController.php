<?php

namespace App\Http\Controllers;

use App\Models\IngresoProducto;
use App\Models\VentaProducto;
use App\Models\Productos;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteProductosMensualController extends Controller
{
    public function reporteMensual()
    {
        $inicioMes = now()->startOfMonth()->startOfDay();
        $finMes = now()->endOfMonth()->endOfDay();

        $ingresos = IngresoProducto::with(['producto', 'registradoPor'])
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->get();

        $ventas = \App\Models\VentaProducto::with(['detalles.producto'])
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->get();

        $detalles = $ventas->flatMap->detalles;

        // ===== Ingresos (KPIs y Top 5 por costo) =====
        $registrosIngreso = $ingresos->count();
        $unidadesIngreso = (int) $ingresos->sum('cantidad_unidades');
        $paquetesIngreso = (int) $ingresos->sum('cantidad_paquetes');
        $totalCostoIngresos = $ingresos->sum(
            fn($i) =>
            ($i->cantidad_unidades ?? 0) * ($i->precio_unitario ?? 0) +
            ($i->cantidad_paquetes ?? 0) * ($i->precio_paquete ?? 0)
        );
        $ticketPromIngreso = $registrosIngreso > 0 ? round($totalCostoIngresos / $registrosIngreso, 2) : 0.0;

        $porProductoIngreso = $ingresos
            ->groupBy(fn($i) => $i->producto?->nombre ?? '—')
            ->map(function ($items, $nombre) {
                $unid = (int) $items->sum('cantidad_unidades');
                $paq = (int) $items->sum('cantidad_paquetes');
                $costo = $items->sum(
                    fn($i) =>
                    ($i->cantidad_unidades ?? 0) * ($i->precio_unitario ?? 0) +
                    ($i->cantidad_paquetes ?? 0) * ($i->precio_paquete ?? 0)
                );
                return (object) [
                    'producto' => $nombre,
                    'unidades' => $unid,
                    'paquetes' => $paq,
                    'costo_total' => (float) $costo,
                ];
            })
            ->values()
            ->sortByDesc('costo_total');

        $maxCostoIngreso = max($porProductoIngreso->max('costo_total') ?? 1, 1);
        $top5Ingreso = $porProductoIngreso->take(5);

        // 🟧 (opcional) normaliza % de barra ya calculado:
        $top5Ingreso = $top5Ingreso->map(function ($r) use ($maxCostoIngreso) {
            $r->pct_bar = $maxCostoIngreso ? round(($r->costo_total / $maxCostoIngreso) * 100) : 0;
            return $r;
        });

        // ===== Ventas (KPIs y Top 5 por monto) =====
        $registrosVentas = $detalles->count();
        $totalVentas = (float) $ventas->sum('total');
        $totalQR = (float) $ventas->where('metodo_pago', 'qr')->sum('total');
        $totalEfectivo = (float) $ventas->where('metodo_pago', 'efectivo')->sum('total');
        $ticketPromVenta = $registrosVentas > 0 ? round($totalVentas / $registrosVentas, 2) : 0.0;

        $porProductoVenta = $detalles
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

        // 🟧 (opcional) % de barra ya listo:
        $top5Venta = $top5Venta->map(function ($r) use ($maxMontoVenta) {
            $r->pct_bar = $maxMontoVenta ? round(($r->monto / $maxMontoVenta) * 100) : 0;
            return $r;
        });

        // 🟧 Producto más vendido (por monto) — para “card” con barra al 100%
        $productoMasVendido = $porProductoVenta->first(); // puede ser null
        // Si quieres “más vendido por CANTIDAD”, cambia a ->sortByDesc('cantidad')->first()

        // ===== Stock mensual =====
        $productos = Productos::all();
        $stockResumen = [];
        foreach ($productos as $p) {
            $ing = (int) $ingresos->where('producto_id', $p->id)->sum('cantidad_unidades');
            $ven = (int) $detalles->where('producto_id', $p->id)->sum('cantidad');
            $final = (int) $p->stock_unidades;
            $inicial = $final - $ing + $ven;

            $stockResumen[$p->nombre] = [
                'stock_inicial' => $inicial,
                'ingresos' => $ing,
                'ventas' => $ven,
                'stock_final' => $final,
            ];
        }

        // Totales por producto (para tablero resumen)
        $totalesPorProducto = [];
        foreach ($detalles as $d) {
            $nombre = $d->producto->nombre ?? '—';
            $totalesPorProducto[$nombre] = ($totalesPorProducto[$nombre] ?? 0) + (float) $d->subtotal;
        }

        $data = [
            'titulo' => 'Reporte Mensual — Ingresos, Ventas y Stock',
            'periodo' => $inicioMes->isoFormat('MMMM [de] YYYY'),
            'generado_por' => optional(auth()->user())->name ?? 'Sistema',
            'generado_el' => now()->isoFormat('dddd D [de] MMMM YYYY, HH:mm'),

            // KPIs Ingresos
            'registrosIngreso' => $registrosIngreso,
            'unidadesIngreso' => $unidadesIngreso,
            'paquetesIngreso' => $paquetesIngreso,
            'totalCostoIngresos' => $totalCostoIngresos,
            'ticketPromIngreso' => $ticketPromIngreso,

            // KPIs Ventas
            'registrosVentas' => $registrosVentas,
            'totalVentas' => $totalVentas,
            'ticketPromVenta' => $ticketPromVenta,
            'totalQR' => $totalQR,
            'totalEfectivo' => $totalEfectivo,

            // Tops con % ya listo
            'top5Ingreso' => $top5Ingreso,
            'top5Venta' => $top5Venta,

            // 🟧 Producto más vendido (card)
            'productoMasVendido' => $productoMasVendido,

            // Listados
            'ingresos' => $ingresos,
            'detalleVentas' => $detalles,
            'stockResumen' => $stockResumen,
            'totalesPorProducto' => $totalesPorProducto,
        ];

        return Pdf::loadView('pdf.reporte-productos-mensual-kpi', $data)
            ->setPaper('A4', 'portrait')
            ->stream('reporte-productos-mensual.pdf');
    }
}
