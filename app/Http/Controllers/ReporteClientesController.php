<?php

namespace App\Http\Controllers;

use App\Models\Clientes;
use App\Models\PlanCliente;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class ReporteClientesController extends Controller
{
    public function ficha(int $id)
    {
        $cliente = Clientes::with([
            'usuario',
            'modificadoPor',
            'casillero',
            // Traemos los planes para detectar el vigente/reciente
            'planesCliente.plan:id,nombre',
            'planesCliente.disciplina:id,nombre',
        ])->findOrFail($id);

        // Plan vigente (o el más reciente si no hay vigente)
        $planVigente = PlanCliente::query()
            ->with(['plan:id,nombre', 'disciplina:id,nombre'])
            ->where('cliente_id', $cliente->id)
            ->orderByRaw("
                CASE 
                    WHEN estado = 'vigente' THEN 1
                    WHEN estado = 'deuda' THEN 2
                    WHEN estado = 'bloqueado' THEN 3
                    WHEN estado = 'vencido' THEN 4
                    ELSE 5
                END
            ")
            ->orderByDesc('fecha_inicio')
            ->first();

        // Logo + foto
        $logo = public_path('images/LogosMPG/Recurso 3.png');
        $fotoPath = method_exists($cliente, 'getFotoPathForPdfAttribute')
            ? $cliente->foto_path_for_pdf
            : ($cliente->foto
                ? public_path('storage/' . $cliente->foto)
                : public_path('images/default-user.png'));

        // Metadatos
        $generadoPor = optional(auth()->user())->name ?? 'Sistema';
        $generadoEl = now()->isoFormat('dddd D [de] MMMM YYYY, HH:mm');

        return Pdf::loadView('pdf.ficha-cliente', [
            'cliente' => $cliente,
            'planVigente' => $planVigente,
            'logo' => $logo,
            'fotoPath' => $fotoPath,
            'generadoPor' => $generadoPor,
            'generadoEl' => $generadoEl,
        ])->setPaper('A4')->stream('Ficha_Cliente_' . $cliente->ci . '.pdf');
    }
}
