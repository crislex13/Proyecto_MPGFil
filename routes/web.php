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
        Route::get('dia',  [SesionReportController::class, 'dia'])->name('dia');
        Route::get('mes',  [SesionReportController::class, 'mes'])->name('mes');
        Route::get('anio', [SesionReportController::class, 'anio'])->name('anio');
    });

    //Reportes de ingresos de productos
    Route::prefix('reportes/ingresos')
    ->name('reportes.ingresos.')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('dia',   [IngresoProductoReportController::class, 'dia'])->name('dia');
        Route::get('mes',   [IngresoProductoReportController::class, 'mes'])->name('mes');
        Route::get('anio',  [IngresoProductoReportController::class, 'anio'])->name('anio');
        Route::get('rango', [IngresoProductoReportController::class, 'rango'])->name('rango');
    });

    //Reportes Productos
    Route::prefix('reportes/productos')
    ->name('reporte.productos.')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('diario',  [ProductoReportController::class, 'diario'])->name('diario');
        Route::get('mensual', [ProductoReportController::class, 'mensual'])->name('mensual');
        Route::get('anual',   [ProductoReportController::class, 'anual'])->name('anual');
    });

Route::get('/reportes/financiero', [ReporteGeneralController::class, 'reporteFinanciero'])
    ->middleware(['auth'])
    ->name('reportes.financiero');

Route::post('/admin/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/admin/login');
})->name('filament.admin.auth.logout');