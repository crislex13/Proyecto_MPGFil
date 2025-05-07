<?php

namespace App\Filament\Widgets;

use App\Models\DetalleVentaProducto;
use App\Models\productos;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class ProductoTopWidget extends Widget
{
    protected static string $view = 'filament.widgets.producto-top-widget';
    protected static ?int $sort = 3;

    public $producto;
    public $totalVendidas;
    public $totalGenerado;

    public function mount(): void
    {
        $inicioMes = now()->startOfMonth()->toDateTimeString();
        $finMes = now()->endOfMonth()->toDateTimeString();

        $topProducto = DetalleVentaProducto::whereBetween('created_at', [$inicioMes, $finMes])
            ->selectRaw('producto_id, SUM(cantidad) as total_vendido, SUM(cantidad * precio_unitario) as total_bs')
            ->groupBy('producto_id')
            ->orderByDesc('total_vendido')
            ->first();

        if ($topProducto && $topProducto->producto_id) {
            $this->producto = productos::find($topProducto->producto_id);
            $this->totalVendidas = $topProducto->total_vendido;
            $this->totalGenerado = $topProducto->total_bs;
        }
    }

    public function getColumnSpan(): int|string
    {
        return 2;
    }
}