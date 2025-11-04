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
    protected static ?string $navigationLabel = 'Información de Cliente';
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
            // Solo asistencias válidas (excluye falta / acceso_denegado)
            'asistencias' => fn($q) => $q
                ->whereIn('estado', ['puntual', 'atrasado', 'permiso'])
                ->latest('fecha')->latest('hora_entrada'),
            'sesionesAdicionales' => fn($q) => $q->latest('fecha'),
        ])
            ->where('user_id', auth()->id())
            ->firstOrFail();
    }
}
