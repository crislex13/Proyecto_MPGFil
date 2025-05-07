<?php

namespace App\Http\Controllers;

use App\Models\Personal;
use App\Models\PagoPersonal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;

class ReportePersonalController extends Controller
{
    public function ficha($id)
    {
        $personal = Personal::with(['turnos', 'asistencias', 'permisos'])->findOrFail($id);

        $pagos = PagoPersonal::with(['turno', 'sala'])
            ->where('personal_id', $personal->id)
            ->where('pagado', true)
            ->get();

        $salasUnicas = $pagos
            ->filter(fn($p) => $p->sala)
            ->pluck('sala.nombre')
            ->unique();

        $pdf = Pdf::loadView('pdf.ficha-personal', [
            'personal' => $personal,
            'pagos' => $pagos,
            'salasUnicas' => $salasUnicas,
        ])->setPaper('A4');

        return $pdf->stream('Ficha_Personal_' . $personal->ci . '.pdf'); 
    }
}
