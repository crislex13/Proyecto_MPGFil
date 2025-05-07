<?php

namespace App\Filament\Widgets;

use App\Models\PlanCliente;
use Filament\Widgets\LineChartWidget;
use Illuminate\Support\Carbon;
use Filament\Support\Enums\MaxWidth;
use Filament\Widgets\ChartWidget;

class InscripcionesPorDia extends LineChartWidget
{
    protected static ?string $heading = '📈 Inscripciones - Últimos 30 días';

    protected function getData(): array
    {
        $data = PlanCliente::where('fecha_inicio', '>=', now()->subDays(30))
            ->selectRaw('DATE(fecha_inicio) as dia, COUNT(*) as total')
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Inscripciones',
                    'data' => $data->pluck('total'),
                    'borderColor' => '#f59e0b', // Optional: amarillo ámbar
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)', // Optional
                ],
            ],
            'labels' => $data->pluck('dia')->map(fn($d) => Carbon::parse($d)->format('d M')),
        ];
    }

    protected function getColumns(): int
    {
        return 1;
    }

    public function getColumnSpan(): int|string|array
    {
        return [
            'default' => 3,
            'lg' => 2,
            'xl' => 1,
        ];
    }

    protected function getType(): string
    {
        return 'line'; // También podés usar 'bar', 'pie', etc.
    }
}