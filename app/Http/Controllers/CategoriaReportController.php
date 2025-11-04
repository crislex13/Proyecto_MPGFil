<?php

namespace App\Http\Controllers;

use App\Models\CategoriaProducto;
use App\Models\Productos;
use App\Models\LoteProducto;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class CategoriaReportController extends Controller
{
    // Reporte general (todas las categorías)
    public function general()
    {
        $cat  = (new CategoriaProducto)->getTable(); // categorias
        $prod = (new Productos)->getTable();         // productos
        $lote = (new LoteProducto)->getTable();      // lote_productos

        $categorias = DB::table("$cat as c")
            ->selectRaw("
                c.id,
                c.nombre,
                c.descripcion,
                c.created_at,
                (
                    SELECT COUNT(*)
                    FROM $prod p
                    WHERE p.categoria_id = c.id
                ) AS productos_count,
                (
                    SELECT COUNT(*)
                    FROM $lote l
                    INNER JOIN $prod p2 ON p2.id = l.producto_id
                    WHERE p2.categoria_id = c.id
                ) AS lotes_count
            ")
            ->orderBy('nombre')
            ->get();

        return Pdf::loadView('pdf.categorias_resumen', [
                'titulo'      => 'Reporte de Categorías (General)',
                'periodo'     => '—',
                'categorias'  => $categorias,
                'generado_el' => now()->isoFormat('dddd D [de] MMMM YYYY, HH:mm'),
            ])
            ->setPaper('A4','portrait')
            ->stream('reporte-categorias-general.pdf');
    }

    // Reporte mensual (categorías creadas en el mes actual)
    public function mes()
    {
        $cat  = (new CategoriaProducto)->getTable();
        $prod = (new Productos)->getTable();
        $lote = (new LoteProducto)->getTable();

        $desde = now()->startOfMonth()->toDateTimeString();
        $hasta = now()->endOfMonth()->toDateTimeString();

        $categorias = DB::table("$cat as c")
            ->whereBetween('c.created_at', [$desde, $hasta])
            ->selectRaw("
                c.id,
                c.nombre,
                c.descripcion,
                c.created_at,
                (
                    SELECT COUNT(*)
                    FROM $prod p
                    WHERE p.categoria_id = c.id
                ) AS productos_count,
                (
                    SELECT COUNT(*)
                    FROM $lote l
                    INNER JOIN $prod p2 ON p2.id = l.producto_id
                    WHERE p2.categoria_id = c.id
                ) AS lotes_count
            ")
            ->orderBy('nombre')
            ->get();

        return Pdf::loadView('pdf.categorias_resumen', [
                'titulo'      => 'Reporte de Categorías (Mes actual)',
                'periodo'     => now()->isoFormat('MMMM [de] YYYY'),
                'categorias'  => $categorias,
                'generado_el' => now()->isoFormat('dddd D [de] MMMM YYYY, HH:mm'),
            ])
            ->setPaper('A4','portrait')
            ->stream('reporte-categorias-mensual.pdf');
    }
}
