<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReporteGeneralController;
use App\Http\Controllers\ReportePersonalController;
use App\Http\Controllers\ReporteProductosDiaController;
use App\Http\Controllers\ReporteProductosMensualController;
use App\Http\Controllers\ReporteProductosAnualController;
use App\Http\Controllers\ReporteClientesMensualController;
use App\Http\Controllers\ReportePersonalMensualController;
use App\Http\Controllers\PlanReportController;
use App\Http\Controllers\SesionReportController;
use App\Http\Controllers\IngresoProductoReportController;
use App\Http\Controllers\ProductoReportController;
use App\Http\Controllers\PdfExportController;
use App\Http\Controllers\CasillerosReportController;
use App\Http\Controllers\CategoriaReportController;
use App\Http\Controllers\ProductoFichaReportController;
use App\Http\Controllers\VentaReportController;
use App\Http\Controllers\TurnoReportController;
use App\Http\Controllers\PagoPersonalReportController;

Route::get('/', function () {
    return view('index');
});
Route::get('/login', function () {
    return redirect('admin/login');
})->name('login');

Route::redirect('/admin/dashboard', '/admin');
//ruta pdf cliente personal
Route::get('/reporte-pdf/{cliente}', [ReporteGeneralController::class, 'generarPDF'])->name('reporte.pdf');
Route::get('/reporte/cliente/{id}/ficha', [\App\Http\Controllers\ReporteClientesController::class, 'ficha'])->name('reporte.cliente.ficha');
Route::get('/clientes/{id}/reporte-mensual', [ReporteClientesMensualController::class, 'reporteMensual'])
    ->name('clientes.reporte.mensual')
    ->middleware(['auth']);

Route::get('/reporte/personal/{id}/ficha', [ReportePersonalController::class, 'ficha'])->name('reporte.personal.ficha');
Route::get('/reporte-personal/{id}/mensual', [ReportePersonalMensualController::class, 'fichaMensual'])
    ->name('personal.reporte.mensual');

// Reportes PDF de productos
Route::get('/reporte-productos/diario', [ReporteProductosDiaController::class, 'diario'])->name('reporte.productos.diario');
Route::get('/reporte-productos/mensual', [ReporteProductosMensualController::class, 'reporteMensual'])->name('reporte.productos.mensual');
Route::get('/reporte-productos/anual', [ReporteProductosAnualController::class, 'reporteAnual'])->name('reporte.productos.anual');

//Reportes de planes clientes
Route::prefix('reportes/planes')
    ->name('reportes.planes.')
    ->middleware(['auth']) // <- SOLO admin
    ->group(function () {
        Route::get('dia', [PlanReportController::class, 'dia'])->name('dia');
        Route::get('mes', [PlanReportController::class, 'mes'])->name('mes');
        Route::get('anio', [PlanReportController::class, 'anio'])->name('anio');
    });

//Reportes de sesiones
Route::prefix('reportes/sesiones')
    ->name('reportes.sesiones.')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('dia', [SesionReportController::class, 'dia'])->name('dia');
        Route::get('mes', [SesionReportController::class, 'mes'])->name('mes');
        Route::get('anio', [SesionReportController::class, 'anio'])->name('anio');
    });

//Reportes de ingresos de productos
Route::prefix('reportes/ingresos')
    ->name('reportes.ingresos.')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('dia', [IngresoProductoReportController::class, 'dia'])->name('dia');
        Route::get('mes', [IngresoProductoReportController::class, 'mes'])->name('mes');
        Route::get('anio', [IngresoProductoReportController::class, 'anio'])->name('anio');
        Route::get('rango', [IngresoProductoReportController::class, 'rango'])->name('rango');
    });

//Reportes Productos
Route::prefix('reportes/productos')
    ->name('reporte.productos.')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('diario', [ProductoReportController::class, 'diario'])->name('diario');
        Route::get('mensual', [ProductoReportController::class, 'mensual'])->name('mensual');
        Route::get('anual', [ProductoReportController::class, 'anual'])->name('anual');
    });

//Route::get('/reportes/financiero', [ReporteGeneralController::class, 'reporteFinanciero'])
//  ->middleware(['auth'])
//->name('reportes.financiero');
//reportes finanzas ingresos egresos 
Route::middleware(['auth'])->get('/reportes/finanzas', [PdfExportController::class, 'finanzasGeneral'])
    ->name('reportes.financiero');

// === Reportes mensuales generales de permisos ===
Route::middleware(['auth'])->group(function () {
    Route::get('/reportes/permisos-clientes/mensual', [\App\Http\Controllers\PermisosClientesReportController::class, 'mensualGeneral'])
        ->name('reportes.permisos.clientes.mensual');

    Route::get('/reportes/permisos-personal/mensual', [\App\Http\Controllers\PermisosPersonalReportController::class, 'mensualGeneral'])
        ->name('reportes.permisos.personal.mensual');
});

//Reportes de casilleros
Route::prefix('reportes/casilleros')
    ->name('reportes.casilleros.')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('dia', [CasillerosReportController::class, 'dia'])->name('dia');
        Route::get('mes', [CasillerosReportController::class, 'mes'])->name('mes');
        Route::get('anio', [CasillerosReportController::class, 'anio'])->name('anio');
    });

//reporte categorias 
Route::prefix('reportes/categorias')
    ->name('reportes.categorias.')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('general', [CategoriaReportController::class, 'general'])->name('general');
        Route::get('mes', [CategoriaReportController::class, 'mes'])->name('mes'); // opcional: por creación en el mes actual
    });

//reportes porfichaproducto 
Route::get('/reportes/producto/{producto}/ficha', [ProductoFichaReportController::class, 'show'])
    ->name('reporte.producto.ficha')
    ->middleware(['auth']);

//reportes de ventas admin y usuarios
Route::middleware(['auth'])->group(function () {

    // ---- Reportes personales (todos los usuarios) ----
    Route::get('/reportes/ventas/dia/mias', [VentaReportController::class, 'diaPersonal'])
        ->name('reporte.ventas.dia.mias');

    Route::get('/reportes/ventas/mes/mias', [VentaReportController::class, 'mesPersonal'])
        ->name('reporte.ventas.mes.mias');

    // ---- Reportes globales (solo admin) ----
    Route::middleware(['auth'])->group(function () {
        Route::get('/reportes/ventas/dia', [VentaReportController::class, 'diaGlobal'])->name('reporte.ventas.dia.global');
        Route::get('/reportes/ventas/mes', [VentaReportController::class, 'mesGlobal'])->name('reporte.ventas.mes.global');
        Route::get('/reportes/ventas/anio', [VentaReportController::class, 'anioGlobal'])->name('reporte.ventas.anio.global');
    });
});

//reporte turnos
Route::middleware(['auth'])->group(function () {
    Route::get(
        '/reportes/turnos/cobertura-mensual',
        [TurnoReportController::class, 'coberturaMensual']
    )->name('turnos.reporte.cobertura');
});

//reportes de pagos personal 
Route::prefix('reportes/pagos-personal')->name('reportes.pagos.')->group(function () {
    Route::get('/dia', [PagoPersonalReportController::class, 'dia'])->name('dia');
    Route::get('/mes', [PagoPersonalReportController::class, 'mes'])->name('mes');
    Route::get('/anio', [PagoPersonalReportController::class, 'anio'])->name('anio');
    Route::get('/rango', [PagoPersonalReportController::class, 'rango'])->name('rango');

    Route::get('/comprobante/{pago}', [PagoPersonalReportController::class, 'comprobante'])
        ->name('comprobante');
});

Route::get('/persona/mes', [PagoPersonalReportController::class, 'personaMes'])
    ->name('persona.mes'); // ?personal_id=ID&year=YYYY&month=MM

Route::post('/admin/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/admin/login');
})->name('filament.admin.auth.logout');