<?php

namespace App\Http\Controllers;

use App\Models\IngresoProducto;
use App\Models\VentaProducto;
use App\Models\Productos;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;



class ReporteProductosDiaController extends Controller
{
    public function diario()
    {
        $fecha = Carbon::today();

        $ingresos = IngresoProducto::with('producto')
            ->whereDate('fecha', $fecha)
            ->get();

        $ventas = VentaProducto::with('detalles.producto')
            ->whereDate('fecha', $fecha)
            ->get();

        $productos = Productos::all();

        $controlStock = $productos->mapWithKeys(function ($producto) use ($ingresos, $ventas) {
            $id = $producto->id;

            $stockInicial = $producto->stock_unidades 
                + $ingresos->where('producto_id', $id)->sum('cantidad_unidades') 
                - $ventas->flatMap->detalles->where('producto_id', $id)->sum('cantidad');

            $ingresado = $ingresos->where('producto_id', $id)->sum('cantidad_unidades');
            $vendido = $ventas->flatMap->detalles->where('producto_id', $id)->sum('cantidad');
            $stockFinal = $stockInicial + $ingresado - $vendido;

            return [$producto->nombre => [
                'inicial' => $stockInicial,
                'ingresado' => $ingresado,
                'vendido' => $vendido,
                'final' => $stockFinal,
            ]];
        });

        $totalesPorProducto = [];
        $totalGeneral = 0;
        $totalQR = 0;
        $totalEfectivo = 0;

        foreach ($ventas as $venta) {
            foreach ($venta->detalles as $detalle) {
                $nombre = $detalle->producto->nombre ?? 'Desconocido';
                $totalesPorProducto[$nombre] = ($totalesPorProducto[$nombre] ?? 0) + $detalle->subtotal;
            }
            $totalGeneral += $venta->total;
            if ($venta->metodo_pago === 'qr') $totalQR += $venta->total;
            if ($venta->metodo_pago === 'efectivo') $totalEfectivo += $venta->total;
        }

        $pdf = Pdf::loadView('pdf.reporte-productos-diario', [
            'fecha' => $fecha,
            'ingresos' => $ingresos,
            'detalleVentas' => $ventas->flatMap->detalles,
            'controlStock' => $controlStock,
            'totalesPorProducto' => $totalesPorProducto,
            'totalGeneral' => $totalGeneral,
            'totalQR' => $totalQR,
            'totalEfectivo' => $totalEfectivo,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('Reporte_Productos_' . $fecha->format('Ymd') . '.pdf');
    }
}