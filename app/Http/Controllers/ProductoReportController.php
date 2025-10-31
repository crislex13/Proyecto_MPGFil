<?php

namespace App\Http\Controllers;

use App\Models\Productos;
use App\Models\IngresoProducto;
use App\Models\VentaProducto;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ProductoReportController extends Controller
{
    public function diario()
    {
        $hoy   = Carbon::today();
        $start = $hoy->clone()->startOfDay();
        $end   = $hoy->clone()->endOfDay();

        $data = $this->buildData($start, $end);
        $data['titulo']       = 'Reporte Diario - Ingresos y Egresos de Productos';
        $data['periodo']      = $hoy->isoFormat('dddd D [de] MMMM YYYY');
        $data['generado_por'] = optional(auth()->user())->name ?? 'Sistema';
        $data['generado_el']  = now()->isoFormat('dddd D [de] MMMM YYYY, HH:mm');

        return $this->pdf($data, 'reporte-productos-diario-'.$hoy->format('Ymd').'.pdf');
    }

    public function mensual()
    {
        $y = (int) request('year', now()->year);
        $m = (int) request('month', now()->month);

        $start = Carbon::create($y, $m, 1)->startOfMonth();
        $end   = $start->clone()->endOfMonth();

        $data = $this->buildData($start, $end);
        $data['titulo']       = 'Reporte Mensual - Ingresos y Egresos de Productos';
        $data['periodo']      = $start->isoFormat('MMMM [de] YYYY');
        $data['generado_por'] = optional(auth()->user())->name ?? 'Sistema';
        $data['generado_el']  = now()->isoFormat('dddd D [de] MMMM YYYY, HH:mm');

        return $this->pdf($data, 'reporte-productos-'.$start->format('Y-m').'.pdf');
    }

    public function anual()
    {
        $y = (int) request('year', now()->year);

        $start = Carbon::create($y, 1, 1)->startOfYear();
        $end   = $start->clone()->endOfYear();

        $data = $this->buildData($start, $end);
        $data['titulo']       = 'Reporte Anual - Ingresos y Egresos de Productos';
        $data['periodo']      = (string) $y;
        $data['generado_por'] = optional(auth()->user())->name ?? 'Sistema';
        $data['generado_el']  = now()->isoFormat('dddd D [de] MMMM YYYY, HH:mm');

        return $this->pdf($data, 'reporte-productos-'.$y.'.pdf');
    }

    /** ---------- Núcleo: arma todos los datos del PDF ---------- */
    protected function buildData(Carbon $start, Carbon $end): array
    {
        // Ingresos (entradas a inventario)
        $ingresos = IngresoProducto::with('producto')
            ->whereBetween('fecha', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->get();

        // Ventas (salidas de inventario) + detalles
        $ventas = VentaProducto::with('detalles.producto')
            ->whereBetween('fecha', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->get();

        // Colección plana de detalles, segura ante nulls
        $detalleVentas = $ventas->flatMap(fn($v) => $v->detalles ?? collect());

        // KPIs ingresos (costo) = lo que “entra” al inventario por precio de compra
        $ing_unidades = (int) $ingresos->sum('cantidad_unidades');
        $ing_paquetes = (int) $ingresos->sum('cantidad_paquetes');
        $ing_costo    = (float) $ingresos->sum(fn($r) =>
            (int)($r->cantidad_unidades ?? 0) * (float)($r->precio_unitario ?? 0) +
            (int)($r->cantidad_paquetes ?? 0) * (float)($r->precio_paquete ?? 0)
        );
        $ing_registros = $ingresos->count();

        // KPIs ventas (monto) = lo que “sale” vendiéndose (precio de venta)
        $ven_items   = (int) $detalleVentas->sum('cantidad');
        $ven_total   = (float) $ventas->sum('total');
        $ven_registros = $ventas->count();
        $ven_qr      = (float) $ventas->filter(fn($v) => ($v->metodo_pago ?? '') === 'qr')->sum('total');
        $ven_efectivo= (float) $ventas->filter(fn($v) => ($v->metodo_pago ?? '') === 'efectivo')->sum('total');

        // Totales por producto vendido (para lista rápida)
        $totalesPorProducto = [];
        foreach ($detalleVentas as $d) {
            $nombre = $d->producto->nombre ?? '—';
            $totalesPorProducto[$nombre] = ($totalesPorProducto[$nombre] ?? 0) + (float) ($d->subtotal ?? 0);
        }

        // Control de stock por producto (usando stock actual de lotes)
        $productos = Productos::with('lotes')->get();
        $ctrl = $productos->mapWithKeys(function ($p) use ($ingresos, $detalleVentas) {
            $pid        = $p->id;
            $ing        = (int) $ingresos->where('producto_id', $pid)->sum('cantidad_unidades');
            $vend       = (int) $detalleVentas->where('producto_id', $pid)->sum('cantidad');
            $stockActual= (int) ($p->stock_unidades ?? 0);
            // Si stock_unidades representa el stock actual, inicial = actual - ing + vend
            $inicial    = $stockActual - $ing + $vend;
            $final      = $inicial + $ing - $vend; // = stockActual

            return [$p->nombre => [
                'inicial'   => $inicial,
                'ingresado' => $ing,
                'vendido'   => $vend,
                'final'     => $final,
            ]];
        })->toArray();

        // Paquetes para control (opcional): si prefieres mostrarlos, puedes replicar el esquema para stock_paquetes

        return [
            'start' => $start, 'end' => $end,

            // Ingresos KPIs
            'ing_registros' => $ing_registros,
            'ing_unidades'  => $ing_unidades,
            'ing_paquetes'  => $ing_paquetes,
            'ing_costo'     => $ing_costo,

            // Ventas KPIs
            'ven_registros' => $ven_registros,
            'ven_items'     => $ven_items,
            'ven_total'     => $ven_total,
            'ven_qr'        => $ven_qr,
            'ven_efectivo'  => $ven_efectivo,

            // Listas / tablas
            'ingresos'      => $ingresos,
            'detalleVentas' => $detalleVentas,
            'controlStock'  => $ctrl,
            'totalesPorProducto' => $totalesPorProducto,
        ];
    }

    protected function pdf(array $data, string $filename)
    {
        return Pdf::loadView('pdf.productos_resumen', $data)
            ->setPaper('A4', 'portrait')
            ->stream($filename);
    }
}
