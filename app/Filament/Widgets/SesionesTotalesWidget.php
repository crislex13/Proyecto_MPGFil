<?php


namespace App\Filament\Widgets;

use App\Models\SesionAdicional;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Carbon\Carbon;

class SesionesTotalesWidget extends StatsOverviewWidget
{
    protected function getCards(): array
    {
        $mesActual = SesionAdicional::whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->count();

        $añoActual = SesionAdicional::whereYear('fecha', now()->year)
            ->count();

        return [
            Card::make('📦 Sesiones este mes', $mesActual)
                ->description('Contratadas en ' . now()->format('F'))
                ->color('info')
                ->icon('heroicon-o-calendar-days'),

            Card::make('📆 Sesiones este año', $añoActual)
                ->description('Total en ' . now()->format('Y'))
                ->color('success')
                ->icon('heroicon-o-chart-bar'),
        ];
    }

    public function getColumnSpan(): int|string
    {
        return 4;
    }

    //protected static ?int $sort = 1;
}