<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Facades\Route;
use Filament\Forms\Form;
use Filament\Support\Enums\ActionSize;

class ReporteFinanciero extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Reportes';
    protected static string $view = 'filament.pages.reporte-financiero';

    public ?string $tipo = 'diario';
    public ?string $fecha = null;

    public function mount(): void
    {
        $this->form->fill([
            'tipo' => $this->tipo,
            'fecha' => now()->toDateString(),
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('tipo')
                ->label('Tipo de reporte')
                ->options([
                    'diario' => 'Diario',
                    'mensual' => 'Mensual',
                    'anual' => 'Anual',
                ])
                ->required(),

            DatePicker::make('fecha')
                ->label('Fecha de referencia')
                ->required(),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->statePath('data');
    }

    public function generarPDF()
    {
        $data = $this->form->getState();
        $tipo = $data['tipo'];
        $fecha = $data['fecha'];

        return redirect()->route('reportes.financiero', ['tipo' => $tipo, 'fecha' => $fecha]);
    }
}
