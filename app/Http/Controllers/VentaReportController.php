<?php

namespace App\Http\Controllers;

use App\Models\VentaProducto;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VentaReportController extends Controller
{
    // ====== PERSONALES (usuario logueado) ======

    public function diaPersonal(Request $request)
    {
        $hoy = Carbon::today();
        $start = $hoy->clone()->startOfDay();
        $end = $hoy->clone()->endOfDay();

        $user = auth()->user();

        $data = $this->buildData($start, $end, scope: 'mine', userId: $user->id);
        $data['titulo'] = 'Reporte Diario de Ventas (Mis ventas)';
        $data['periodo'] = $hoy->isoFormat('dddd D [de] MMMM YYYY');
        $data['generado_por'] = $user->name ?? 'Sistema';
        $data['generado_el'] = now()->isoFormat('dddd D [de] MMMM YYYY, HH:mm');
        $data['es_global'] = false;

        return $this->pdf($data, 'reporte-ventas-dia-mias-' . $hoy->format('Ymd') . '.pdf');
    }

    public function mesPersonal(Request $request)
    {
        $y = (int) $request->input('year', now()->year);
        $m = (int) $request->input('month', now()->month);

        $start = Carbon::create($y, $m, 1)->startOfMonth();
        $end = $start->clone()->endOfMonth();

        $user = auth()->user();

        $data = $this->buildData($start, $end, scope: 'mine', userId: $user->id);
        $data['titulo'] = 'Reporte Mensual de Ventas (Mis ventas)';
        $data['periodo'] = $start->isoFormat('MMMM [de] YYYY');
        $data['generado_por'] = $user->name ?? 'Sistema';
        $data['generado_el'] = now()->isoFormat('dddd D [de] MMMM YYYY, HH:mm');
        $data['es_global'] = false;

        return $this->pdf($data, 'reporte-ventas-mes-mias-' . $start->format('Y-m') . '.pdf');
    }

    // ====== GLOBALES (solo admin) ======

    public function diaGlobal(Request $request)
    {
        $this->authorizeAdmin();
        $hoy = Carbon::today();
        $start = $hoy->clone()->startOfDay();
        $end = $hoy->clone()->endOfDay();

        // opcional: ?user_id= filtrar un vendedor específico
        $userId = $request->integer('user_id') ?: null;

        $data = $this->buildData($start, $end, scope: 'all', userId: $userId);
        $data['titulo'] = 'Reporte Diario de Ventas (Global)';
        $data['periodo'] = $hoy->isoFormat('dddd D [de] MMMM YYYY');
        $data['generado_por'] = optional(auth()->user())->name ?? 'Sistema';
        $data['generado_el'] = now()->isoFormat('dddd D [de] MMMM YYYY, HH:mm');
        $data['es_global'] = true;

        return $this->pdf($data, 'reporte-ventas-dia-global-' . $hoy->format('Ymd') . '.pdf');
    }

    public function mesGlobal(Request $request)
    {
        $this->authorizeAdmin();

        $y = (int) $request->input('year', now()->year);
        $m = (int) $request->input('month', now()->month);

        $start = Carbon::create($y, $m, 1)->startOfMonth();
        $end = $start->clone()->endOfMonth();

        $userId = $request->integer('user_id') ?: null;

        $data = $this->buildData($start, $end, scope: 'all', userId: $userId);
        $data['titulo'] = 'Reporte Mensual de Ventas (Global)';
        $data['periodo'] = $start->isoFormat('MMMM [de] YYYY');
        $data['generado_por'] = optional(auth()->user())->name ?? 'Sistema';
        $data['generado_el'] = now()->isoFormat('dddd D [de] MMMM YYYY, HH:mm');
        $data['es_global'] = true;

        return $this->pdf($data, 'reporte-ventas-mes-global-' . $start->format('Y-m') . '.pdf');
    }

    public function anioGlobal(Request $request)
    {
        $this->authorizeAdmin();

        $y = (int) $request->input('year', now()->year);
        $start = Carbon::create($y, 1, 1)->startOfYear();
        $end = $start->clone()->endOfYear();

        $userId = $request->integer('user_id') ?: null;

        $data = $this->buildData($start, $end, scope: 'all', userId: $userId);
        $data['titulo'] = 'Reporte Anual de Ventas (Global)';
        $data['periodo'] = (string) $y;
        $data['generado_por'] = optional(auth()->user())->name ?? 'Sistema';
        $data['generado_el'] = now()->isoFormat('dddd D [de] MMMM YYYY, HH:mm');
        $data['es_global'] = true;

        return $this->pdf($data, 'reporte-ventas-anio-global-' . $y . '.pdf');
    }

    // ====== Núcleo de armado de datos ======

    protected function buildData(Carbon $start, Carbon $end, string $scope = 'mine', ?int $userId = null): array
    {
        // Base: ventas con detalles y producto, y vendedor
        $ventas = VentaProducto::query()
            ->with(['detalles.producto', 'usuario'])
            ->whereBetween('fecha', [$start->toDateTimeString(), $end->toDateTimeString()]);

        if ($scope === 'mine') {
            $ventas->where('usuario_id', $userId ?? auth()->id());
        } elseif ($scope === 'all' && $userId) {
            // admin mirando a un usuario específico
            $ventas->where('usuario_id', $userId);
        }

        $ventas = $ventas->get();

        // Colección plana de detalles
        $detalle = $ventas->flatMap(fn($v) => $v->detalles ?? collect());

        // KPIs
        $ven_registros = $ventas->count();
        $ven_items = (int) $detalle->sum('cantidad');
        $ven_total = (float) $ventas->sum('total');
        $ven_qr = (float) $ventas->where('metodo_pago', 'qr')->sum('total');
        $ven_efectivo = (float) $ventas->where('metodo_pago', 'efectivo')->sum('total');

        // Top por producto (monto y qty)
        $porProducto = $detalle
            ->groupBy('producto_id')
            ->map(function ($rows) {
                $nombre = optional($rows->first()->producto)->nombre ?? '—';
                $qty = (int) $rows->sum('cantidad');
                $monto = (float) $rows->sum('subtotal');
                return (object) compact('nombre', 'qty', 'monto');
            })
            ->values()
            ->sortByDesc('monto')
            ->values();

        $maxMontoProd = max(($porProducto->max('monto') ?? 1), 1);

        // Solo en global: agregado por usuario (para barras)
        $porUsuario = collect();
        $maxMontoUser = 1;
        if ($scope === 'all' && !$userId) {
            $porUsuario = $ventas
                ->groupBy('usuario_id')
                ->map(function ($rows, $uid) {
                    $usuario = optional($rows->first()->usuario)->name ?? '—';
                    $registros = $rows->count();
                    $monto = (float) $rows->sum('total');
                    return (object) compact('usuario', 'registros', 'monto');
                })
                ->values()
                ->sortByDesc('monto')
                ->values();
            $maxMontoUser = max(($porUsuario->max('monto') ?? 1), 1);
        }

        // Detalle para tabla (fecha, prod, qty, p.u., subtotal, usuario)
        $detalleTabla = $ventas->flatMap(function ($v) {
            $fecha = optional($v->fecha)->format('d/m/Y H:i');
            $vendedor = optional($v->usuario)->name ?? '—';

            return ($v->detalles ?? collect())->map(function ($d) use ($fecha, $vendedor) {
                return (object) [
                    'fecha' => $fecha,
                    'producto' => $d->producto->nombre ?? '—',
                    'cantidad' => (int) ($d->cantidad ?? 0),
                    'pu' => (float) ($d->precio_unitario ?? 0),
                    'subtotal' => (float) ($d->subtotal ?? 0),
                    'vendedor' => $vendedor,
                ];
            });
        })->sortBy('fecha')->values();

        return compact(
            'start',
            'end',
            'ven_registros',
            'ven_items',
            'ven_total',
            'ven_qr',
            'ven_efectivo',
            'porProducto',
            'maxMontoProd',
            'porUsuario',
            'maxMontoUser',
            'detalleTabla'
        );
    }

    protected function pdf(array $data, string $filename)
    {
        return Pdf::loadView('pdf.ventas_resumen', $data)
            ->setPaper('A4', 'portrait')
            ->stream($filename);
    }

    protected function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->hasRole('admin') === true, 403, 'Solo administradores.');
    }
}
