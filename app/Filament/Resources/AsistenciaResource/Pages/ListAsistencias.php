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

class ListAsistencias extends ListRecords
{
    protected static string $resource = AsistenciaResource::class;

    public string $ci = '';
    public ?Clientes $cliente = null;
    public ?Personal $personal = null;
    public bool $mostrarModal = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Este método lo puedes dejar si no haces create por formulario directo
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
                    Asistencia::create([
                        'asistible_id' => $cliente->id,
                        'asistible_type' => Clientes::class,
                        'tipo_asistencia' => 'sesion',
                        'sesion_adicional_id' => $sesion->id,
                        'fecha' => today(),
                        'hora_entrada' => $ahora,
                        'estado' => 'puntual',
                        'origen' => 'manual',
                        'usuario_registro_id' => auth()->id(),
                    ]);

                    Notification::make()
                        ->title('✅ Asistencia registrada')
                        ->body("Se registró asistencia a tu sesión adicional.")
                        ->success()
                        ->send();

                    $this->reset('ci', 'cliente', 'personal', 'mostrarModal');
                    return; // <- Este return es crucial
                }
            }

            Notification::make()
                ->title('⏰ Fuera de horario')
                ->body('Tienes sesiones hoy, pero no estás dentro del horario permitido.')
                ->warning()
                ->send();
            return;
        }

        // 🔍 2. Si no hay sesiones válidas, verificar plan activo
        [$puedeIngresar, $mensaje] = $cliente->puedeRegistrarAsistenciaHoy();

        if (!$puedeIngresar) {
            Notification::make()
                ->title('🚫 Acceso denegado')
                ->body($mensaje)
                ->danger()
                ->send();
            return;
        }

        // ✅ 3. Registrar por plan si aplica
        Asistencia::create([
            'asistible_id' => $cliente->id,
            'asistible_type' => Clientes::class,
            'tipo_asistencia' => 'plan',
            'fecha' => today(),
            'hora_entrada' => now(),
            'estado' => 'puntual',
            'origen' => 'manual',
            'usuario_registro_id' => auth()->id(),
        ]);

        Notification::make()
            ->title('✅ Asistencia registrada')
            ->body('Ingreso con plan activo registrado con éxito.')
            ->success()
            ->send();

        $this->reset('ci', 'cliente', 'personal', 'mostrarModal');
    }

    public function registrarComoPersonal()
    {
        $personal = $this->personal;

        // ✅ 1. Verificamos si tiene un permiso aprobado hoy
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
                    'observacion' => 'Permiso: ' . ($permiso->motivo ?? 'Sin motivo'),
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

        // ✅ 2. Verificamos si tiene turno hoy
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

        // ✅ 3. Verificamos si ya tiene una asistencia en este turno sin salida
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

        // ✅ 4. Verificamos si ya registró entrada para este turno (ya tiene entrada y salida)
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

        // ✅ 5. Validamos si puede ingresar (puntual o atrasado)
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

        // ✅ 6. Registrar nueva entrada
        Asistencia::create([
            'asistible_id' => $personal->id,
            'asistible_type' => Personal::class,
            'tipo_asistencia' => 'personal',
            'fecha' => today(),
            'hora_entrada' => $ahora,
            'estado' => $estado,
            'origen' => 'manual',
            'usuario_registro_id' => auth()->id(),
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