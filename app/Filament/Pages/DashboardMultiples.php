<?php

namespace App\Filament\Pages;

use App\Models\Clientes;
use App\Models\Personal;
use Filament\Pages\Page;

class DashboardMultiples extends Page
{
    public $cliente = null;
    public $instructor = null;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.pages.dashboard-multiples';
    protected static ?string $slug = 'dashboard-multiples';
    protected static ?string $title = 'Mi Panel Combinado';

    public static function shouldRegisterNavigation(): bool
    {
        return false; // Ocultamos del menú
    }

    public function mount()
    {
        $userId = auth()->id();

        $this->cliente = Clientes::where('user_id', $userId)->with('planesCliente.plan')->first();
        $this->instructor = Personal::where('user_id', $userId)->with('turnos')->first();
    }
}
