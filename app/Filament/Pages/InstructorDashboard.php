<?php

namespace App\Filament\Pages;

use App\Models\Personal;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class InstructorDashboard extends Page
{
    public $instructor;

    protected static ?string $title = 'Panel del Instructor';
    protected static ?string $navigationLabel = 'Informacion de Instructor';
    protected static ?string $slug = 'instructor-dashboard';
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static string $view = 'filament.pages.instructor-dashboard';

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() && Auth::user()->hasRole('instructor');
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole('instructor');
    }

    public function mount()
    {
        $this->instructor = Personal::with([
            'turnos',
            'asistencias' => function ($q) {
                $q->where('tipo_asistencia', 'personal')                // solo asistencias de personal
                    ->whereIn('estado', ['puntual', 'atrasado', 'permiso']) // excluir acceso_denegado, etc.
                    ->whereNotNull('hora_entrada')                        // aseguramos “registradas”
                    ->orderByDesc('fecha')
                    ->orderByDesc('hora_entrada')
                    ->take(5);
            },
        ])->where('user_id', auth()->id())->firstOrFail();
    }
}
