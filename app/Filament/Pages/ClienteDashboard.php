<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class ClienteDashboard extends Page
{
    public $cliente;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static string $view = 'filament.pages.cliente-dashboard';

    protected static ?string $title = 'Mi Panel';
    protected static ?string $navigationLabel = 'Mi Información';
    protected static ?string $slug = 'cliente-dashboard';

    protected function getHeaderActions(): array
    {
        return [];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() && Auth::user()->hasRole('cliente');
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole('cliente');
    }

    public function mount()
    {
        $this->cliente = \App\Models\Clientes::with([
            'planesCliente.plan',
            'asistencias',
            'sesionesAdicionales',
        ])->where('user_id', auth()->id())->firstOrFail();
    }
}
