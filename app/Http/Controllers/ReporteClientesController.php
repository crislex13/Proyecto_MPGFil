<?php

namespace App\Http\Controllers;

use App\Models\Clientes;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteClientesController extends Controller
{
    public function ficha($id)
    {
        $cliente = Clientes::with([
            'planesCliente.plan',
            'casillero',
            'sesionesAdicionales.turno',
            'sesionesAdicionales.instructor',
            'asistencias', // asegúrate de tener esta relación en el modelo
            'usuario',
            'modificadoPor',
        ])->findOrFail($id);

        $data = [
            'cliente' => $cliente,
        ];

        $pdf = Pdf::loadView('pdf.ficha-cliente', $data)->setPaper('A4');
        return $pdf->stream('Ficha_Cliente_' . $cliente->ci . '.pdf');
    }
}