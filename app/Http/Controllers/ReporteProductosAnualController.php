<?php

namespace App\Http\Controllers;

use App\Models\IngresoProducto;
use App\Models\VentaProducto;
use App\Models\Productos;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteProductosAnualController extends Controller
{
    public function reporteAnual()
    {
        $inicioAno = now()->startOfYear()->startOfDay();
        $finAno    = now()->endOfYear()->endOfDay();

        // Base
        $ingresos = IngresoProducto::with('producto')
            ->whereBetween('fecha', [$inicioAno, $finAno])
            ->get();

        $ventas = VentaProducto::with('detalles.producto')
            ->whereBetween('fecha', [$inicioAno, $finAno])
            ->get();

        $detalleVentas = $ventas->flatMap->detalles;

        // ===== Control de stock (tu método) =====
        $controlStock = $this->calcularControlStock($ingresos, $detalleVentas);

        // ===== Totales y stockResumen (tu método) =====
        $totales = $this->calcularTotales($ingresos, $ventas);
        // $totales = [
        //   'productosIngresados','productosVendidos','stockResumen','totalesPorProducto','totalQR','totalEfectivo','totalGeneral'
        // ]

        // ===== KPIs Ingresos =====
        $registrosIngreso   = $ingresos->count();
        $unidadesIngreso    = (int) $ingresos->sum('cantidad_unidades');
        $paquetesIngreso    = (int) $ingresos->sum('cantidad_paquetes');
        $totalCostoIngresos = $ingresos->sum(fn($i) =>
            ($i->cantidad_unidades ?? 0) * ($i->precio_unitario ?? 0) +
            ($i->cantidad_paquetes ?? 0) * ($i->precio_paquete ?? 0)
        );
        $ticketPromIngreso  = $registrosIngreso > 0 ? round($totalCostoIngresos / $registrosIngreso, 2) : 0.0;

        // Top por costo de ingreso (para barras)
        $porProductoIngreso = $ingresos
            ->groupBy(fn($i) => $i->producto?->nombre ?? '—')
            ->map(function ($items, $nombre) {
                $unid = (int) $items->sum('cantidad_unidades');
                $paq  = (int) $items->sum('cantidad_paquetes');
                $costo= $items->sum(fn($i) =>
                    ($i->cantidad_unidades ?? 0) * ($i->precio_unitario ?? 0) +
                    ($i->cantidad_paquetes ?? 0) * ($i->precio_paquete ?? 0)
                );
                return (object)[
                    'producto'    => $nombre,
                    'unidades'    => $unid,
                    'paquetes'    => $paq,
                    'costo_total' => (float) $costo,
                ];
            })
            ->values()
            ->sortByDesc('costo_total');

        $maxCostoIngreso = max($porProductoIngreso->max('costo_total') ?? 1, 1);
        $top5Ingreso     = $porProductoIngreso->take(5)->map(function ($r) use ($maxCostoIngreso) {
            $r->pct_bar = $maxCostoIngreso ? round(($r->costo_total / $maxCostoIngreso) * 100) : 0;
            return $r;
        });

        // ===== KPIs Ventas =====
        $registrosVentas = $detalleVentas->count();
        $totalVentas     = (float) $ventas->sum('total');
        $totalQR         = (float) $ventas->where('metodo_pago', 'qr')->sum('total');
        $totalEfectivo   = (float) $ventas->where('metodo_pago', 'efectivo')->sum('total');
        $ticketPromVenta = $registrosVentas > 0 ? round($totalVentas / $registrosVentas, 2) : 0.0;

        // Top por monto vendido (para barras)
        $porProductoVenta = $detalleVentas
            ->groupBy(fn($d) => $d->producto?->nombre ?? '—')
            ->map(function ($items, $nombre) {
                return (object)[
                    'producto' => $nombre,
                    'cantidad' => (int) $items->sum('cantidad'),
                    'monto'    => (float) $items->sum('subtotal'),
                ];
            })
            ->values()
            ->sortByDesc('monto');

        $maxMontoVenta     = max($porProductoVenta->max('monto') ?? 1, 1);
        $top5Venta         = $porProductoVenta->take(5)->map(function ($r) use ($maxMontoVenta) {
            $r->pct_bar = $maxMontoVenta ? round(($r->monto / $maxMontoVenta) * 100) : 0;
            return $r;
        });

        // Producto más vendido (por monto) — card
        $productoMasVendido = $porProductoVenta->first(); // si quieres por cantidad, cambia sort

        // ===== Armar data para el blade con barras =====
        $data = [
            'titulo'       => 'REPORTE ANUAL DE PRODUCTOS',
            'periodo'      => now()->format('Y'),

            // Listados base (si los sigues usando en tablas detalladas)
            'ingresos'     => $ingresos,
            'ventas'       => $ventas,
            'detalleVentas'=> $detalleVentas,
            'controlStock' => $controlStock,

            // Totales (de tu método)
            ...$totales, // incluye: stockResumen, totalesPorProducto, totalQR, totalEfectivo, totalGeneral, etc.

            // KPIs Ingreso
            'registrosIngreso'   => $registrosIngreso,
            'unidadesIngreso'    => $unidadesIngreso,
            'paquetesIngreso'    => $paquetesIngreso,
            'totalCostoIngresos' => $totalCostoIngresos,
            'ticketPromIngreso'  => $ticketPromIngreso,

            // KPIs Venta
            'registrosVentas'    => $registrosVentas,
            'totalVentas'        => $totalVentas,
            'ticketPromVenta'    => $ticketPromVenta,
            'totalQR_kpi'        => $totalQR,       // ojo: ya tienes totalQR en $totales; esto es solo por claridad visual de KPIs
            'totalEfectivo_kpi'  => $totalEfectivo, // idem

            // Tops con barras
            'top5Ingreso'        => $top5Ingreso,
            'top5Venta'          => $top5Venta,
            'productoMasVendido' => $productoMasVendido,
        ];

        return Pdf::loadView('pdf.reporte-productos-anual-kpi', $data)
            ->setPaper('A4', 'portrait')
            ->stream('reporte-productos-anual.pdf');
    }

    private function calcularTotales($ingresos, $ventas)
    {
        $productos = Productos::all();
        $productosIngresados = collect();
        $productosVendidos   = collect();
        $stockResumen = [];

        foreach ($productos as $producto) {
            $ingresosCantidad = (int) $ingresos->where('producto_id', $producto->id)->sum('cantidad_unidades');
            $ventasCantidad   = (int) $ventas->flatMap(function ($venta) {
                return $venta->detalles;
            })->where('producto_id', $producto->id)->sum('cantidad');

            $productosIngresados[$producto->id] = [
                'nombre'   => $producto->nombre,
                'cantidad' => $ingresosCantidad,
            ];

            $productosVendidos[$producto->id] = [
                'nombre'   => $producto->nombre,
                'cantidad' => $ventasCantidad,
            ];

            $stockResumen[$producto->nombre] = [
                'stock_inicial' => (int) ($producto->stock_unidades + $ventasCantidad - $ingresosCantidad),
                'ingresos'      => $ingresosCantidad,
                'ventas'        => $ventasCantidad,
                'stock_final'   => (int) $producto->stock_unidades,
            ];
        }

        $totalQR       = (float) $ventas->where('metodo_pago', 'qr')->sum('total');
        $totalEfectivo = (float) $ventas->where('metodo_pago', 'efectivo')->sum('total');
        $totalGeneral  = (float) $ventas->sum('total');

        $totalesPorProducto = $productosVendidos->mapWithKeys(function ($item) {
            // Si no tienes monto por producto aquí, queda como multiplicador 1 (contador)
            return [$item['nombre'] => (float) $item['cantidad'] * 1];
        });

        return compact(
            'productosIngresados',
            'productosVendidos',
            'stockResumen',
            'totalesPorProducto',
            'totalQR',
            'totalEfectivo',
            'totalGeneral'
        );
    }

    private function calcularControlStock($ingresos, $detalleVentas)
    {
        $resumen = [];

        foreach ($ingresos as $ingreso) {
            $nombre = $ingreso->producto->nombre ?? 'Desconocido';
            $resumen[$nombre]['ingresado'] = ($resumen[$nombre]['ingresado'] ?? 0) + (int) $ingreso->cantidad_unidades;
        }

        foreach ($detalleVentas as $detalle) {
            $nombre = $detalle->producto->nombre ?? 'Desconocido';
            $resumen[$nombre]['vendido'] = ($resumen[$nombre]['vendido'] ?? 0) + (int) $detalle->cantidad;
        }

        foreach ($resumen as $nombre => &$datos) {
            $producto    = Productos::where('nombre', $nombre)->first();
            $stockFinal  = (int) ($producto?->stock_unidades ?? 0);
            $ingresado   = (int) ($datos['ingresado'] ?? 0);
            $vendido     = (int) ($datos['vendido'] ?? 0);

            $datos['final']   = $stockFinal;
            $datos['inicial'] = $stockFinal - $ingresado + $vendido;
        }

        return $resumen;
    }
}
