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
            'asistencias' => fn($q) => $q->latest()->take(5),
        ])->where('user_id', auth()->id())->firstOrFail();
    }
}
