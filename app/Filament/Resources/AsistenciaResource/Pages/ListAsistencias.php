<?php

namespace App\Filament\Resources\AsistenciaResource\Pages;

use App\Filament\Resources\AsistenciaResource;
use App\Models\Asistencia;
use App\Models\Clientes;
use App\Models\Personal;
use App\Services\AsistenciaService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListAsistencias extends ListRecords
{
    protected static string $resource = AsistenciaResource::class;

    /** ----------------- Estado UI ----------------- */
    public string $ci = '';
    public ?Clientes $cliente = null;
    public ?Personal $personal = null;
    public bool $mostrarModal = false;

    /** ----------------- Acciones Header ----------------- */
    public function getHeaderActions(): array
    {
        return [
            // 1) Registrar por CI (disparo único)
            Action::make('marcarPorCi')
                ->label('Registrar por CI')
                ->icon('heroicon-o-identification')
                ->form([
                    \Filament\Forms\Components\TextInput::make('ci')
                        ->label('C.I.')
                        ->required()
                        ->placeholder('Ingresa el CI sin espacios'),
                ])
                ->action(function (array $data): void {
                    $ci = trim((string) $data['ci']);
                    // quitar todos los espacios intermedios
                    $ci = preg_replace('/\s+/', '', $ci) ?? '';
                    $this->procesarCi($ci);
                }),

            // 2) Resolver CI duplicado (Cliente + Personal)
            Action::make('resolverDobleRol')
                ->label('El CI pertenece a Cliente e Instructor')
                ->modalHeading('¿Cómo deseas registrar la asistencia?')
                ->modalDescription('Selecciona con qué rol registrar esta marcación.')
                ->modalSubmitActionLabel('Registrar como Cliente')
                ->modalCancelActionLabel('Cancelar')
                ->closeModalByClickingAway(false)
                ->color('info')
                ->visible(fn(): bool => $this->mostrarModal)
                ->requiresConfirmation()
                ->action(function (): void {
                    if ($this->cliente) {
                        [$ok, $msg] = AsistenciaService::toggleCliente($this->cliente, now(), 'manual');
                        $this->notificar($ok, $msg);
                    }
                    $this->resetUi();
                })
                ->extraModalFooterActions([
                    Action::make('registrarComoInstructor')
                        ->label('Registrar como Instructor')
                        ->color('success')
                        ->action(function (): void {
                            if ($this->personal) {
                                [$ok, $msg] = AsistenciaService::togglePersonal($this->personal, now(), 'manual');
                                $this->notificar($ok, $msg);
                            }
                            $this->resetUi();
                        }),
                ]),
        ];
    }

    /** ----------------- Flujo principal ----------------- */
    private function procesarCi(string $ci): void
    {
        if ($ci === '') {
            Notification::make()
                ->title('⚠️ CI vacío')
                ->warning()
                ->send();
            return;
        }

        $this->cliente = Clientes::where('ci', $ci)->first();
        $this->personal = Personal::where('ci', $ci)->first();

        // Solo cliente
        if ($this->cliente && !$this->personal) {
            [$ok, $msg] = AsistenciaService::toggleCliente($this->cliente, now(), 'manual');
            $this->notificar($ok, $msg);
            $this->resetUi();
            return;
        }

        // Solo personal
        if ($this->personal && !$this->cliente) {
            [$ok, $msg] = AsistenciaService::togglePersonal($this->personal, now(), 'manual');
            $this->notificar($ok, $msg);
            $this->resetUi();
            return;
        }

        // Cliente + personal => mostrar modal para elegir
        if ($this->cliente && $this->personal) {
            $this->mostrarModal = true;
            return;
        }

        // No existe
        Notification::make()
            ->title('⚠️ CI no registrado')
            ->body('No se encontró ningún cliente ni personal con ese CI.')
            ->danger()
            ->send();

        $this->resetUi();
    }

    /** ----------------- Utilidades UI ----------------- */
    private function notificar(bool $ok, string $msg): void
    {
        Notification::make()
                    ->title($ok ? '✅ Marca registrada' : '🚫 Acceso denegado')
                    ->body($msg)
            ->{$ok ? 'success' : 'danger'}()
                ->send();
    }

    private function resetUi(): void
    {
        $this->reset('ci', 'cliente', 'personal', 'mostrarModal');
        // refrescar tabla; en Filament v3 con Livewire v3 esto es suficiente
        $this->dispatch('refresh');
    }

    /** ----------------- Soporte: CI preseleccionado por sesión ----------------- */
    public function mount(): void
    {
        parent::mount();

        if (session()->has('ci_preseleccionado')) {
            $ci = (string) session()->pull('ci_preseleccionado');
            $ci = preg_replace('/\s+/', '', trim($ci)) ?? '';
            $this->procesarCi($ci);
        }
    }

    /** ----------------- Nota -----------------
     * Evitamos lógica on-the-fly (por cada tecla). 
     * El registro ocurre sólo por acción del formulario o sesión.
     */

    protected function getTablePollingInterval(): ?string
    {
        return '20s'; // refresco cada 20s
    }

    protected function onTablePolling(): void
    {
        $this->avisarRestantes();
    }

    protected function avisarRestantes(): void
    {
        $abiertas = Asistencia::query()
            ->whereNull('hora_salida')
            ->latest('hora_entrada')
            ->get();

        foreach ($abiertas as $a) {
            $rest = $a->min_restantes;   // accessor

            if (is_null($rest))
                continue;        // sin fin programado
            if ($rest <= 0)
                continue;            // ya terminó
            if ($rest > 5)
                continue;             // aún falta más de 5

            // evita repetir notificación
            $key = "warn_asistencia_{$a->id}";
            if (!cache()->get($key)) {
                Notification::make()
                    ->title("⏳ {$a->nombre_completo} termina en {$rest} min")
                    ->body('Gestiona la salida a tiempo.')
                    ->warning()
                    ->persistent()
                    ->send();

                cache()->put($key, true, now()->addMinutes(10));
            }
        }
    }

}
