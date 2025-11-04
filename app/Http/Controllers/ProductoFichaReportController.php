<?php

namespace App\Http\Controllers;

use App\Models\Productos;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ProductoFichaReportController extends Controller
{
    public function show(Productos $producto)
    {
        Carbon::setLocale('es');

        // Eager-load relaciones clave
        $producto->load([
            'categoria',
            'registradoPor',
            'modificadoPor',
            'lotes' => fn($q) => $q->orderBy('fecha_vencimiento')->orderBy('fecha_ingreso'),
            'ingresos' => fn($q) => $q->latest()->limit(50),   // para “movimientos recientes”
            'detallesVenta' => fn($q) => $q->latest()->limit(50),
        ]);

        $hoy = Carbon::today();

        // Lotes y stock
        $lotes = $producto->lotes ?? collect();
        $totalLotes = $lotes->count();
        $stockUnidades = (int) $lotes->sum('stock_unidades');
        $stockPaquetes = (int) $lotes->sum('stock_paquetes');

        $lotesVencidos = $lotes->filter(fn($l) => $l->fecha_vencimiento && Carbon::parse($l->fecha_vencimiento)->lt($hoy))->count();
        $lotesVigentes = $lotes->filter(fn($l) => !$l->fecha_vencimiento || Carbon::parse($l->fecha_vencimiento)->gte($hoy))->count();
        $proxVencimiento = optional(
            $lotes->filter(fn($l) => $l->fecha_vencimiento && Carbon::parse($l->fecha_vencimiento)->gte($hoy))
                ->sortBy('fecha_vencimiento')
                ->first()
        )->fecha_vencimiento;

        // Ingresos (entradas) recientes
        $ingresosRecientes = $producto->ingresos ?? collect();
        $totalUnidIngresadas = (int) $ingresosRecientes->sum('cantidad_unidades');
        $totalPaqIngresados = (int) $ingresosRecientes->sum('cantidad_paquetes');
        $ultimoIngreso = optional($ingresosRecientes->sortByDesc('fecha')->first())->fecha;

        // Ventas (salidas) recientes (detalles)
        $detallesVenta = $producto->detallesVenta ?? collect();
        $totalUnidVendidas = (int) $detallesVenta->sum('cantidad');
        $montoVendido = (float) $detallesVenta->sum('subtotal');
        $ultimaVenta = optional($detallesVenta->sortByDesc('created_at')->first())->created_at;

        // Resumen por mes (últimos 12 meses) con barras
        $inicioMes = Carbon::now()->startOfMonth()->subMonths(11);
        $ventasPorMes = collect();
        $maxMontoMes = 1;

        for ($i = 0; $i < 12; $i++) {
            $ini = (clone $inicioMes)->addMonths($i);
            $fin = (clone $ini)->endOfMonth();

            $itemsMes = $producto->detallesVenta()
                ->whereBetween('created_at', [$ini->startOfDay(), $fin->endOfDay()])
                ->get();

            $cant = (int) $itemsMes->sum('cantidad');
            $monto = (float) $itemsMes->sum('subtotal');

            $ventasPorMes->push([
                'etiqueta' => $ini->isoFormat('MMM YY'),
                'cantidad' => $cant,
                'monto' => $monto,
            ]);

            if ($monto > $maxMontoMes)
                $maxMontoMes = $monto;
        }

        // Imagen absoluta para DomPDF (evitar 404 de storage)
        $imgPath = null;
        if ($producto->imagen) {
            // asumiendo disk 'public'
            $abs = public_path('storage/' . ltrim($producto->imagen, '/'));
            if (is_file($abs))
                $imgPath = $abs;
        }
        if (!$imgPath) {
            $fallback = public_path('images/default-product.png');
            $imgPath = is_file($fallback) ? $fallback : null;
        }

        // KPI “salud” del inventario: días a vencer (si perecedero + lotes)
        $diasProxVenc = $proxVencimiento ? Carbon::parse($proxVencimiento)->diffInDays($hoy, false) * -1 : null;

        // Movimientos (mostrar los últimos 10 de cada uno)
        $ingresosTabla = $ingresosRecientes->sortByDesc('fecha')->take(10);
        $ventasTabla = $detallesVenta->sortByDesc('created_at')->take(10);

        $data = [
            'titulo' => 'Ficha de Producto',
            'generado_por' => optional(auth()->user())->name ?? 'Sistema',
            'generado_el' => now()->isoFormat('dddd D [de] MMMM YYYY, HH:mm'),

            'producto' => $producto,
            'imgPath' => $imgPath,

            // KPIs
            'totalLotes' => $totalLotes,
            'stockUnidades' => $stockUnidades,
            'stockPaquetes' => $stockPaquetes,
            'lotesVigentes' => $lotesVigentes,
            'lotesVencidos' => $lotesVencidos,
            'proxVencimiento' => $proxVencimiento,
            'diasProxVenc' => $diasProxVenc,

            'totalUnidIngresadas' => $totalUnidIngresadas,
            'totalPaqIngresados' => $totalPaqIngresados,
            'ultimoIngreso' => $ultimoIngreso,

            'totalUnidVendidas' => $totalUnidVendidas,
            'montoVendido' => $montoVendido,
            'ultimaVenta' => $ultimaVenta,

            // Listas
            'lotes' => $lotes,
            'ingresosTabla' => $ingresosTabla,
            'ventasTabla' => $ventasTabla,

            // Series por mes (barras)
            'ventasPorMes' => $ventasPorMes,
            'maxMontoMes' => $maxMontoMes,
        ];

        return Pdf::loadView('pdf.ficha_producto', $data)
            ->setPaper('A4', 'portrait')
            ->stream('ficha-producto-' . $producto->id . '.pdf');
    }
}
