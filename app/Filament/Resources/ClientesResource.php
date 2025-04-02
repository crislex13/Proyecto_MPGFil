<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientesResource\Pages;
use App\Models\Clientes;
use App\Models\Plan;
use App\Models\PlanDisciplina;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Carbon\Carbon;
class ClientesResource extends Resource
{
    protected static ?string $model = Clientes::class;
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'Clientes';
    protected static ?string $navigationGroup = 'Administración';

    public static function form(Form $form): Form
    {
        $requestName = request()->route()->getName();
        $isCreatePage = $requestName === 'filament.resources.clientes.create';

        return $form->schema([
            Section::make('Datos personales')->schema([
                TextInput::make('nombre')->required()->placeholder('Ingrese el nombre'),
                TextInput::make('apellido_paterno')->required()->placeholder('Ingrese el apellido paterno'),
                TextInput::make('apellido_materno')->placeholder('Ingrese el apellido materno (opcional)'),
                DatePicker::make('fecha_de_nacimiento')->required()->placeholder('Seleccione la fecha de nacimiento'),
                TextInput::make('ci')
                    ->label('Carnet de identidad')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn(Get $get, Set $set, $state) => $set('biometrico_id', $state))
                    ->placeholder('Ingrese el C.I. (Carnet de Identidad)'),
                TextInput::make('telefono')
                    ->label('Teléfono o Celular')
                    ->required()
                    ->tel()
                    ->maxLength(15)
                    ->placeholder('Ingrese su número de teléfono o celular'),
                TextInput::make('correo')->email()->placeholder('Ingrese su correo electrónico (opcional)'),
                Select::make('sexo')->options([
                    'masculino' => 'Masculino',
                    'femenino' => 'Femenino',
                ])->placeholder('Seleccione el sexo'),
                FileUpload::make('foto')
                    ->label('Foto del cliente')
                    ->image()
                    ->directory('fotos/clientes')
                    ->disk('public')
                    ->getUploadedFileNameForStorageUsing(fn($file) => time() . '-' . str_replace(' ', '_', $file->getClientOriginalName()))
                    ->default(fn($record) => $record?->foto ? asset('storage/' . $record->foto) : null)
                    ->required()
                    ->previewable()
                    ->dehydrated(),
                TextInput::make('biometrico_id')
                    ->label('ID del biométrico')
                    ->required()
                    ->disabled()
                    ->dehydrated(),
            ])->columns(2),

            Section::make('Salud y contacto de emergencia')->schema([
                Textarea::make('antecedentes_medicos')->placeholder('Describa antecedentes médicos relevantes (opcional)'),
                TextInput::make('contacto_emergencia_nombre')->label('Nombre')->placeholder('Ingrese el nombre del contacto de emergencia'),
                TextInput::make('contacto_emergencia_parentesco')->placeholder('Ingrese el parentesco con el contacto de emergencia'),
                TextInput::make('contacto_emergencia_celular')->placeholder('Ingrese el número de celular del contacto de emergencia'),
            ])->columns(2),

            Section::make('Plan')->schema([
                Select::make('plan_id')
                    ->label('Plan')
                    ->relationship('plan', 'nombre')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn(Set $set) => $set('precio_plan', null))
                    ->placeholder('Seleccione el plan'),

                Select::make('disciplina_id')
                    ->label('Disciplina')
                    ->relationship('disciplina', 'nombre')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        $planId = $get('plan_id');
                        $disciplinaId = $get('disciplina_id');

                        if ($planId && $disciplinaId) {
                            $registro = PlanDisciplina::where('plan_id', $planId)
                                ->where('disciplina_id', $disciplinaId)
                                ->first();

                            $set('precio_plan', $registro?->precio ?? 0);

                            $aCuenta = $get('a_cuenta') ?? 0;
                            $set('total', $registro?->precio ?? 0);
                            $set('saldo', ($registro?->precio ?? 0) - $aCuenta);
                        }
                    })
                    ->placeholder('Seleccione la disciplina'),

                DatePicker::make('fecha_inicio')
                    ->required()
                    ->reactive()
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        $plan = Plan::find($get('plan_id'));

                        if ($plan && $state) {
                            $fechaFinal = Carbon::parse($state)
                                ->addDays($plan->duracion_dias)
                                ->subDay();

                            $set('fecha_final', $fechaFinal->toDateString());
                        }
                    })
                    ->placeholder('Seleccione la fecha de inicio')
                    ->hint(fn() => request()->routeIs('filament.admin.resources.clientes.edit')
                        ? 'Puedes modificar la fecha si fue registrada erróneamente.'
                        : null),

                DatePicker::make('fecha_final')
                    ->after(fn(Get $get) => $get('fecha_inicio') ?? today()->format('Y-m-d'))
                    ->disabled()
                    ->dehydrated()
                    ->placeholder('Se calculará automáticamente según la duración del plan'),

                TextInput::make('precio_plan')
                    ->label('Precio del Plan (Bs.)')
                    ->numeric()
                    ->rules(['numeric', 'min:0'])
                    ->disabled()
                    ->dehydrated(),

                TextInput::make('a_cuenta')
                    ->label('A cuenta')
                    ->numeric()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        $precioPlan = floatval($get('precio_plan'));
                        $casillero = floatval($get('casillero_monto'));
                        $total = $precioPlan + $casillero;
                        $aCuenta = floatval($state);

                        if ($aCuenta < 0) {
                            $set('a_cuenta', 0);
                            $set('saldo', $total);
                            Notification::make()
                                ->title('Valor inválido')
                                ->body('No se permiten montos negativos en "A cuenta".')
                                ->danger()
                                ->send();
                            return;
                        }

                        if ($aCuenta > $total) {
                            $set('a_cuenta', $total);
                            $set('saldo', 0);
                            Notification::make()
                                ->title('Atención')
                                ->body('"A cuenta" no puede ser mayor al total.')
                                ->warning()
                                ->send();
                            return;
                        }

                        $set('saldo', $total - $aCuenta);
                    })
                    ->placeholder('Ingrese el monto recibido'),

                TextInput::make('saldo')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->disabled()
                    ->dehydrated(),

                TextInput::make('casillero_monto')
                    ->label('Monto Casillero (Bs.)')
                    ->numeric()
                    ->default(0)
                    ->reactive()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                        $precioPlan = floatval($get('precio_plan'));
                        $aCuenta = floatval($get('a_cuenta'));
                        $casillero = floatval($state);

                        if ($casillero < 0) {
                            $set('casillero_monto', 0);
                            $set('total', $precioPlan);
                            $set('saldo', max($precioPlan - $aCuenta, 0));

                            Notification::make()
                                ->title('Valor inválido')
                                ->body('No se permiten montos negativos en el monto del casillero.')
                                ->danger()
                                ->send();
                            return;
                        }

                        $total = $precioPlan + $casillero;
                        $set('total', $total);
                        $set('saldo', max($total - $aCuenta, 0));
                    })
                    ->hint('Si el cliente usará casillero, indique el monto adicional.'),
                TextInput::make('total')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->disabled()
                    ->dehydrated(),

                Select::make('metodo_pago')->options([
                    'efectivo' => 'Efectivo',
                    'qr' => 'QR',
                ])->required()->placeholder('Seleccione el método de pago'),

                Select::make('comprobante')->options([
                    'simple' => 'Simple',
                    'factura' => 'Factura',
                ])->default('simple')->required()->placeholder('Seleccione el tipo de comprobante'),
            ])->columns(2),

            Section::make('Estado del cliente')->schema([
                Select::make('estado')->options([
                    'activo' => 'Activo',
                    'inactivo' => 'Inactivo',
                ])->default('activo')->required()->placeholder('Seleccione el estado del cliente'),
            ]),

            Section::make('Resumen de pago')->schema([
                Placeholder::make('resumen_total')
                    ->label('💰 Total a pagar (Bs.)')
                    ->content(fn(Get $get) => 'Bs. ' . number_format($get('total') ?? 0, 2))
                    ->extraAttributes([
                        'class' => 'text-lg font-semibold text-gray-800'
                    ]),

                Placeholder::make('resumen_saldo')
                    ->label('💸 Saldo restante (Bs.)')
                    ->content(fn(Get $get) => 'Bs. ' . number_format($get('saldo') ?? 0, 2)),

                Placeholder::make('estado_pago')
                    ->label('Estado de pago')
                    ->content(function (Get $get) {
                        $saldo = $get('saldo') ?? 0;

                        return new HtmlString(
                            $saldo > 0
                            ? '<span style="color: red; font-weight: bold;">⚠️ El cliente tiene un saldo pendiente.</span>'
                            : '<span style="color: green; font-weight: bold;">✅ El cliente ha pagado completamente.</span>'
                        );
                    }),
            ])->columns(2),
        ]);
    }


    public static function table(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('foto_url')
                ->label('Foto')
                ->circular()
                ->height(40)
                ->width(40),


            TextColumn::make('nombre')
                ->label('Nombre')
                ->formatStateUsing(function ($state, $record) {
                    return $record->saldo > 0
                        ? "⚠️ $state"
                        : $state;
                })
                ->searchable(),
            TextColumn::make('apellido_paterno')->searchable(),
            TextColumn::make('apellido_materno')->searchable(),
            TextColumn::make('correo')->searchable(),
            TextColumn::make('telefono'),
            TextColumn::make('plan.nombre')->label('Plan'),
            TextColumn::make('disciplina.nombre')->label('Disciplina'),
            TextColumn::make('precio_plan')->money('BOB'),
            TextColumn::make('a_cuenta')->money('BOB'),
            TextColumn::make('saldo')->money('BOB'),
            BadgeColumn::make('deuda')
                ->label('Deuda')
                ->colors([
                    'danger' => fn($record) => $record->saldo > 0,
                    'success' => fn($record) => $record->saldo == 0,
                ])
                ->formatStateUsing(fn($record) => $record->saldo > 0 ? 'Pendiente' : 'Sin deuda'),

            TextColumn::make('total')->money('BOB'),
            TextColumn::make('fecha_inicio')->label('Inicio')->sortable(),
            TextColumn::make('fecha_final')->label('Final')->sortable(),
            BadgeColumn::make('estado')->label('Estado de Cliente')->colors([
                'success' => 'activo',
                'danger' => 'inactivo',
            ])->sortable(),
        ])->filters([
                    \Filament\Tables\Filters\Filter::make('Con deuda')
                        ->query(fn($query) => $query->where('saldo', '>', 0)),
                ])->actions([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('ver')
                        ->label('Ver')
                        ->icon('heroicon-o-eye')
                        ->modalHeading('Detalles del Cliente')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Cerrar')
                        ->color('info')
                        ->form(fn(Clientes $record) => [
                            // Foto
                            Placeholder::make('Foto')
                                ->label('Foto del cliente')
                                ->content(fn($record) => new HtmlString(
                                    '<img src="' . asset('storage/' . $record->foto) . '" alt="Foto del cliente" style="width: 120px; height: 120px; object-fit: cover; border-radius: 100px;" />'
                                ))
                                ->columnSpanFull(),

                            // Datos Personales
                            Section::make('Datos Personales')->schema([
                                TextInput::make('nombre')->default($record->nombre)->disabled(),
                                TextInput::make('apellido_paterno')->default($record->apellido_paterno)->disabled(),
                                TextInput::make('apellido_materno')->default($record->apellido_materno)->disabled(),
                                TextInput::make('ci')->default($record->ci)->disabled(),
                                DatePicker::make('fecha_de_nacimiento')->default($record->fecha_de_nacimiento)->disabled(),
                                TextInput::make('telefono')->default($record->telefono)->disabled(),
                                TextInput::make('correo')->default($record->correo)->disabled(),
                                Placeholder::make('Sexo')->content($record->sexo)->columnSpanFull(),
                                Placeholder::make('ID Biométrico')->content($record->biometrico_id)->columnSpanFull(),
                            ]),

                            // Salud y Emergencia
                            Section::make('Salud y Contacto de Emergencia')->schema([
                                Placeholder::make('Antecedentes Médicos')->content($record->antecedentes_medicos ?? 'Ninguno')->columnSpanFull(),
                                Placeholder::make('Nombre de Emergencia')->content($record->contacto_emergencia_nombre ?? '-'),
                                Placeholder::make('Parentesco')->content($record->contacto_emergencia_parentesco ?? '-'),
                                Placeholder::make('Celular de Emergencia')->content($record->contacto_emergencia_celular ?? '-'),
                            ]),

                            // Plan y Disciplina
                            Section::make('Plan y Disciplina')->schema([
                                Placeholder::make('Plan')->content($record->plan?->nombre ?? '-'),
                                Placeholder::make('Disciplina')->content($record->disciplina?->nombre ?? '-'),
                                Placeholder::make('Fecha de Inicio')->content($record->fecha_inicio ?? '-'),
                                Placeholder::make('Fecha Final')->content($record->fecha_final ?? '-'),
                            ]),

                            Section::make('Pagos y Estado')->schema([
                                Placeholder::make('Precio del Plan')->content('Bs. ' . number_format($record->precio_plan, 2)),
                                Placeholder::make('A Cuenta')->content('Bs. ' . number_format($record->a_cuenta, 2)),
                                Placeholder::make('Saldo')->content('Bs. ' . number_format($record->saldo, 2)),
                                Placeholder::make('Monto por Casillero')
                                    ->content('Bs. ' . number_format($record->casillero_monto, 2))
                                    ->helperText('Este monto representa el uso del casillero por parte del cliente.'),
                                Placeholder::make('Total')->content('Bs. ' . number_format($record->total, 2)),
                                Placeholder::make('Método de Pago')->content(ucfirst($record->metodo_pago)),
                                Placeholder::make('Comprobante')->content(ucfirst($record->comprobante)),
                                Placeholder::make('Estado del Cliente')
                                    ->content(fn() => new HtmlString(
                                        $record->estado === 'activo'
                                        ? '<span style="color: green;">Activo</span>'
                                        : '<span style="color: red;">Inactivo</span>'
                                    ))
                                    ->columnSpanFull(),
                            ]),

                            Section::make('Estado de Deuda')->schema([
                                Placeholder::make('Deuda')
                                    ->content(fn() => new HtmlString(
                                        $record->saldo > 0
                                        ? '<span style="color: red;">Pendiente</span>'
                                        : '<span style="color: green;">Sin deuda</span>'
                                    ))
                                    ->columnSpanFull(),

                                Placeholder::make('Bloqueado por Deuda')
                                    ->content(fn() => new HtmlString(
                                        $record->bloqueado_por_deuda
                                        ? '<span style="color: red;">Sí</span>'
                                        : '<span style="color: green;">No</span>'
                                    ))
                                    ->helperText('Este campo indica si el cliente fue bloqueado por falta de pago.')
                                    ->columnSpanFull(),
                            ]),
                        ])
                ]);

    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClientes::route('/'),
            'create' => Pages\CreateClientes::route('/create'),
            'edit' => Pages\EditClientes::route('/{record}/edit'),
        ];
    }
}
