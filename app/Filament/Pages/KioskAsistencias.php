<?php

namespace App\Filament\Pages;

use App\Models\Asistencia;
use App\Models\Clientes;
use Filament\Pages\Page;          // 👈 CORRECTO: Page de Filament, no Resources\Pages\Page
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Filament\Panel;

class KioskAsistencias extends Page
{
    // Vista blade: resources/views/filament/pages/kiosk-asistencias.blade.php
    protected static string $view = 'filament.pages.kiosk-asistencias';

    public static function getSlug(): string
    {
        return 'kiosk'; // => /admin/kiosk
    }

    // No lo queremos en el menú
    public static function shouldRegisterNavigation(): bool
    {
        return false; // no mostrar en el menú
    }

    // (Opcional) Proteger acceso directo por URL, ajusta a tu gusto (rol, permiso, etc.)
    public static function canAccess(): bool
    {
        return auth()->check(); // o auth()->user()?->hasRole('recepcionista') …
    }

    // Refresco Livewire cada X segundos (configurable en config/maxpower.php)
    protected function getPollingInterval(): ?string
    {
        $sec = (int) config('maxpower.kiosk.poll_seconds', 3);
        return max(1, $sec) . 's';
    }

    /** Último evento (bienvenida/denegado) de la ventana reciente */
    public function getUltimoEventoProperty(): ?Asistencia
    {
        $windowSec = (int) config('maxpower.kiosk.welcome_window_seconds', 25);

        return Asistencia::query()
            ->where('created_at', '>=', now()->subSeconds($windowSec))
            ->latest('created_at')
            ->first();
    }

    /** Advertencias: asistencias abiertas con ≤ N minutos restantes */
    public function getAdvertenciasProperty(): Collection
    {
        $limMin = (int) config('maxpower.kiosk.warn_minutes', 5);

        return Asistencia::query()
            ->whereNull('hora_salida')
            ->latest('hora_entrada')
            ->get()
            ->filter(function (Asistencia $a) use ($limMin) {
                $rest = $a->min_restantes; // accessor del modelo
                return !is_null($rest) && $rest > 0 && $rest <= $limMin;
            })
            ->values();
    }

    /** Sesiones de HOY del cliente del último evento (si aplica) */
    public function getSesionesDeHoyProperty(): Collection
    {
        $a = $this->ultimoEvento;
        if (!$a || $a->asistible_type !== Clientes::class) {
            return collect();
        }

        /** @var Clientes $cli */
        $cli = $a->asistible;

        return $cli->sesionesAdicionales()
            ->whereDate('fecha', Carbon::today())
            ->orderBy('hora_inicio')
            ->get()
            ->map(fn($s) => [
                'hora_inicio' => (string) $s->hora_inicio,
                'hora_fin' => (string) $s->hora_fin,
                'id' => $s->id,
            ]);
    }

    // (Opcional) Título de la pestaña
    public function getTitle(): string
    {
        return 'Monitor de Asistencias';
    }

    // Si quieres forzar un refresh manual adicional:
    public function render(): View
    {
        // Livewire hará el polling; esto es opcional:
        $this->dispatch('refresh');

        return parent::render();
    }
}
