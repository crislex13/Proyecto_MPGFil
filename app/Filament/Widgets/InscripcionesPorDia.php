<?php

namespace App\Filament\Widgets;

use App\Models\PlanCliente;
use Filament\Widgets\LineChartWidget;
use Illuminate\Support\Carbon;

class InscripcionesPorDia extends LineChartWidget
{
    protected static ?string $heading = 'Inscripciones - Últimos 30 días';

    protected function getData(): array
    {
        $data = PlanCliente::where('fecha_inicio', '>=', now()->subDays(30))
            ->selectRaw('DATE(fecha_inicio) as dia, COUNT(*) as total')
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        $labels = $data->pluck('dia')->map(fn($d) => Carbon::parse($d)->format('d M'))->toArray();
        $values = $data->pluck('total')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Inscripciones',
                    'data' => $values,
                ],
            ],
            'labels' => $labels,
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('admin') || auth()->user()?->hasRole('gerente');
    }
}