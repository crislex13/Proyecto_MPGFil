<?php

namespace App\Filament\Pages;

use App\Models\Clientes;
use App\Models\Personal;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class RolBasedDashboard extends Page
{
    public ?Clientes $cliente = null;
    public ?Personal $instructor = null;

    protected static ?string $title = 'Mi Panel';
    protected static ?string $navigationLabel = 'Inicio';
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $slug = 'mi-panel';
    protected static string $view = 'filament.pages.rol-dashboard';

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        // Mostrar el panel solo para estos roles
        return $user && ($user->hasRole('cliente') || $user->hasRole('instructor') || $user->hasRole('recepcionista'));
    }

    public static function canAccess(): bool
    {
        return self::shouldRegisterNavigation();
    }

    public function mount(): void
    {
        $user = auth()->user();

        // Buscar si es cliente
        $this->cliente = Clientes::with(['planesCliente.plan', 'asistencias', 'sesionesAdicionales'])
            ->where('user_id', $user->id)
            ->first();

        // Buscar si es instructor
        $this->instructor = Personal::with(['turnos', 'asistencias'])
            ->where('user_id', $user->id)
            ->where('cargo', 'instructor')
            ->first();
    }
}
