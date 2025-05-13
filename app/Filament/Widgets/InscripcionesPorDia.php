<?php

namespace App\Filament\Widgets;

use App\Models\PlanCliente;
use Filament\Widgets\LineChartWidget;
use Illuminate\Support\Carbon;
use Filament\Support\Enums\MaxWidth;
use Filament\Widgets\ChartWidget;
use BezhanSalleh\FilamentShield\Contracts\HasWidgetAuthorization;

class InscripcionesPorDia extends LineChartWidget
{
    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }
    protected static ?string $heading = 'Inscripciones - Últimos 30 días';

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
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)',
                ],
            ],
            'labels' => $data->pluck('dia')->map(fn($d) => Carbon::parse($d)->format('d M')),
        ];
    }

    protected function getColumns(): int
    {
        return 1;
    }

    protected function getType(): string
    {
        return 'line';
    }

    public function getColumnSpan(): int|string
    {
        return 3;
    }

    protected function getChartOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'responsive' => true,
        ];
    }

    public function getExtraAttributes(): array
    {
        return [
            'class' => 'h-[180px] max-h-[180px] overflow-hidden',
        ];
    }
}