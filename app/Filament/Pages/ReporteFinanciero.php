<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Enums\ActionSize;
use Filament\Forms\Form;

class ReporteFinanciero extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Reportes';
    protected static string $view = 'filament.pages.reporte-financiero';

    // 🔒 No registrar en el menú
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    // 🔒 Bloquear acceso directo por URL
    public static function canAccess(): bool
    {
        return false;
    }
}