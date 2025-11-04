<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanClienteResource\Pages;
use App\Models\PlanCliente;
use App\Models\PlanDisciplina;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Carbon\Carbon;
use App\Models\Clientes;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\PlanClienteResource\RelationManagers\SesionesAdicionalesRelationManager;
use Filament\Notifications\Notification;
use Filament\Forms\Components\CheckboxList;
use Filament\Support\Exceptions\Halt;
use App\Models\Asistencia;


class PlanClienteResource extends Resource
{
    protected static ?string $model = PlanCliente::class;

    public static function getNavigationLabel(): string
    {
        return 'Planes de Cliente';
    }

    public static function getNavigationGroup(): string
    {
        return 'Administración de Clientes';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-document-text';
    }

    public static function getModelLabel(): string
    {
        return 'Plan de Cliente';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Planes de Clientes';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('view_any_plan::cliente');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_plan::cliente');
    }

    public static function canView($record): bool
    {
        return auth()->user()?->can('view_plan::cliente');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_plan::cliente');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_plan::cliente');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('admin') === true;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->hasRole('admin') === true;
    }

    public static function form(Form $form): Form
    {
        $actualizarMontos = function (callable $get, callable $set) {
            $precio = floatval($get('precio_plan') ?? 0);
            $cuenta = floatval($get('a_cuenta') ?? 0);
            $saldo = max($precio - $cuenta, 0);

            $set('total', $precio);
            $set('saldo', $saldo);
            $set('estado', $saldo <= 0 ? 'vigente' : 'deuda');
        };

        $calcularFechaFinal = function (callable $get, callable $set) {
            $plan = \App\Models\Plan::find($get('plan_id'));
            $fechaInicio = $get('fecha_inicio') ? Carbon::parse($get('fecha_inicio')) : null;
            // ahora tratamos SIEMPRE como índices numéricos 0..6
            $diasPermitidos = array_map('intval', (array) $get('dias_permitidos'));

            if (!$plan || !$fechaInicio || $fechaInicio->year < 2020) {
                $set('fecha_final', null);
                return;
            }

            $duracion = (int) ($plan->duracion_dias ?? 0);

            // todos los días marcados -> rango corrido
            if (count($diasPermitidos) === 7) {
                $set('fecha_final', $fechaInicio->copy()->addDays(max($duracion - 1, 0))->toDateString());
                return;
            }

            // días específicos -> contar solo días permitidos
            if (!empty($diasPermitidos)) {
                $permitidos = collect($diasPermitidos)->map(fn($d) => (int) $d)->unique()->all();

                $diasContados = 0;
                $fecha = $fechaInicio->copy();
                $guard = 730; // límite de seguridad

                while ($diasContados < $duracion && $guard > 0) {
                    if (in_array($fecha->dayOfWeek, $permitidos, true)) {
                        $diasContados++;
                    }
                    if ($diasContados < $duracion) {
                        $fecha->addDay();
                    }
                    $guard--;
                }

                $set('fecha_final', $fecha->toDateString());
                return;
            }

            // sin selección -> rango corrido
            $set('fecha_final', $fechaInicio->copy()->addDays(max($duracion - 1, 0))->toDateString());
        };


        return $form->schema([
            Section::make('Información del Cliente')
                ->icon('heroicon-o-user')
                ->columns(2)
                ->schema([
                    Select::make('cliente_id')
                        ->label('Cliente')
                        ->options(Clientes::all()->pluck('nombre_completo', 'id'))
                        ->searchable()
                        ->required()
                        ->placeholder('Seleccione el cliente'),
                ]),

            Section::make('Detalles del Plan')
                ->icon('heroicon-o-clipboard-document-check')
                ->columns(2)
                ->schema([
                    Select::make('plan_id')
                        ->label('Plan')
                        ->options(\App\Models\Plan::pluck('nombre', 'id'))
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $get, callable $set) use ($actualizarMontos) {
                            $planId = $state;
                            $disciplinaId = $get('disciplina_id');

                            if ($planId && $disciplinaId) {
                                $precio = PlanDisciplina::where('plan_id', $planId)
                                    ->where('disciplina_id', $disciplinaId)
                                    ->value('precio');

                                $set('precio_plan', $precio ?? 0);

                                if ($precio === null) {
                                    Notification::make()
                                        ->title('⚠️ Precio no asignado')
                                        ->body('No se encontró un precio para este Plan y Disciplina.')
                                        ->warning()
                                        ->send();
                                }
                            } else {
                                $set('precio_plan', 0);
                            }

                            $tipoAsistencia = \App\Models\Plan::find($planId)?->tipo_asistencia;
                            $set('tipo_asistencia', $tipoAsistencia);

                            $actualizarMontos($get, $set);
                        })
                        ->afterStateHydrated(function ($state, callable $get, callable $set) use ($actualizarMontos) {
                            $disciplinaId = $get('disciplina_id');
                            if ($state && $disciplinaId) {
                                $precio = PlanDisciplina::where('plan_id', $state)
                                    ->where('disciplina_id', $disciplinaId)
                                    ->value('precio');

                                if ($precio !== null) {
                                    // Solo corrige si está vacío o en 0 para no pisar ediciones válidas
                                    $precioActual = (float) ($get('precio_plan') ?? 0);
                                    if ($precioActual <= 0) {
                                        $set('precio_plan', $precio);
                                        $actualizarMontos($get, $set);
                                    }
                                }
                            }
                        }),

                    Select::make('disciplina_id')
                        ->label('Disciplina')
                        ->options(\App\Models\Disciplina::pluck('nombre', 'id'))
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $get, callable $set) use ($actualizarMontos) {
                            $disciplinaId = $state;
                            $planId = $get('plan_id');

                            if ($planId && $disciplinaId) {
                                $precio = PlanDisciplina::where('plan_id', $planId)
                                    ->where('disciplina_id', $disciplinaId)
                                    ->value('precio');

                                if ($precio !== null) {
                                    $set('precio_plan', $precio);
                                } else {
                                    $set('precio_plan', 0);
                                    Notification::make()
                                        ->title('⚠️ Precio no asignado')
                                        ->body('No se encontró un precio para este Plan y Disciplina.')
                                        ->warning()
                                        ->send();
                                }
                            }

                            $actualizarMontos($get, $set);
                        })
                        ->afterStateHydrated(function ($state, callable $get, callable $set) use ($actualizarMontos) {
                            $planId = $get('plan_id');
                            if ($planId && $state) {
                                $precio = PlanDisciplina::where('plan_id', $planId)
                                    ->where('disciplina_id', $state)
                                    ->value('precio');

                                if ($precio !== null) {
                                    $precioActual = (float) ($get('precio_plan') ?? 0);
                                    if ($precioActual <= 0) {
                                        $set('precio_plan', $precio);
                                        $actualizarMontos($get, $set);
                                    }
                                }
                            }
                        }),

                    CheckboxList::make('dias_permitidos')
                        ->label('Días permitidos para asistir')
                        ->options([
                            1 => 'Lunes',
                            2 => 'Martes',
                            3 => 'Miércoles',
                            4 => 'Jueves',
                            5 => 'Viernes',
                            6 => 'Sábado',
                            0 => 'Domingo',
                        ])
                        ->columns(3)
                        ->required()
                        // 👇 NUEVO: normaliza a enteros al hidratar
                        ->afterStateHydrated(function ($component, $state) {
                            $component->state(array_map('intval', (array) $state));
                        })
                        ->afterStateUpdated(function ($state, callable $set, callable $get) use ($calcularFechaFinal) {
                            $fecha = $get('fecha_inicio');
                            try {
                                $carbonFecha = Carbon::parse($fecha);
                                if ($carbonFecha->year < 2020) {
                                    throw new \Exception('Fecha inválida');
                                }
                                $calcularFechaFinal($get, $set);
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('⚠️ Atención')
                                    ->body('Seleccione primero una fecha de inicio válida para calcular la fecha final.')
                                    ->warning()
                                    ->send();
                                $set('fecha_final', null);
                            }
                        })
                        ->helperText('Seleccione los días en que el cliente podrá asistir si el plan lo permite'),

                    DatePicker::make('fecha_inicio')
                        ->label('Fecha inicio')
                        ->required()
                        ->maxDate(now())
                        ->minDate(Carbon::createFromDate(2020, 1, 1))
                        ->reactive()
                        ->extraAttributes([
                            'id' => 'fecha_inicio_input',
                            'onkeydown' => 'event.preventDefault();',
                        ])
                        ->afterStateUpdated(function ($state, callable $set, callable $get) use ($calcularFechaFinal) {
                            try {
                                $fecha = Carbon::parse($state);

                                if ($fecha->year < 2020) {
                                    Notification::make()
                                        ->title('⚠️ Fecha inválida')
                                        ->body('Por favor, seleccione una fecha válida desde el calendario.')
                                        ->warning()
                                        ->send();

                                    $set('fecha_inicio', null);
                                    $set('fecha_final', null);
                                    return;
                                }

                                $calcularFechaFinal($get, $set);
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('⚠️ Fecha inválida')
                                    ->body('La fecha ingresada no es válida.')
                                    ->danger()
                                    ->send();

                                $set('fecha_inicio', null);
                                $set('fecha_final', null);
                            }
                        }),

                    DatePicker::make('fecha_final')
                        ->label('Fecha final')
                        ->disabled()
                        ->dehydrated(true),

                ]),

            Section::make('Costos del Plan')
                ->icon('heroicon-o-banknotes')
                ->columns(3)
                ->schema([
                    TextInput::make('precio_plan')
                        ->label('Precio del Plan')
                        ->readOnly()
                        ->numeric()
                        ->default(0)
                        ->afterStateUpdated(function ($state, callable $set, callable $get) use ($actualizarMontos) {
                            $actualizarMontos($get, $set);
                        })
                        ->disabled()
                        ->dehydrated(false),

                    TextInput::make('a_cuenta')
                        ->label('A cuenta')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->live(onBlur: true)
                        ->extraAttributes([
                            'oninput' => "this.value = this.value.replace(/[^0-9.]/g, '')",
                            'onkeydown' => "if(event.key === '-' || event.key === 'e') event.preventDefault();",
                        ])
                        ->afterStateUpdated(function ($state, callable $set, callable $get) use ($actualizarMontos) {
                            $precio = floatval($get('precio_plan'));
                            $cuenta = floatval($state);

                            if ($cuenta > $precio) {
                                Notification::make()
                                    ->title('⚠️ Monto inválido')
                                    ->body('El monto "A cuenta" no puede ser mayor al precio del plan.')
                                    ->danger()
                                    ->send();

                                $set('a_cuenta', $precio);
                            }

                            $actualizarMontos($get, $set);
                        })
                        ->afterStateHydrated(fn(callable $get, callable $set) => $actualizarMontos($get, $set)),

                    TextInput::make('total')
                        ->label('Total')
                        ->readOnly()
                        ->disabled()
                        ->dehydrated(false),

                    TextInput::make('saldo')
                        ->label('Saldo')
                        ->readOnly()
                        ->disabled()
                        ->dehydrated(false),

                    Select::make('estado')
                        ->label('Estado del plan')
                        ->options([
                            'vigente' => 'Vigente',
                            'deuda' => 'Con deuda',
                            'vencido' => 'Vencido',
                            'bloqueado' => 'Bloqueado por deuda',
                        ])
                        //->default('vigente')
                        ->required()
                        ->native(false)
                        ->dehydrated(false)
                        ->columnSpanFull(),
                ]),

            Section::make('Pago')
                ->icon('heroicon-o-currency-dollar')
                ->columns(2)
                ->schema([
                    Select::make('metodo_pago')
                        ->label('Método de pago')
                        ->options([
                            'efectivo' => 'Efectivo',
                            'qr' => 'QR',
                        ])
                        ->required(),

                    Select::make('comprobante')
                        ->label('Comprobante')
                        ->options([
                            'simple' => 'Simple',
                            'factura' => 'Factura',
                        ])
                        ->default('simple')
                        ->required(),
                ]),

            Section::make('Control de cambios')
                ->icon('heroicon-o-user-circle')
                ->collapsible()
                ->columns(1)
                ->visible(fn() => auth()->user()?->hasRole('admin'))
                ->schema([
                    \Filament\Forms\Components\Placeholder::make('registrado_por')
                        ->label('Registrado por')
                        ->content(fn($record) => optional($record?->registradoPor)->name ?? 'No registrado'),

                    \Filament\Forms\Components\Placeholder::make('modificado_por')
                        ->label('Modificado por')
                        ->content(fn($record) => optional($record?->modificadoPor)->name ?? 'Sin cambios'),
                ]),
        ]);
    }


    protected function mutateFormDataBeforeSave(array $data): array
    {
        // 1) Recalcular precio desde PlanDisciplina (por si el form no lo setea)
        if (!empty($data['plan_id']) && !empty($data['disciplina_id'])) {
            $precio = PlanDisciplina::where('plan_id', $data['plan_id'])
                ->where('disciplina_id', $data['disciplina_id'])
                ->value('precio');

            if ($precio !== null) {
                $data['precio_plan'] = (float) $precio;
            }
        }

        // 2) Calcular totales y estado
        $precio = (float) ($data['precio_plan'] ?? 0);
        $cuenta = (float) ($data['a_cuenta'] ?? 0);

        // (ya validas que a_cuenta no supere precio en el form)
        $data['total'] = $precio;
        $data['saldo'] = max($precio - $cuenta, 0);
        $data['estado'] = $data['saldo'] <= 0 ? 'vigente' : 'deuda';

        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cliente.foto_url')
                    ->label('Foto')
                    ->circular()
                    ->height(40)
                    ->width(40),

                TextColumn::make('cliente.nombre_completo')
                    ->label('Cliente')
                    ->icon('heroicon-o-user')
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('cliente', function ($q) use ($search) {
                            $q->where('nombre', 'like', "%{$search}%")
                                ->orWhere('apellido_paterno', 'like', "%{$search}%")
                                ->orWhere('apellido_materno', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(['nombre', 'apellido_paterno', 'apellido_materno']),

                TextColumn::make('plan.nombre')
                    ->label('Plan')
                    ->icon('heroicon-o-rectangle-stack'),

                TextColumn::make('disciplina.nombre')
                    ->label('Disciplina')
                    ->icon('heroicon-o-bolt'),

                TextColumn::make('fecha_inicio')
                    ->label('Inicio')
                    ->icon('heroicon-o-calendar-days')
                    ->date()
                    ->sortable(),

                TextColumn::make('fecha_final')
                    ->label('Final')
                    ->icon('heroicon-o-calendar')
                    ->date()
                    ->sortable(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->icon('heroicon-o-adjustments-vertical')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'vigente' => 'success',
                        'bloqueado' => 'danger',
                        'vencido' => 'warning',
                        'deuda' => 'gray',
                    })
                    ->sortable()
                    ->formatStateUsing(fn(?string $state) => $state === 'deuda' ? 'Con deuda' : ucfirst($state)),


                TextColumn::make('total')
                    ->label('Total')
                    ->icon('heroicon-o-calculator')
                    ->money('BOB')
                    ->sortable()
                    ->alignRight(),

                TextColumn::make('a_cuenta')
                    ->label('A cuenta')
                    ->icon('heroicon-o-currency-dollar')
                    ->money('BOB')
                    ->alignRight()
                    ->color(fn($state) => $state > 0 ? 'success' : null)
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('saldo')
                    ->label('Saldo')
                    ->icon('heroicon-o-exclamation-circle')
                    ->money('BOB')
                    ->alignRight()
                    ->color(fn($state) => $state > 0 ? 'danger' : null)
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('registradoPor.name')
                    ->label('Registrado por')
                    ->icon('heroicon-o-user-plus')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->visible(fn() => auth()->user()?->hasRole('admin')),

                TextColumn::make('modificadoPor.name')
                    ->label('Modificado por')
                    ->icon('heroicon-o-pencil-square')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->visible(fn() => auth()->user()?->hasRole('admin')),
            ])
            ->filters([

                Tables\Filters\SelectFilter::make('plan_id')
                    ->label('Plan')
                    ->relationship('plan', 'nombre'),

                Tables\Filters\SelectFilter::make('disciplina_id')
                    ->label('Disciplina')
                    ->relationship('disciplina', 'nombre'),

                Tables\Filters\SelectFilter::make('estado')
                    ->label('Estado del plan')
                    ->options([
                        'vigente' => 'Vigente',
                        'deuda' => 'Con deuda',
                        'vencido' => 'Vencido',
                        'bloqueado' => 'Bloqueado por deuda',
                    ]),

            ])
            ->actions([
                Tables\Actions\EditAction::make()->tooltip('Editar este plan'),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn() => auth()->user()?->hasRole('admin') === true)
                    ->before(function ($record) {
                        // 1) Tiene sesiones adicionales
                        if ($record->sesionesAdicionales()->exists()) {
                            Notification::make()
                                ->title('No puedes eliminar este plan')
                                ->body('El plan tiene sesiones adicionales y/o asistencias vinculadas.')
                                ->danger()->send();
                            throw new Halt();
                        }

                        // 2) Tiene asistencias por sesión de este plan
                        $idsSesiones = $record->sesionesAdicionales()->pluck('id');
                        if ($idsSesiones->isNotEmpty()) {
                            $hayAsist = Asistencia::whereIn('sesion_adicional_id', $idsSesiones)->exists();
                            if ($hayAsist) {
                                Notification::make()
                                    ->title('No puedes eliminar este plan')
                                    ->body('Hay asistencias vinculadas a sesiones de este plan.')
                                    ->danger()->send();
                                throw new Halt();
                            }
                        }

                        // 3) (Opcional) Bloquear si hay asistencias por "plan" del cliente en el rango del plan
                        $hayAsistPlan = Asistencia::where('asistible_type', Clientes::class)
                            ->where('asistible_id', $record->cliente_id)
                            ->where('tipo_asistencia', 'plan')
                            ->whereBetween('fecha', [$record->fecha_inicio, $record->fecha_final])
                            ->exists();

                        if ($hayAsistPlan) {
                            Notification::make()
                                ->title('No puedes eliminar este plan')
                                ->body('Hay asistencias del cliente registradas durante la vigencia del plan.')
                                ->danger()->send();
                            throw new Halt();
                        }
                    }),
            ])
            ->bulkActions([

            ])
            ->headerActions([
                Tables\Actions\Action::make('reporteDiario')
                    ->label('PDF Diario (hoy)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn() => route('reportes.planes.dia', ['date' => now()->toDateString()]))
                    ->openUrlInNewTab()
                    ->visible(fn() => auth()->user()?->hasRole('admin')),

                Tables\Actions\Action::make('reporteMensual')
                    ->label('PDF Mensual (actual)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn() => route('reportes.planes.mes', [
                        'year' => now()->year,
                        'month' => now()->month,
                    ]))
                    ->openUrlInNewTab()
                    ->visible(fn() => auth()->user()?->hasRole('admin')),

                Tables\Actions\Action::make('reporteAnual')
                    ->label('PDF Anual (actual)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn() => route('reportes.planes.anio', ['year' => now()->year]))
                    ->openUrlInNewTab()
                    ->visible(fn() => auth()->user()?->hasRole('admin')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlanClientes::route('/'),
            'create' => Pages\CreatePlanCliente::route('/create'),
            'edit' => Pages\EditPlanCliente::route('/{record}/edit'),
        ];
    }
}
