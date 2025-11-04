<?php

namespace App\Http\Controllers;

use App\Models\PermisoCliente;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PermisosClientesReportController extends Controller
{
    public function mensualGeneral()
    {
        $fecha = request('fecha', now()->toDateString());
        $ini = Carbon::parse($fecha)->startOfMonth();
        $fin = Carbon::parse($fecha)->endOfMonth();

        // Totales por estado
        $base = PermisoCliente::whereBetween('fecha', [$ini, $fin]);

        $totales = [
            'pendientes' => (clone $base)->where('estado', 'pendiente')->count(),
            'aprobados' => (clone $base)->where('estado', 'aprobado')->count(),
            'rechazados' => (clone $base)->where('estado', 'rechazado')->count(),
            'solicitados' => (clone $base)->count(),
        ];

        // Top motivos (los 5 más frecuentes)
        $topMotivos = PermisoCliente::select('motivo', DB::raw('COUNT(*) as c'))
            ->whereBetween('fecha', [$ini, $fin])
            ->whereNotNull('motivo')
            ->where('motivo', '!=', '')
            ->groupBy('motivo')
            ->orderByDesc('c')
            ->limit(5)
            ->get();

        // Clientes con ≥3 permisos en el mes (unimos con clientes y sacamos nombre directamente)
        $limiteClientes = PermisoCliente::from('permisos_clientes as pc')
            ->join('clientes as c', 'c.id', '=', 'pc.cliente_id')
            ->whereBetween('pc.fecha', [$ini, $fin])
            ->selectRaw("
                CONCAT(c.nombre, ' ', c.apellido_paterno, ' ', COALESCE(c.apellido_materno, '')) as cliente_nombre,
                COUNT(*) as c
            ")
            ->groupBy('c.id', 'c.nombre', 'c.apellido_paterno', 'c.apellido_materno')
            ->havingRaw('COUNT(*) >= 3')
            ->orderByDesc('c')
            ->get();

        // Detalle general (con relaciones para mostrar nombres)
        $detalle = PermisoCliente::with(['cliente', 'autorizadoPor'])
            ->whereBetween('fecha', [$ini, $fin])
            ->orderBy('fecha', 'asc')
            ->get();

        // Logo como data URI (seguro para DomPDF)
        $logoPath = public_path('images/maxpowergym-logo.png');
        $logo = $this->cargarLogo();

        $pdf = Pdf::loadView('pdf.permisos-clientes-mensual', [
            'titulo' => 'Permisos de Clientes — Mensual (General)',
            'logo' => $logo,
            'periodo' => $ini->format('d/m/Y') . ' — ' . $fin->format('d/m/Y'),
            'generado_por' => Auth::user()?->name ?? 'Sistema',
            'generado_el' => now()->format('d/m/Y H:i'),
            'totales' => $totales,
            'topMotivos' => $topMotivos,
            'limiteClientes' => $limiteClientes,
            'detalle' => $detalle,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('permisos-clientes-' . $ini->format('Ym') . '.pdf');
    }

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
                $ext = strtolower(pathinfo($p, PATHINFO_EXTENSION));
                $mime = $ext === 'jpg' ? 'jpeg' : $ext; // jpg -> jpeg
                return 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($p));
            }
        }
        return null;
    }
}
