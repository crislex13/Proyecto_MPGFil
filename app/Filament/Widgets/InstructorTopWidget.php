<?php

namespace App\Filament\Widgets;

use App\Models\SesionAdicional;
use App\Models\Personal;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class InstructorTopWidget extends Widget
{
    protected static string $view = 'filament.widgets.instructor-top-widget';
    protected static ?int $sort = 2;

    public $instructor;
    public $totalSesiones;
    public $totalGanancias;

    public function mount(): void
    {
        $inicioMes = now()->startOfMonth()->toDateString();
        $finMes = now()->endOfMonth()->toDateString();

        $topInstructorId = SesionAdicional::query()
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->whereNotNull('instructor_id')
            ->selectRaw('instructor_id, COUNT(*) as total_sesiones')
            ->groupBy('instructor_id')
            ->orderByDesc('total_sesiones')
            ->pluck('instructor_id')
            ->first();

        $this->instructor = $topInstructorId ? Personal::find($topInstructorId) : null;

        if ($this->instructor) {
            $sesionesInstructor = SesionAdicional::query()
                ->whereBetween('fecha', [$inicioMes, $finMes])
                ->where('instructor_id', $topInstructorId)
                ->get();

            $this->totalSesiones = $sesionesInstructor->count();
            $this->totalGanancias = $sesionesInstructor->sum('precio');
        }
    }
    public function getColumnSpan(): int|string
    {
        return 2;
    }
}