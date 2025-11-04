<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\ResumenEstadistico;
use App\Filament\Widgets\InscripcionesPorDia;
use App\Filament\Widgets\InstructorTopWidget;
use App\Filament\Widgets\ProductoTopWidget;
use App\Filament\Widgets\FlujoCajaDiaWidget;
use App\Filament\Widgets\FlujoCajaSemana;
use Illuminate\Support\Facades\Auth;
use App\Filament\Widgets\SesionesTotalesWidget;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Panel de Control';
    protected static ?string $title = 'Panel de Control - MaxPowerGym';

    protected function getHeaderWidgets(): array
    {
        return [
            ResumenEstadistico::class,
            InstructorTopWidget::class,
            ProductoTopWidget::class,
            FlujoCajaDiaWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return [
            ResumenEstadistico::class => 4,
            InstructorTopWidget::class => 2,
            ProductoTopWidget::class => 2,
            FlujoCajaDiaWidget::class => 4,
        ];
    }

    // >>> AÑADE ESTO <<<
    protected function getHeaderActions(): array
    {
        $soloAdmin = fn() => Auth::user()?->hasRole('admin');

        return [
            Actions\Action::make('pdf_diario_link')
                ->label('PDF diario')
                ->icon('heroicon-o-document-arrow-down')
                ->visible($soloAdmin)
                ->url(fn() => route('reportes.financiero', [
                    'tipo' => 'diario',
                    'fecha' => now()->toDateString(), // referencia de hoy
                ]), shouldOpenInNewTab: true),

            Actions\Action::make('pdf_mensual_link')
                ->label('PDF mensual')
                ->icon('heroicon-o-document-arrow-down')
                ->visible($soloAdmin)
                ->url(fn() => route('reportes.financiero', [
                    'tipo' => 'mensual',
                    'fecha' => now()->toDateString(), // el controller usa start/endOfMonth()
                ]), shouldOpenInNewTab: true),

            Actions\Action::make('pdf_anual_link')
                ->label('PDF anual')
                ->icon('heroicon-o-document-arrow-down')
                ->visible($soloAdmin)
                ->url(fn() => route('reportes.financiero', [
                    'tipo' => 'anual',
                    'fecha' => now()->toDateString(), // el controller usa start/endOfYear()
                ]), shouldOpenInNewTab: true),
        ];
    }
    // <<< FIN ACCIONES >>>

    protected function getFooterWidgets(): array
    {
        return [
            //FlujoCajaSemana::class,
            //InscripcionesPorDia::class,
        ];
    }

    public function getFooterWidgetsColumns(): int|array
    {
        return [
            FlujoCajaSemana::class => 6,
            InscripcionesPorDia::class => 6,
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->hasAnyRole(['admin', 'supervisor', 'recepcionista']);
    }
}
