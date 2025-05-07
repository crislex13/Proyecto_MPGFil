<?php

namespace App\Http\Controllers;

use App\Models\IngresoProducto;
use App\Models\VentaProducto;
use App\Models\Productos;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;

class ReporteProductosMensualController extends Controller
{
    public function reporteMensual()
    {
        $inicioMes = now()->startOfMonth()->startOfDay();
        $finMes = now()->endOfMonth()->endOfDay();

        $ingresos = IngresoProducto::with('producto')
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->get();

        $ventas = VentaProducto::with('detalles.producto')
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->get();

        // 🧮 Cálculo de stock
        $productos = \App\Models\Productos::all();
        $stockResumen = [];

        foreach ($productos as $producto) {
            $ingresosCantidad = $ingresos->where('producto_id', $producto->id)->sum('cantidad_unidades');
            $ventasCantidad = $ventas->flatMap->detalles->where('producto_id', $producto->id)->sum('cantidad');

            $stockResumen[$producto->nombre] = [
                'stock_inicial' => $producto->stock_unidades + $ventasCantidad - $ingresosCantidad,
                'ingresos' => $ingresosCantidad,
                'ventas' => $ventasCantidad,
                'stock_final' => $producto->stock_unidades,
            ];
        }

        // Totales por producto y método de pago
        $totalesPorProducto = [];
        $totalQR = 0;
        $totalEfectivo = 0;
        $totalGeneral = 0;

        foreach ($ventas as $venta) {
            foreach ($venta->detalles as $detalle) {
                $nombre = $detalle->producto->nombre ?? 'Producto desconocido';
                $totalesPorProducto[$nombre] = ($totalesPorProducto[$nombre] ?? 0) + $detalle->subtotal;
            }

            $totalGeneral += $venta->total;
            $venta->metodo_pago === 'qr' ? $totalQR += $venta->total : null;
            $venta->metodo_pago === 'efectivo' ? $totalEfectivo += $venta->total : null;
        }

        // 📦 Generar el PDF
        $pdf = Pdf::loadView('pdf.reporte-productos-mensual', [
            'ingresos' => $ingresos,
            'detalleVentas' => $ventas->flatMap->detalles,
            'totalesPorProducto' => $totalesPorProducto,
            'totalQR' => $totalQR,
            'totalEfectivo' => $totalEfectivo,
            'totalGeneral' => $totalGeneral,
            'stockResumen' => $stockResumen,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('reporte-productos-mensual.pdf');
    }

}
