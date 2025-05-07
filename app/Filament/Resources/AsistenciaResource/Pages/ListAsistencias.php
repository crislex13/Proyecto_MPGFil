<?php

namespace App\Filament\Resources\AsistenciaResource\Pages;

use App\Models\Clientes;
use App\Models\Personal;
use App\Models\Asistencia;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\AsistenciaResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;
use Filament\Actions\Action;
use App\Models\PlanCliente;

class ListAsistencias extends ListRecords
{
    protected static string $resource = AsistenciaResource::class;

    public string $ci = '';
    public ?Clientes $cliente = null;
    public ?Personal $personal = null;
    public bool $mostrarModal = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }

    public function updatedCi($value)
    {
        $this->ci = trim($value);

        $this->cliente = Clientes::where('ci', $this->ci)->first();
        $this->personal = Personal::where('ci', $this->ci)->first();

        if ($this->cliente && !$this->personal) {
            $this->registrarComoCliente();
        } elseif (!$this->cliente && $this->personal) {
            $this->registrarComoPersonal();
        } elseif ($this->cliente && $this->personal) {
            $this->mostrarModal = true;
        } else {
            Notification::make()
                ->title('⚠️ CI no registrado')
                ->danger()
                ->body("No se encontró ningún cliente ni personal con el CI ingresado.")
                ->send();
        }
    }

    public function registrarComoCliente()
    {
        $cliente = $this->cliente;

        // ✅ 1. Buscar sesiones adicionales no registradas para hoy
        $sesiones = $cliente->sesionesAdicionalesDeHoyNoRegistradas();

        if ($sesiones->count()) {
            foreach ($sesiones as $sesion) {
                $ahora = now();
                $inicio = \Carbon\Carbon::parse($sesion->hora_inicio)->subMinutes(15);
                $fin = \Carbon\Carbon::parse($sesion->hora_fin);

                if ($ahora->between($inicio, $fin)) {
                    // Determinar si llegó puntual o atrasado
                    $horaSesion = \Carbon\Carbon::parse($sesion->hora_inicio);
                    $estado = $ahora->lte($horaSesion) ? 'puntual' : 'atrasado';

                    Asistencia::create([
                        'asistible_id' => $cliente->id,
                        'asistible_type' => Clientes::class,
                        'tipo_asistencia' => 'sesion',
                        'sesion_adicional_id' => $sesion->id,
                        'fecha' => today(),
                        'hora_entrada' => $ahora,
                        'estado' => $estado, // ahora es puntual o atrasado según la hora exacta de la sesión
                        'origen' => 'manual',
                        'usuario_registro_id' => auth()->id(),
                        'observacion' => "Asistencia a sesión adicional registrada como $estado.",
                    ]);

                    Notification::make()
                        ->title('✅ Asistencia registrada')
                        ->body("Se registró asistencia a tu sesión adicional ($estado).")
                        ->success()
                        ->send();

                    $this->reset('ci', 'cliente', 'personal', 'mostrarModal');
                    return;
                }
            }

            // ❌ El cliente tiene sesiones hoy, pero está fuera de horario
            Asistencia::create([
                'asistible_id' => $cliente->id,
                'asistible_type' => Clientes::class,
                'tipo_asistencia' => 'sesion',
                'fecha' => today(),
                'hora_entrada' => now(),
                'estado' => 'acceso_denegado',
                'origen' => 'manual',
                'usuario_registro_id' => auth()->id(),
                'observacion' => 'Sesión adicional fuera de horario permitido',
            ]);

            Notification::make()
                ->title('⏰ Fuera de horario')
                ->body('Tienes sesiones hoy, pero no estás dentro del horario permitido.')
                ->warning()
                ->send();

            $this->reset('ci', 'cliente', 'personal', 'mostrarModal');
            return;
        }

        // 🔍 2. Si no tiene sesión, se verifica su plan activo
        [$puedeIngresar, $mensaje] = $cliente->puedeRegistrarAsistenciaHoy();

        if (!$puedeIngresar) {
            // ❌ El cliente no cumple condiciones para ingresar
            Asistencia::create([
                'asistible_id' => $cliente->id,
                'asistible_type' => Clientes::class,
                'tipo_asistencia' => 'plan',
                'fecha' => today(),
                'hora_entrada' => now(),
                'estado' => 'acceso_denegado',
                'origen' => 'manual',
                'usuario_registro_id' => auth()->id(),
                'observacion' => $mensaje,
            ]);

            Notification::make()
                ->title('🚫 Acceso denegado')
                ->body($mensaje)
                ->danger()
                ->send();

            $this->reset('ci', 'cliente', 'personal', 'mostrarModal');
            return;
        }

        // ✅ 3. Ingreso por plan activo
        $plan = $cliente->planesCliente()
            ->whereDate('fecha_inicio', '<=', now())
            ->whereDate('fecha_final', '>=', now())
            ->whereIn('estado', ['vigente', 'deuda'])
            ->with('plan')
            ->latest('fecha_final')
            ->first()?->plan;

        // 🕐 Evaluar si es puntual o atrasado
        $estado = 'puntual'; // Valor por defecto
        if ($plan && $plan->tieneRestriccionHoraria()) {
            $horaActual = now();
            $horaInicio = \Carbon\Carbon::createFromTimeString($plan->hora_inicio);
            $estado = $horaActual->lte($horaInicio) ? 'puntual' : 'atrasado';
        }

        // ✅ Registrar asistencia al plan
        Asistencia::create([
            'asistible_id' => $cliente->id,
            'asistible_type' => Clientes::class,
            'tipo_asistencia' => 'plan',
            'fecha' => today(),
            'hora_entrada' => now(),
            'estado' => $estado,
            'origen' => 'manual',
            'usuario_registro_id' => auth()->id(),
        ]);

        Notification::make()
            ->title('✅ Asistencia registrada')
            ->body("Ingreso registrado con éxito. Estado: " . ucfirst($estado))
            ->success()
            ->send();

        $this->reset('ci', 'cliente', 'personal', 'mostrarModal');
    }

    public function registrarComoPersonal()
    {
        $personal = $this->personal;

        $permiso = $personal->tienePermisoHoy();

        if ($permiso) {
            $yaRegistrado = Asistencia::whereDate('fecha', today())
                ->where('asistible_id', $personal->id)
                ->where('asistible_type', Personal::class)
                ->where('estado', 'permiso')
                ->exists();

            if (!$yaRegistrado) {
                Asistencia::create([
                    'asistible_id' => $personal->id,
                    'asistible_type' => Personal::class,
                    'tipo_asistencia' => 'personal',
                    'fecha' => today(),
                    'estado' => 'permiso',
                    'origen' => 'manual',
                    'usuario_registro_id' => auth()->id(),
                    'observacion' => 'Permiso aprobado: ' . ($permiso->motivo ?? 'Sin motivo registrado'),
                ]);
            }

            Notification::make()
                ->title('📌 Permiso registrado')
                ->body('El personal tiene un permiso aprobado hoy. Se registró como asistencia con estado "permiso".')
                ->info()
                ->send();

            $this->reset('ci', 'cliente', 'personal', 'mostrarModal');
            return;
        }

        $turno = $personal->turnoHoy();

        if (!$turno) {
            Notification::make()
                ->title('⚠️ Sin turno asignado')
                ->body('No se encontró un turno válido para hoy.')
                ->danger()
                ->send();
            return;
        }

        $inicio = now()->setTimeFrom(Carbon::createFromFormat('H:i:s', $turno->hora_inicio))->subHour();
        $fin = now()->setTimeFrom(Carbon::createFromFormat('H:i:s', $turno->hora_fin));
        $ahora = now();

        $asistencia = Asistencia::where('asistible_id', $personal->id)
            ->where('asistible_type', Personal::class)
            ->whereBetween('hora_entrada', [$inicio, $fin])
            ->whereNull('hora_salida')
            ->first();

        if ($asistencia) {
            $minutosDesdeEntrada = $asistencia->hora_entrada
                ? Carbon::parse($asistencia->hora_entrada)->diffInMinutes(now())
                : 0;

            if ($minutosDesdeEntrada < 15) {
                Notification::make()
                    ->title('⏳ Registro rechazado')
                    ->body("No se puede registrar salida aún. Deben pasar al menos 15 minutos desde la entrada.")
                    ->warning()
                    ->send();

                $this->reset('ci', 'cliente', 'personal', 'mostrarModal');
                return;
            }

            $asistencia->update([
                'hora_salida' => $ahora,
            ]);

            Notification::make()
                ->title('✅ Salida registrada')
                ->body('Tu salida ha sido registrada correctamente.')
                ->success()
                ->send();

            $this->reset('ci', 'cliente', 'personal', 'mostrarModal');
            return;
        }

        $yaIngreso = Asistencia::where('asistible_id', $personal->id)
            ->where('asistible_type', Personal::class)
            ->whereBetween('hora_entrada', [$inicio, $fin])
            ->exists();

        if ($yaIngreso) {
            Notification::make()
                ->title('⚠️ Ya registró asistencia para este turno')
                ->body('Este personal ya marcó su asistencia para el turno actual.')
                ->warning()
                ->send();

            $this->reset('ci', 'cliente', 'personal', 'mostrarModal');
            return;
        }

        $verificacion = $personal->puedeRegistrarEntrada();

        if (!$verificacion['permitido']) {
            Notification::make()
                ->title('⚠️ Acceso denegado')
                ->body($verificacion['mensaje'])
                ->danger()
                ->send();
            return;
        }

        $estado = $verificacion['estado'];

        Asistencia::create([
            'asistible_id' => $personal->id,
            'asistible_type' => Personal::class,
            'tipo_asistencia' => 'personal',
            'fecha' => today(),
            'hora_entrada' => $ahora,
            'estado' => $estado,
            'origen' => 'manual',
            'usuario_registro_id' => auth()->id(),
            'observacion' => "Registro manual con estado: {$estado}",
        ]);

        Notification::make()
            ->title('✅ Entrada registrada')
            ->body("Estado: " . ucfirst($estado))
            ->success()
            ->send();

        $this->reset('ci', 'cliente', 'personal', 'mostrarModal');
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('resolverDobleRol')
                ->label('El CI pertenece a Cliente e Instructor')
                ->modalHeading('¿Cómo deseas registrar la asistencia?')
                ->modalDescription('El número de C.I. ingresado está registrado como Cliente y como Personal. Selecciona el rol con el que deseas registrar la asistencia.')
                ->modalSubmitActionLabel('Registrar como Cliente')
                ->modalCancelActionLabel('Cancelar')
                ->color('info')
                ->visible(fn() => $this->mostrarModal)
                ->requiresConfirmation()
                ->action(fn() => $this->registrarComoCliente())
                ->extraModalFooterActions([
                    Action::make('registrarComoInstructor')
                        ->label('Registrar como Instructor')
                        ->action(fn() => $this->registrarComoPersonal())
                        ->color('success'),
                ]),
        ];
    }

    public function registrarComoClienteManual(Clientes $cliente)
    {
        $this->cliente = $cliente;
        $this->registrarComoCliente();
    }

    public function registrarComoPersonalManual(Personal $personal)
    {
        $this->personal = $personal;
        $this->registrarComoPersonal();
    }

    public function mount(): void
    {
        if (session()->has('ci_preseleccionado')) {
            $this->ci = session()->pull('ci_preseleccionado');
            $this->updatedCi($this->ci);
        }
    }

}