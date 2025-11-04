<?php

namespace App\Http\Controllers;

use App\Models\PermisoPersonal;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PermisosPersonalReportController extends Controller
{
    public function mensualGeneral()
    {
        $fecha = request('fecha', now()->toDateString());
        $ini   = Carbon::parse($fecha)->startOfMonth();
        $fin   = Carbon::parse($fecha)->endOfMonth();

        // Totales por estado
        $base = PermisoPersonal::whereBetween('fecha_inicio', [$ini, $fin]);

        $totales = [
            'pendientes'  => (clone $base)->where('estado', 'pendiente')->count(),
            'aprobados'   => (clone $base)->where('estado', 'aprobado')->count(),
            'rechazados'  => (clone $base)->where('estado', 'rechazado')->count(),
            'solicitados' => (clone $base)->count(),
        ];

        // Días aprobados (inclusive, aprox.)
        $diasAprobados = PermisoPersonal::whereBetween('fecha_inicio', [$ini, $fin])
            ->where('estado', 'aprobado')
            ->get()
            ->sum(function ($p) {
                $iniP = Carbon::parse($p->fecha_inicio);
                $finP = Carbon::parse($p->fecha_fin);
                return max(1, $iniP->diffInDays($finP) + 1);
            });

        // Top motivos (TOP 5)
        $topMotivos = PermisoPersonal::select('motivo', DB::raw('COUNT(*) as c'))
            ->whereBetween('fecha_inicio', [$ini, $fin])
            ->whereNotNull('motivo')
            ->where('motivo', '!=', '')
            ->groupBy('motivo')
            ->orderByDesc('c')
            ->limit(5)
            ->get();

        // Detalle
        $detalle = PermisoPersonal::with(['personal', 'autorizadoPor'])
            ->whereBetween('fecha_inicio', [$ini, $fin])
            ->orderBy('fecha_inicio', 'asc')
            ->get();

        $pdf = Pdf::loadView('pdf.permisos-personal-mensual', [
            'titulo'        => 'Permisos del Personal — Mensual (General)',
            'periodo'       => $ini->format('d/m/Y') . ' — ' . $fin->format('d/m/Y'),
            'generado_por'  => Auth::user()?->name ?? 'Sistema',
            'generado_el'   => now()->format('d/m/Y H:i'),
            'totales'       => $totales,
            'diasAprobados' => $diasAprobados,
            'topMotivos'    => $topMotivos,
            'detalle'       => $detalle,
            'logo'          => $this->cargarLogo(),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('permisos-personal-' . $ini->format('Ym') . '.pdf');
    }

    /** Logo en base64, tolerante a rutas y extensiones. */
    private function cargarLogo(): ?string
    {
        $posibles = [
            public_path('images/LogosMPG/Recurso 3.png'),
            public_path('images/maxpowergym-logo.png'),
            public_path('images/logo.png'),
            public_path('logo.png'),
        ];

        foreach ($posibles as $p) {
            if (is_file($p)) {
                $ext  = strtolower(pathinfo($p, PATHINFO_EXTENSION));
                $mime = $ext === 'jpg' ? 'jpeg' : $ext;
                return 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($p));
            }
        }
        return null;
    }
}
