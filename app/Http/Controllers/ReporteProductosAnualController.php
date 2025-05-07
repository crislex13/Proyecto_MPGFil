<?php

namespace App\Http\Controllers;

use App\Models\IngresoProducto;
use App\Models\VentaProducto;
use App\Models\Productos;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;

class ReporteProductosAnualController extends Controller
{
    public function reporteAnual()
    {
        $inicioAno = now()->startOfYear()->startOfDay();
        $finAno = now()->endOfYear()->endOfDay();

        $ingresos = IngresoProducto::with('producto')
            ->whereBetween('fecha', [$inicioAno, $finAno])
            ->get();

        $ventas = VentaProducto::with('detalles.producto')
            ->whereBetween('fecha', [$inicioAno, $finAno])
            ->get();

        $detalleVentas = $ventas->flatMap->detalles;
        $controlStock = $this->calcularControlStock($ingresos, $detalleVentas);
        $totales = $this->calcularTotales($ingresos, $ventas);

        $pdf = Pdf::loadView('pdf.reporte-productos-anual', [
            'titulo' => 'REPORTE ANUAL DE PRODUCTOS',
            'periodo' => now()->format('Y'),
            'ingresos' => $ingresos,
            'ventas' => $ventas,
            'detalleVentas' => $detalleVentas,
            'controlStock' => $controlStock,
            ...$totales,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('reporte-productos-anual.pdf');
    }

    private function calcularTotales($ingresos, $ventas)
    {
        $productos = Productos::all();
        $productosIngresados = collect();
        $productosVendidos = collect();
        $stockResumen = [];

        foreach ($productos as $producto) {
            $ingresosCantidad = $ingresos->where('producto_id', $producto->id)->sum('cantidad_unidades');

            $ventasCantidad = $ventas->flatMap(function ($venta) {
                return $venta->detalles;
            })->where('producto_id', $producto->id)->sum('cantidad');

            $productosIngresados[$producto->id] = [
                'nombre' => $producto->nombre,
                'cantidad' => $ingresosCantidad,
            ];

            $productosVendidos[$producto->id] = [
                'nombre' => $producto->nombre,
                'cantidad' => $ventasCantidad,
            ];

            $stockResumen[$producto->nombre] = [
                'stock_inicial' => $producto->stock_unidades + $ventasCantidad - $ingresosCantidad,
                'ingresos' => $ingresosCantidad,
                'ventas' => $ventasCantidad,
                'stock_final' => $producto->stock_unidades,
            ];
        }

        $totalQR = $ventas->where('metodo_pago', 'qr')->sum('total');
        $totalEfectivo = $ventas->where('metodo_pago', 'efectivo')->sum('total');
        $totalGeneral = $ventas->sum('total');

        $totalesPorProducto = $productosVendidos->mapWithKeys(function ($item) {
            return [$item['nombre'] => $item['cantidad'] * 1]; // por si subtotal no está disponible aquí
        });

        return compact('productosIngresados', 'productosVendidos', 'stockResumen', 'totalesPorProducto', 'totalQR', 'totalEfectivo', 'totalGeneral');
    }
    private function calcularControlStock($ingresos, $detalleVentas)
    {
        $resumen = [];

        // Agrupar ingresos por producto
        foreach ($ingresos as $ingreso) {
            $nombre = $ingreso->producto->nombre ?? 'Desconocido';
            $resumen[$nombre]['ingresado'] = ($resumen[$nombre]['ingresado'] ?? 0) + $ingreso->cantidad_unidades;
        }

        // Agrupar ventas por producto
        foreach ($detalleVentas as $detalle) {
            $nombre = $detalle->producto->nombre ?? 'Desconocido';
            $resumen[$nombre]['vendido'] = ($resumen[$nombre]['vendido'] ?? 0) + $detalle->cantidad;
        }

        // Obtener stock actual y calcular stock inicial
        foreach ($resumen as $nombre => &$datos) {
            $producto = Productos::where('nombre', $nombre)->first();
            $stockFinal = $producto?->stock_unidades ?? 0;
            $ingresado = $datos['ingresado'] ?? 0;
            $vendido = $datos['vendido'] ?? 0;

            $datos['final'] = $stockFinal;
            $datos['inicial'] = $stockFinal - $ingresado + $vendido;
        }

        return $resumen;
    }
}